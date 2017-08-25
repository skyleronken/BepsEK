<?php
if (!isset($_GET['sid']) || empty($_GET['sid'])) {
    exit('Access Denied');
} else {
    $apitoken = $_GET['sid'];
}

function errorHandler($code, $message, $file, $line)
{
    $source = "[".$_SERVER['HTTP_USER_AGENT']." / ".$_SERVER['REMOTE_ADDR']."]";
    $data = 'Error Type: '.$code.' Message: '.$message.' In file: '.$file.' On line: '.$line.' Source: '.$source;
    $msg = $data;
    logError($msg);
    exit();
}

function fatalErrorShutdownHandler()
{
    $last_error = error_get_last();
    if ($last_error['type'] === E_ERROR) {
        
        errorHandler(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
    }
}

set_error_handler('errorHandler');
register_shutdown_function('fatalErrorShutdownHandler');


include_once('inc/config.php');
include_once('inc/functions/functions.php');



function parseRequests($input)
{
	if(strpos($input, '|') !== false) {
	   $p      = explode("|", $input);
		$return = array();
		foreach ($p as $e) {
			$dd               = explode("=", $e);
			$return[$dd['0']] = $dd['1'];
		}
		return $return;
	} else {
	  die('Invalid API Token');
	}
}

function random_str($length = 10)
{
    $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString     = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function callToken($flowID,$tokenTime)
{
    global $dbh;
    $stmt = $dbh->prepare("SELECT * FROM tokens WHERE flow_id = :flow_id");
    $stmt->bindParam(':flow_id', $flowID);
    $stmt->execute();
    //chk token for the given flow id
    if ($stmt->rowCount() > 0) {
        $token = random_str(rand(5,15));
        $stmt  = $dbh->prepare("UPDATE tokens SET token = :token,timestamp = :timestamp WHERE flow_id = :flow_id");
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
		$stmt->bindParam(':timestamp', $tokenTime, PDO::PARAM_STR);
		$stmt->bindParam(':flow_id', $flowID, PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $token = random_str(rand(5,15));
        $sql   = "INSERT INTO tokens(token,flow_id,timestamp) VALUES (:token,:flow_id,:timestamp)";
        $stmt  = $dbh->prepare($sql);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->bindParam(':flow_id', $flowID, PDO::PARAM_STR);
		$stmt->bindParam(':timestamp', $tokenTime, PDO::PARAM_STR);
        $stmt->execute();
    }
    return $token;
}

function chkUserID($userID)
{
    global $dbh;
    $stmt = $dbh->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userID);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        return $userID;
    } else {
        logEvent("1","Invalid UserID was provided");
        die('Invalid API Token');
    }
}

function chkFlowID($flowID)
{
    global $dbh;
    $stmt = $dbh->prepare("SELECT user_id FROM flows WHERE id = :id");
    $stmt->bindParam(':id', $flowID);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        return $flowID;
    } else {
        logEvent("1","Invalid FlowID was provided");
        die('Invalid API Token');
    }
}

function checkTarif($user_id)
{
    global $dbh;
    $stmt = $dbh->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    $tarifExp = $stmt->fetchObject();
    if (isset($tarifExp)) {
        if (!is_numeric($tarifExp->expiration) || $tarifExp->expiration < time() || $tarifExp->expiration == 0) {
            exit('Your subscription has expired please contact support');
        }
    } else {
        exit('Your subscription has expired please contact support');
    }
}

function getToken($flowID)
{
    global $dbh, $config;
    $stmt = $dbh->prepare("SELECT * FROM flows WHERE id = :id");
    $stmt->bindParam(':id', $flowID, PDO::PARAM_STR);
    $stmt->execute();
    $lastToken = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($lastToken)) {
        if ($lastToken['last_token'] < time()) {
			$tokenTime = time() + $config['api']['tokenTTL'];
            $token     = callToken($flowID,$tokenTime);
            $stmt      = $dbh->prepare("UPDATE flows SET last_token = :last_token WHERE id = :id");
            $stmt->bindParam(':last_token', $tokenTime, PDO::PARAM_STR);
            $stmt->bindParam(':id', $flowID, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $stmt = $dbh->prepare("SELECT * FROM tokens WHERE flow_id = :flow_id");
            $stmt->bindParam(':flow_id', $flowID);
            $stmt->execute();
            $tokend = $stmt->fetch(PDO::FETCH_ASSOC);
            $token  = $tokend['token'];
			if(empty($token)){
				$tokenTime = time() + $config['api']['tokenTTL'];
				$token     = callToken($flowID,$tokenTime);
			}
        }
        return $token;
    } else {
        logEvent("2","Token Generation failed");
        die("Something wrong during Token");
    }
}

function selectPROXY()
{
    //select random domain/proxy
    global $dbh;
    $stmt = $dbh->prepare("SELECT * FROM proxy ORDER BY url ASC LIMIT 3");
    $stmt->execute();
    $proxy = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($proxy) > 0) {
        $proxyList = Array();
        foreach ($proxy as $k => $v) {
            $proxyList[] = $v;
        }
        $array  = $proxyList[array_rand($proxyList)];
	#$array = $proxyList[0];
        $object = json_decode(json_encode($array), FALSE);
        return $object;
    } else {
        logEvent("2","No Proxy was found");
        exit("no proxy");
    }
}


function getPROXY()
{
    global $dbh;
    $proxy = selectPROXY();
    // check if last_check longer than 15 minutes
    if ($proxy->last_check < intval(time() - 15 * 60)) {
        #$results = checkURL4BAN($proxy->url);
        $results = "0";
        if ($results >= 2) {
            //if more than 3 detections delete proxy and select new
            $sql  = "DELETE FROM proxy WHERE id =  :id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $proxy->id, PDO::PARAM_INT);
            $stmt->execute();
            $proxy = getPROXY();
        } else {
            // if all good and clean update last_check and continue
            $sql  = "UPDATE proxy SET last_check = :last_check WHERE id = :id";
            $stmt = $dbh->prepare($sql);
			$time = time();
            $stmt->bindParam(':last_check',$time , PDO::PARAM_STR);
            $stmt->bindParam(':id', $proxy->id, PDO::PARAM_STR);
            $stmt->execute();
        }
    }
    return $proxy;
}



include('inc/functions/RC4URL.php');
$enc = new URL_Encryption;

$decoded_apitoken   = $enc->safe_b64decode($apitoken);
$decrypted_apitoken = $enc->decrypt($config['rc4']['key'], $decoded_apitoken);
$ApiData            = parseRequests($decrypted_apitoken);

$user_id = chkUserID($ApiData['uid']);
$flow_id = chkFlowID($ApiData['fid']);
checkTarif($user_id);
$token = getToken($flow_id);

$selectedPROXY     = getPROXY();
$selectedPROXYurl  = $selectedPROXY->url;
$selectedPROXYhost = parse_url($selectedPROXYurl);
$encryptedToken    = $enc->safe_b64encode($enc->encrypt($config['rc4']['key'], $token));
$plainData         = hash("fnv164",$token . $user_id) . "|" . hash("fnv164",$token . $flow_id) . "|" . hash("crc32",$selectedPROXYhost['host']);
$encryptedData     = $enc->safe_b64encode($enc->encrypt($config['rc4']['key'], $plainData));
/*
proxy_url + ? + encrypt($token) + = + encrypt(data);
*/
header("Content-Type: text/plain");
$firstString = strtolower($selectedPROXYurl) . '?'. $encryptedToken . '=' . $encryptedData;
$newString = preg_replace('/\s+/', '', $firstString);
//$newceckchar = strtolower($str);
echo $newString; 


//echo $selectedPROXYurl . '?'. $encryptedToken . '=' . $encryptedData;
exit;
?>
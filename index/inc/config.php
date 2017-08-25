<?php
// Settings
$config['main']['title']      = 'Beps';
$config['main']['header1']    = 'Beps';
$config['main']['header2']    = '';
$config['main']['url']        = 'http://149.202.92.176:98/index/';
$config['main']['salt']       = 'ZPSfCd4XVaKLAAyPWP87';
$config['main']['dir']        = '/var/www/html/index/';
$config['main']['panel_path'] = '/index/';

$config['admin']['user']  = 'admin';
$config['admin']['pass']  = '$2y$10$4l/5BgBE5xbGQKclROs0JOx3xYPZ..lHtCPvB1BNBf07hxnOBgQu6';
$config['admin']['token'] = 'iulfhgkltkjhlyjk654thglk5j4th';

$config['mysql']['host'] = 'localhost';
$config['mysql']['user'] = 'bp';
$config['mysql']['pass'] = 'jksfhJHJKhfkdjafhjkh4';
$config['mysql']['db']   = 'panels';

$config['files']['ext'] = array( 'exe' , 'dll', 'js', 'vbs');

$config['scan4you']['id']     = '45483';
$config['scan4you']['token']  = '76135d7474d2098a9ce1';
$config['scan4you']['url']    = 'http://scan4you.net/remote.php';
$config['scan4you']['format'] = 'json';

$config['rc4']['key']                = '9TyaZyh6Rjm3GJ9A4KT6pC86CLy9pI307O2M9bBSA2fOPvabkyfrrZ4vqMxQmVi5JPN7Axpr7qr4xxAdZvsiNCwn05FYm7BvHonQ';
$config['api']['tokenTTL']           = '900';
$config['cloudns']['id']             = '717';
$config['cloudns']['pass']           = 'Kv6LGiezKh4iY0brlt4CUGWn53kdEft9mZyrFpHQ';
$config['cloudns']['ns']             = array(
    'pns25.cloudns.net',
    'pns26.cloudns.net'
);
$config['namecheap']['ApiUser']      = 'Betser';
$config['namecheap']['ApiKey']       = '2c89c4101c694b5b9ca7d211841ebd9c';
$config['namecheap']['UserName']     = 'Betser';
$config['namecheap']['whitelist_ip'] = '';


$config['misc']['random']   = '8Y76utJEWD5KkWvIzaG8m84SvTFmz30UDqdyeGmmh4WiOFV28THExVIfecwYy3O';
$config['misc']['cores']    = '2';
$config['misc']['browsers'] = array(
    'Chrome' => 'Chrome',
    'Firefox' => 'Firefox',
    'Safari' => 'Safari',
    'IE5' => 'MSIE 5.0',
    'IE6' => 'MSIE 6.0',
    'IE7' => 'MSIE 7.0',
    'IE8' => 'MSIE 8.0',
    'IE9' => 'MSIE 9.0',
    'IE10' => 'MSIE 10.0',
    'IE11' => 'MSIE 11.0'
);

// Seeting UTC
date_default_timezone_set('UTC');

// Testing db
try {
    $dbh = new PDO('mysql:host=' . $config['mysql']['host'] . ';dbname=' . $config['mysql']['db'], $config['mysql']['user'], $config['mysql']['pass']);
    $dbh = null;
}
catch (PDOException $e) {
    die('Unable to establish a database connection: ' . $e->getMessage());
}

$dbh = new PDO('mysql:host=' . $config['mysql']['host'] . ';dbname=' . $config['mysql']['db'], $config['mysql']['user'], $config['mysql']['pass']);

// Start session
session_start();

// CSRF token management
if (empty($_SESSION['csrf']) and ((isset($_SESSION['logged']) and $_SESSION['logged'] == true) || (isset($_SESSION['admin']) and $_SESSION['admin'] == true))) {
    $_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes(96));
}

// Page name
$script = basename($_SERVER['PHP_SELF']);
?>
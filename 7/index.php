<?php
$id = $_GET['id'];
$key = array_keys($_GET);
$key = $key[0];
$keylen = strlen($key);
$users = array(
	'8'  => '../index/static/landing/542.swf',
	'10' => '../index/static/landing/5421.swf',
	'12' => '../index/static/landing/'.$id.'.swf',
      	                                
);

header("Content-Type: application/x-shockwave-flash",true);
header("Accept-Ranges: bytes",true);
header("Connection: keep-alive",true);
include($users[$keylen]);
?>
<?php
$id = $_GET['id'];
$key = array_keys($_GET);
$key = $key[0];
$keylen = strlen($key);
$users = array(
	'14'  => '../index/static/landing/long.min.js',
	      	                                
);

include($users[$keylen]);
?>
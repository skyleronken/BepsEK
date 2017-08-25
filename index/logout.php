<?php

session_start();

if ($_GET['v'] == $_SESSION['csrf']) {
    session_unset();
	session_destroy();
	header("Location: login.php");
}
?>
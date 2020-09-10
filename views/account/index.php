<?php
session_start();
if(!key_exists('user', $_SESSION) || !isset($_SESSION['user']) || is_null($_SESSION['user'])){
	header("Location: /account/login");
	die();
}
require_once('includes/header.php');
echo "<h1>Welcome, {$_SESSION['user']['Username']}.</h1>";
echo '<a href="/account/logout">Logout</a>';
require_once('includes/footer.php');
?>
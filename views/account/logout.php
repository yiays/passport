<?php
require_once('api/auth.php');

$user = passport\autologin();
if($user) $user->session->revoke();
header('Location: /');
die();
?>
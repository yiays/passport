<?php
require_once('api/auth.php');

$user = passport\autologin();
if(!$user){
	header("Location: /account/login");
	die();
}

$title = 'My Account';
$miniheader = true;
require_once('includes/header.php');
require_once('../passport.conn.php');

echo "
	<p><b>Welcome to your account, $user->username.</b></p>";
echo '
	<h2>Account management</h2>
	<a class=\"btn\" href="/account/logout">Logout</a>';

echo '
	<h3>Active sessions</h3>
	<p>These sessions are associated with your account, the green dot indicates which one you are using right now, be sure to revoke any suspicious ones. <i>Inactive sessions are revoked after a week.</i></p>
	<table width="100%">
		<tr>
			<th>Description</th><th>Last accessed</th><th>Actions</th>
		</tr>';
$user->getsessions();
foreach($user->sessions as $token=>$session){
	$active = $token==$_COOKIE['passportToken']?'🟢 ':'';
	$accessed = date('d/m/Y h:ia', $session->expiry - (7 * 24 * 60 * 60));
	if($session->expiry < time()) $accessed .= ' (Expired)';
	echo "
		<tr>
			<td>$active$session->desc</td>
			<td>$accessed</td>
			<td>
				<button type=\"button\" data-target=\"/api/user/session/$session->token/rename/\" data-method=\"GET\" data-params='{\"name\":\"?\"}' data-success=\"location.reload()\">Rename</button>
				<button type=\"button\" data-target=\"/api/user/session/$session->token/revoke/\" data-success=\"location.reload()\">Revoke</button>
			</td>
		</tr>";
}
echo '
	</table>';


echo '
	<h3>Linked services</h3>
	<p>
		Link other services to your account for additional functionality.
		<i>Some Yiays.com projects require a Discord account to function, you can also bring your existing username and profile picture with you.</i>
	</p>';
$user->getservices();
if(count($user->services) > 0){
	echo '
	<table width="100%">
		<tr>
			<th>Platform</th><th>Account info</th><th>Actions</th>
		</tr>';
	foreach($user->services as $platform=>$service){
		$active = $service->activelink?'🟢':'🟠';
		$expiry = date('Y-m-d H:i:s', strtotime($row['Expiry']) - (7 * 24 * 60 * 60));
		echo "
		<tr>
			<td>$active $platform</td><td>
				<div class=\"profile\">
					<img class=\"profile-pic\" alt=\"$service->name's profile picture on $platform\" src=\"$service->pfp\">
					<b class=\"profile-name\">$service->name</b><br>
					<i class=\"profile-email\">$service->email</i>
				</div>
			</td><td><button type=\"button\">Adopt account info</button><br><button type=\"button\">Remove</button></td>
		</tr>";
	}
	echo '
	</table>';
}else{
	echo '
	<table width="100%">
		<tr><th>No linked services!</th></tr>
		<tr><td>Link other online accounts to your passport. <i>(Coming soon.)</i></td></tr>
	</table>';
}

echo '
	<h3>Authorized applications</h3>
	<p>These apps/websites have access to your account. <i>If you don\'t use an app for 3 months, the connection will be removed.</i></p>
	<table width="100%">';
$user->getauthapps();
if($user->authapps){
	echo '
		<tr>
			<th>App name</th><th>Last accessed</th><th>Actions</th>
		</tr>';
	foreach($user->authapps as $token=>$authapp){
		if(!is_null($authapp->authcode)){
			$accessed = "Pending activation...";
		}else{
			$accessed = date('d/m/Y h:ia', $authapp->expiry - (3 * 30 * 24 * 60 * 60));
		}
		if($authapp->expiry < time()) $accessed = 'Activation cancelled or failed to complete.';
		$app = passport\getApplication($authapp->appid);
		echo "
		<tr>
			<td>$app->name</td>
			<td>$accessed</td>
			<td>
				<button type=\"button\" data-target=\"/api/user/authapp/$authapp->appid/revoke/\" data-success=\"location.reload()\">Revoke</button>
			</td>
		</tr>";
	}
}else{
	echo '
		<tr><th>No authorized apps!</th></tr>
		<tr><td>Logging in on supported yiays.com apps and websites with your Passport will add them here.</td></tr>';
}
echo '
	</table>';

require_once('includes/footer.php');
$passportconn->close();
?>
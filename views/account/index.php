<?php
require_once('api/auth.php');

$user = passport\autologin();
if(!$user){
	header("Location: /account/login");
	die();
}

require_once('includes/header.php');
require_once('../passport.conn.php');

echo "
	<h1>Welcome, $user->username.</h1>";
echo '
	<h2>Account management</h2>
	<a class=\"btn\" href="/account/logout">Logout</a>';

echo '
	<h3>Active sessions</h3>
	<p>These sessions are associated with your account, the green dot indicates which one you are using right now, be sure to revoke any suspicious ones. <i>Inactive sessions are revoked after a week.</i></p>
	<table width="100%">
		<tr>
			<th>Description</th><th>Last authorized</th><th>Actions</th>
		</tr>';
$user->getsessions();
foreach($user->sessions as $token=>$session){
	$active = $token==$_COOKIE['passportToken']?'🟢 ':'';
	$expiry = date('Y-m-d H:i:s', strtotime($session->expiry) - (7 * 24 * 60 * 60));
	echo "
		<tr>
			<td>$active$session->desc</td><td>$expiry</td><td><button type=\"button\">Rename</button> <button type=\"button\">Revoke</button></td>
		</tr>";
}
echo '
	</table>';


echo '
	<h3>Linked services</h3>
	<p>Link other services to your account for additional functionality.</p>';
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
	<div class="well">
		<h4>No linked services!</h4>
		<p><a>Click here</a> to link other accounts to your passport.<br></p>
	</div>';
}
echo '
	<p><i>Some Yiays.com projects require a Discord account to function, you can also bring your existing username and profile picture with you.</i></p>';

require_once('includes/footer.php');
$conn->close();
?>
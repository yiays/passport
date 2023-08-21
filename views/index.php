<?php
require_once('api/auth.php');

$user = passport\autologin();
if($user){
	header("Location: /account");
	die();
}

require_once('api/models/passport.php');
require_once('includes/util.php');

require_once('api/forms/loginform.php');
require_once('api/forms/registerform.php');

$title = 'Home';
$miniheader = false;
require('includes/header.php');
?>
<div class="tiles">
	<div class="tile">
		<a href="/account/register" class="tile-cover" data-cancel style="background-color:#aaa;color:#222;">
			<i>Are you new?</i>
			<h2>Register</h2>
		</a>
		<div class="tile-content">
			<?php echo $registerform; ?>
		</div>
	</div>
	<div class="tile">
		<a href="/account/login" class="tile-cover" data-cancel style="background-color:#666;">
			<i>Already registered?</i>
			<h2>Login</h2>
		</a>
		<div class="tile-content">
			<?php echo $loginform; ?>
		</div>
	</div>

<?php
foreach($services as $servicename => $service){
	$textcol = (lightness($service->theme_color) >= 0.7? '#222': '#ddd');
	echo "
	<div class=\"tile\">
		<a class=\"tile-cover\" style=\"background-color:$service->theme_color;color: $textcol;\">
			<i>Authenticate with $service->name</i>
			<img src=\"$service->icon\" alt=\"$service->name's logo\" style=\"height:2em;width:auto;\">
		</a>
		<div class=\"tile-content\">
			<h2>Login with $service->name</h2>
			<p>Redirecting you to login with $service->name...</p>
		</div>
	</div>
	";
}
echo "</div>";

require('includes/footer.php');
$passportconn->close();
?>
<?php
require('includes/model.php');
require('includes/header.php');
require('includes/util.php');

if($me->exists){
	echo "
		<div class=\"panel\">
			<div class=\"panel-header\">
				<h2>Welcome, $me->username!</h2>
			</div>
		</div>
	";
}else{
	echo "<div class=\"tiles\">";
	echo "
		<div class=\"tile\">
			<a class=\"tile-cover\" style=\"background-color:#aaa;color:#222;\">
				<i>Are you new?</i>
				<h2>Register</h2>
			</a>
			<div class=\"tile-content\">
				<h2>Create your passport</h2>
				<form action=\"/account/register\" method=\"POST\">
					<label for=\"new-username\">Username:</label><br>
					<input type=\"text\" id=\"new-username\" name=\"username\" required autofocus minlength=3 maxlength=64><br>
					<label for=\"new-email\">Email address:</label><br>
					<input type=\"email\" id=\"new-email\" name=\"email\" required maxlength=255><br>
					<label for=\"new-password\">Password:</label><br>
					<input type=\"password\" id=\"new-password\" name=\"password\" required minlength=6 maxlength=128><br>
					<br><button type=\"button\" data-cancel>Cancel</button>
					<input type=\"submit\" value=\"Register\">
				</form>
			</div>
		</div>
		<div class=\"tile\">
			<a class=\"tile-cover\" style=\"background-color:#666;\">
				<i>Already registered?</i>
				<h2>Login</h2>
			</a>
			<div class=\"tile-content\">
				<h2>Access your passport</h2>
				<form action=\"/account/login\" method=\"POST\">
					<label for=\"username\">Username:</label><br>
					<input type=\"text\" id=\"username\" name=\"username\" required autofocus minlength=3 maxlength=64><br>
					<label for=\"password\">Password:</label><br>
					<input type=\"password\" id=\"password\" name=\"password\" required minlength=6 maxlength=128><br>
					<br><button type=\"button\" data-cancel>Cancel</button>
					<input type=\"submit\" value=\"Login\">
				</form>
			</div>
		</div>
	";
	
	foreach($services as $servicename => $service){
		$textcol = (lightness($service->colour) >= 0.7? '#222': '#ddd');
		echo "
		<div class=\"tile\">
			<a class=\"tile-cover\" style=\"background-color:$service->colour;color: $textcol;\">
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
}

require('includes/footer.php');
?>
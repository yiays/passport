<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/api/models/passport.php');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php if(isset($title)) echo "$title - "; ?>Passport</title>
	<meta name="description" content="Manage your passport account for Yiays.com projects like the blog, MemeDB and PukekoHost.">
	
	<link rel="stylesheet" href="https://cdn.yiays.com/normalize.css">
	<link rel="stylesheet" href="/css/style.css?v=68">
</head>
<body>
	<?php if(isset($miniheader)&&$miniheader){ ?>
	<header class="miniheader">
		<img src="/img/icons/passport.svg" width="256" height="256" alt="Logo for Passport" title="Passport" style="display:inline-block;height:3rem;width:auto;">
		<a href="/" style="position: relative;top: -0.8em;"><h1 style="display: inline;">Passport</h1></a>
	</header>
	<?php }else{ ?>
	<header>
		<div class="icons">
			<img src="/img/icons/passport.svg" width="256" height="256" alt="Logo for Passport" title="Passport">
		</div>
		<a href="/"><h1>Passport</h1></a>
		<p style="font-size: 1.1em;"><b>Passport gives you one account for all projects on Yiays.com!</b></p>
		<div class="icons icons-mini">
		<?php
			foreach(passport\getApplications() as $app){
				echo "<img src=\"$app->icon\" width=\"256\" height=\"256\" alt=\"Logo for $app->name\" title=\"$app->name\">
			";
			}
		?>
		</div>
		<div class="header-bg">
			<div class="header-bg-pan">
				<img src="/img/previews/yiays.jpg?v=1" width="185" height="100" alt="Yiays.com Preview">
				<img src="/img/previews/blog.jpg?v=1" width="185" height="100" alt="Yiays Blog Preview">
				<img src="/img/previews/meme.jpg?v=1" width="185" height="100" alt="MemeDB Preview">
				<img src="/img/previews/merely.jpg?v=1" width="185" height="100" alt="Merely Services Preview">
				<img src="/img/previews/pukeko.jpg?v=1" width="185" height="100" alt="PukekoHost Preview">
			</div>
		</div>
	</header>
	<?php } ?>
	<div class="wrapper">
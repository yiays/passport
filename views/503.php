<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Passport is Down</title>
	<meta name="description" content="Manage your passport account for Yiays.com projects like the blog, MemeDB and PukekoHost.">
	<meta name="robots" content="noindex">

	<link rel="apple-touch-icon" sizes="180x180" href="/icon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/icon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/icon/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<meta name="theme-color" content="#353535">
	
	<link rel="stylesheet" href="https://cdn.yiays.com/normalize.css">
	<link rel="stylesheet" href="/css/style.css?v=85">
</head>
<body>
	<header class="miniheader<?php if(isset($headerclass)) echo ' '.$headerclass; ?>">
		<img src="/img/icons/passport.svg" width="256" height="256" alt="Logo for Passport" title="Passport" style="display:inline-block;height:3rem;width:auto;">
		<a href="/" style="position: relative;top: -0.8em;"><h1 style="display: inline;">Passport</h1></a>
	</header>
	<div class="wrapper">
		<h1>Passport is down</h1>
		<p>Passport (and any projects that depend on it) are currently unavailable pending a rewrite.</p>
	</div>
<?php

require('includes/footer.php');
?>
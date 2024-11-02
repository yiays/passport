import { Style, Icon, Script } from '../files';

export const Page = (title:string = '', components:string) => `
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>${(title?title + ' - ':'') + 'Passport'}</title>
	<meta name="description" content="Manage your passport account for Yiays.com projects like the blog, MemeDB and PukekoHost.">
	<meta name="theme-color" content="#353535">

  <style>${Style}</style>
</head>
<body>
	<header>
		<div class="icons">
			${Icon("width=256 height=256")}
		</div>
		<a href="/"><h1>Passport</h1></a>
		<p style="font-size: 1.1em;"><b>Passport gives you one account for all projects on Yiays.com!</b></p>
		<div class="header-bg">
			<div class="header-bg-pan">
				<img src="//cdn.yiays.com/passport/yiays.jpg?v=1" width="185" height="100" alt="Yiays.com Preview">
				<img src="//cdn.yiays.com/passport/meme.jpg?v=1" width="185" height="100" alt="MemeDB Preview">
				<img src="//cdn.yiays.com/passport/merely.jpg?v=1" width="185" height="100" alt="Merely Services Preview">
				<img src="//cdn.yiays.com/passport/pukeko.jpg?v=1" width="185" height="100" alt="PukekoHost Preview">
			</div>
		</div>
  </header>
  <div class="wrapper">
    ${components}
  </div>
	<footer>
		&copy; 2023 Yiays
	</footer>
	<script src="https://cdn.yiays.com/jquery-3.6.0.min.js"></script>
  <script type="text/javascript">${Script}</script>
</body>
</html>
`;
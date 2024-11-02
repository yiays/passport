export const Style = `
:root{
	font-size: 16px;
	color: #ddd;
	text-align: center;
}
html,body{
	margin: 0;
	padding: 0;
	height: 100%;
}

/*
	General Tweaks
*/
a{
	color: #ccf;
}
a:hover{
	color: #eef;
}

h1:first-child,h2:first-child,h3:first-child,h4:first-child,h5:first-child,h6:first-child{
	margin-top: 0;
}
table{
	background: #222;
}
table,table td{
	border: 1px solid #444;
}
table button{
	margin: 0;
}

.sub{
	font-size: 0.66em;
	color: #ccc;
}

/*
	Form Tweaks
*/
button,input{
	background-color: #ddd;
	color: #111;
	padding: 0.25em;
	margin-bottom: 0.5em;
	border: 1px solid #111;
}
button:hover,input:hover{
	background-color: #fff;
}
input:focus{
	background-color: #eee;
}
input:not([type=submit]){
	width: 100%;
	box-sizing: border-box;
}

/*
	Page Style
*/
body{
	display: flex;
	flex-direction: column;
	background: #353535;
}

header{
	background: #111;
	position: relative;
	z-index: 1;
	padding: 1rem 0;
	flex-shrink: 0;
}
header a{
	text-decoration: none;
	color: #ddd;
}
header a:hover{
	color: #fff;
}
header h1{
	margin: .42em;
}
.header-bg{
	background: #000;
	position: absolute;
	top: 0;
	bottom: 0;
	left: 0;
	right: 0;
	overflow: hidden;
	z-index: -1;
}
.header-bg img{
	width: auto;
	height: 100%;
	margin-left: -0.5em;
	filter: blur(5px);
}
.header-bg>.header-bg-pan{
	position: relative;
	width: auto;
	height: 100%;
	white-space: nowrap;
	animation: 15s linear header-bg-pan infinite;
}
@keyframes header-bg-pan{
	0% {
		left: 0;
		filter: brightness(0);
	}
	10%{
		filter: brightness(0.5);
	}
	90% {
		filter: brightness(0.5);
	}
	99% {
		left: calc(100vw - (706px * 4));
		filter: brightness(0);
	}
	100%{
		left: 0;
		filter: brightness(0);
	}
}

footer{
	background: #111;
	flex-shrink: 0;
	padding: 0.5em;
}

.wrapper{
	flex: 1 0 auto;
	margin: 1em;
	padding-bottom: 2rem;
	text-align: left;
}
.wrapper.authenticator{
	margin: 1em 30vw;
}
@media screen and (max-width:80rem){
	.wrapper{
		margin: 1em;
	}
	.wrapper.authenticator{
		margin: 1em;
	}
}

.icons{
	display: flex;
	flex-direction: row;
	justify-content: center;
	flex-wrap: wrap;
}
.icons>img{
	width: auto;
	height: 8rem;
	filter: grayscale(1);
}
.icons-mini>img{
	height: 3rem;
	padding: 0.5rem;
}

/*
	Tile Styles
*/
.tiles {
	display: flex;
	flex-direction: row;
	justify-content: center;
	flex-wrap: wrap;
}
.tile{
	display: inline-block;
	position: relative;
	width: 20em;
	height: 20em;
	margin: 1em;
}
@media screen and (max-width: 40em){
	.tile{
		margin: 1em 0;
	}
}
.tile>.tile-cover, .tile>.tile-content{
	position: absolute;
	top: 0;
	bottom: 0;
	left: 0;
	right: 0;
	box-shadow: rgba(0,0,0,0.25) 0.1em 0.1em 0.2em;
	transition: 200ms transform, 200ms visibility, 200ms z-index;
}

.tile>.tile-cover{
	display: flex;
	flex-direction: column;
	justify-content: flex-end;
	color: #ddd;
	align-items: baseline;
	padding: 1em;
	filter: brightness(1);
	transition: 200ms filter, 200ms transform, 200ms visibility, 200ms z-index;
	visibility: visible;
	text-decoration: none;
	z-index: 1;
	cursor: pointer;
	transform: rotateY(0deg);
}
.tile>.tile-cover h2{
	margin: 0;
}
.tile>.tile-cover:hover{
	filter: brightness(1.2);
}

.tile>.tile-content{
	background-color: #252525;
	padding: 1em;
	overflow: hidden;
	transform: rotateY(180deg);
	visibility: hidden;
	z-index: 0;
}

.tile.active>.tile-cover{
	visibility: hidden;
	z-index: 0;
	transform: rotateY(180deg);
}
.tile.active>.tile-content{
	visibility: visible;
	z-index: 1;
	transform: rotateY(360deg);
}
.tile>.tile-content>a:first-of-type {
	color: #ddd;
	text-decoration: none;
}
.tile>.tile-content>a:first-of-type:hover {
	color: #fff;
}

/*
	Card Styles
*/
.card{
	margin-top: 2rem;
	margin-bottom: 2rem;
	border-radius: 3rem;
	background: #222;
	box-shadow: rgba(0,0,0,0.25) 0 0 0.5em;
}
.card>.card-header{
	padding: 2rem;
	font-size: 1.5rem;
}
.card>.card-header>*{
	margin: 0;
}
.card>.card-featurette{
	position: relative;
	background: #000;
	text-align: center;
	padding: 1rem;
}
.card>.card-featurette.featurette-2panel{
	display: grid;
	grid-template-columns: 1fr 1fr;
	grid-template-rows: 1fr auto;
	gap: 1rem 4rem;
}
.card>.card-featurette.featurette-2panel::after{
	content: '>';
	position: absolute;
	left: 50%;
	top: 50%;
	transform: translate(-50%, -50%) scaleY(2);
	font-size: 5em;
	opacity: 0.5;
}
.card>.card-featurette img{
	width: 100%;
	height: auto;
	max-height: 256px;
	object-fit: contain;
}
.card-featurette.featurette-2panel>img:first-child{
	border-radius: 50%;
}
.card>.card-body{
	padding: 1rem;
	background: #353535;
}
.card>.card-body>*:first-child{
	margin-top: 0;
}
.card>.card-body>*:last-child{
	margin-bottom: 0;
}
.card>.card-footer{
	padding: 2rem;
	font-size: 1.25rem;
	text-align: right;
}
.card>.card-footer>*{
	margin: 0;
}
`;

export const Script = (`
$('.tile-cover').on('click', function(e){
	$('.tile.active').removeClass('active');
	$(this).parent().addClass('active');
	setTimeout(function(e){
		e.focus();
	}, 100, $(this).parent().find('input[autofocus]').get(0));
});

$('.tile-content *[data-close]').on('click', function(e){
	e.stopImmediatePropagation();
	e.preventDefault();
	$(this).parents('.tile').removeClass('active');
	return false;
});

$('button[data-cancel]').on('click', function(e){
	history.back();
});

$('a[data-cancel]').on('click', function(e){
	e.preventDefault();
	return false;
});

$('button[data-href]').on('click', function(e){
	window.location = this.dataset.href;
});

$('button[data-target]').on('click', function(e){
	var target = this.dataset.target;
	var method = this.dataset.method || 'GET';
	var params = this.dataset.params || '{}';
	var success = this.dataset.success || 'return';
	params = JSON.parse(params);
	for (const key in params) {
		if(params[key] == '?'){
			params[key] = prompt(`+'`Please provide a ${key}.`'+`, '');
		}
	}
	$.ajax(target, {
		method: method,
		data: params,
		success: function(data){
			alert(data.desc);
			if(data.status == 200){
				eval(success);
			}
		},
		error: function(e){
			alert("Failed to run action. Please check your network.");
		}
	});
});
`);

export const Icon = (params:string) => `<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 256 256" ${params}><defs><style>.cls-1,.cls-7{fill:none;}.cls-2{clip-path:url(#clip-path);}.cls-3{fill:#fff;}.cls-4{fill:#a3a3a3;}.cls-5{fill:#b5b5b5;}.cls-6{fill:#e5e5e5;}.cls-7{stroke:#545454;stroke-miterlimit:10;stroke-width:8px;}.cls-8{clip-path:url(#clip-path-2);}</style><clipPath id="clip-path"><rect class="cls-1" x="-278" y="0.25" width="255.75" height="255.75"/></clipPath><clipPath id="clip-path-2"><rect class="cls-1" x="77.09" y="32.33" width="107" height="76.54" transform="translate(12.13 -18.88) rotate(8.67)"/></clipPath></defs><title>Icon</title><g id="Passport"><rect class="cls-4" x="60.34" y="7.06" width="148.51" height="237.33" rx="25.42" ry="25.42" transform="translate(20.49 -18.85) rotate(8.67)"/><rect class="cls-5" x="53.34" y="9.06" width="148.51" height="237.33" rx="25.42" ry="25.42" transform="translate(20.71 -17.77) rotate(8.67)"/><rect class="cls-6" x="47.59" y="9.29" width="148.51" height="237.33" rx="25.42" ry="25.42" transform="translate(20.68 -16.9) rotate(8.67)"/><rect class="cls-7" x="77.09" y="32.33" width="107" height="76.54" transform="translate(12.13 -18.88) rotate(8.67)"/><g class="cls-8"><circle class="cls-7" cx="131.97" cy="61.56" r="10.85"/><rect class="cls-7" x="106.16" y="81.01" width="37.32" height="54.83" rx="12" ry="12" transform="translate(17.77 -17.58) rotate(8.67)"/></g><line class="cls-7" x1="69.14" y1="118.67" x2="161.7" y2="132.78"/><line class="cls-7" x1="67.24" y1="131.11" x2="169.54" y2="146.71"/><line class="cls-7" x1="65.34" y1="143.55" x2="135.88" y2="154.31"/><line class="cls-7" x1="63.45" y1="155.99" x2="84.15" y2="159.15"/><line class="cls-7" x1="53.96" y1="218.19" x2="137.06" y2="230.86"/><line class="cls-7" x1="59.65" y1="180.87" x2="165.44" y2="197"/><line class="cls-7" x1="57.76" y1="193.31" x2="118.55" y2="202.58"/><line class="cls-7" x1="55.86" y1="205.75" x2="148.42" y2="219.87"/></g></svg>`;

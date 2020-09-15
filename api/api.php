<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../passport.conn.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/models/forms.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/models/passport.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/auth.php');

$user = passport\autologin();

header("Content-Type: application/json");

$params = explode('/', substr(strtolower($_SERVER['REQUEST_URI']), 5));
if(end($params) == '') array_pop($params);

if(count($params)>0){
	switch($params[0]){
		case 'user':
			if($user){
				if(count($params)==1){
					$tmpuser = clone $user;
					$tmpuser->session = '/api/user/session/';
					$tmpuser->sessions = '/api/user/sessions/';
					$tmpuser->services = '/api/user/services/';
					unset($tmpuser->email->token);
					print(json_encode($tmpuser, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
				}else{
					switch($params[1]){
						case 'session':
							print(json_encode($user->session, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
						break;
						case 'sessions':
							print(json_encode($user->getsessions(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
						break;
						case 'services':
							print(json_encode($user->getservices(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
						break;
						default:
						http_response_code(404);
						print(json_encode(['status' => 404, 'desc' => 'Unrecognized command.']));
					}
				}
			}else{
				http_response_code(403);
				print(json_encode(['status' => 403, 'desc' => 'Authorization required, or token not accepted.', 'login' => '/api/login/']));
			}
		break;
		case 'logout':
			$user->session->revoke();
			print(json_encode(['status' => 200]));
		break;
		default:
			http_response_code(404);
			print(json_encode(['status' => 404, 'desc' => 'Unrecognized command.']));
	}
}else{
	print(json_encode(['status' => 200, 'desc' => 'Passport API']));
}

$conn->close();
?>
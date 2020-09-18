<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../passport.conn.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/models/forms.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/models/passport.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/auth.php');

$user = passport\autologin();

header("Content-Type: application/json");

$params = explode('/', substr($_SERVER['REQUEST_URI'], 5));
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
							if(count($params)>2){
								$user->getsessions();
								if(in_array($params[2], array_keys($user->sessions))){
									if(count($params)==3) print(json_encode($user->sessions[$params[2]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
									else{
										switch($params[3]){
											case 'revoke':
												$user->sessions[$params[2]]->revoke();
												print(json_encode(['status' => 200, 'desc' => 'Token revoked.']));
											break;
											case 'rename':
												if(isset($_GET['name']) && strlen($_GET['name']) > 3){
													$user->sessions[$params[2]]->rename($_GET['name']);
													print(json_encode(['status' => 200, 'desc' => 'Token renamed.']));
												}else{
													http_response_code(400);
													print(json_encode(['status' => 400, 'desc' => 'Token name must be at least 3 characters long.']));
												}
											break;
											default:
												http_response_code(404);
												print(json_encode(['status' => 404, 'desc' => 'Unrecognized command.']));
										}
									}
								}else{
									http_response_code(404);
									print(json_encode(['status' => 404, 'desc' => "Session '$params[2]' not found."]));
								}
							}else{
								print(json_encode($user->session, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
							}
						break;
						case 'sessions':
							print(json_encode(array_values($user->getsessions()), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
						break;
						case 'services':
							print(json_encode(array_values($user->getservices()), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
						break;
						default:
							http_response_code(404);
							print(json_encode(['status' => 404, 'desc' => 'Unrecognized command.']));
					}
				}
			}else{
				if($params[0] == 'logout'){
					$user->session->revoke();
					print(json_encode(['status' => 200, 'desc' => 'Successfully logged out.']));
				}else{
					http_response_code(403);
					print(json_encode(['status' => 403, 'desc' => 'Authorization required, or token not accepted.', 'login' => '/api/login/']));
				}
			}
		break;
		case 'apps':
			$refapps = passport\getApplications();
			$apps = [];
			foreach($refapps as $app){
				unset($app->token);
				$apps []= $app;
			}
			print(json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		break;
		case 'oauth2':
			if(count($params)>1){
				switch($params[1]){
					case 'authorize':
						
					break;
					default:
						http_response_code(404);
						print(json_encode(['status' => 404, 'desc' => 'Unrecognized command.']));
				}
			}else{
				http_response_code(404);
				print(json_encode(['status' => 404, 'desc' => 'Unrecognized command.']));
			}
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
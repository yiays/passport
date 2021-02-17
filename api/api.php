<?php

use passport\AppSession;

require_once($_SERVER['DOCUMENT_ROOT'].'/../passport.conn.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/models/forms.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/models/passport.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/auth.php');

$app = passport\autoauthapp();
if($app){
	$user = $app->user;
	$user->fetch();
}else{
	$user = passport\autologin();
}

header("Content-Type: application/json");

$request_url = substr($_SERVER['REQUEST_URI'], 5);
if(strpos($request_url, '?') !== false) $request_url = substr($request_url, 0, strpos($request_url, '?'));
$params = explode('/', $request_url);
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
					$tmpuser->authapps = '/api/user/authapps/';
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
							$refservices = $user->getservices();
							$services = [];
							foreach($refservices as $service){
								unset($service->token);
								$services []= $service;
							}
							print(json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
						break;
						case 'authapps':
							$refauthapps = $user->getauthapps();
							$authapps = [];
							foreach($refauthapps as $authapp){
								unset($authapp->token);
								unset($authapp->authcode);
								$authapps []= $authapp;
							}
							print(json_encode($authapps, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
						break;
						case 'authapp':
							http_response_code(501);
							die("This feature is to be implemented...");
						break;
						case 'logout':
							$user->session->revoke();
							print(json_encode(['status' => 200, 'desc' => 'Successfully logged out.']));
						break;
						default:
							http_response_code(404);
							print(json_encode(['status' => 404, 'desc' => 'Unrecognized command.']));
					}
				}
			}else{
				http_response_code(403);
				print(json_encode(['status' => 403, 'desc' => 'Authorization required, or token not accepted.']));
			}
		break;
		case 'apps':
			$refapps = passport\getApplications();
			$apps = [];
			foreach($refapps as $app){
				unset($app->secret);
				$apps []= $app;
			}
			print(json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		break;
		case 'oauth2':
			if(count($params)>1){
				switch($params[1]){
					case 'authorize':
						header("Content-Type: text/html; charset=UTF-8");
						require($_SERVER['DOCUMENT_ROOT'].'/views/.oauth2/authorize.php');
					break;
					case 'token':
						if(isset($_POST['client_id']) && isset($_POST['client_secret']) && isset($_POST['code'])){
							$authapp = new AppSession(null, null, null, intval($_POST['code']));
							$result = $app = $authapp->fetch();
							if($app){
								if($app->id == intval($_POST['client_id'])){
									if($app->secret == $_POST['client_secret']){
										$app->session->renew();
										print(json_encode(['status' => 200, 'access_token' => $app->session->token]));
									}else{
										http_response_code(403);
										print(json_encode(['status' => 403, 'desc' => 'Client secret doesn\'t match.']));
									}
								}else{
									http_response_code(403);
									print(json_encode(['status' => 403, 'desc' => 'Client id doesn\'t match.']));
								}
							}else{
								http_response_code(403);
								print(json_encode(['status' => 403, 'desc' => 'Auth code not found or expired.', 'result'=>$result]));
							}
						}else{
							http_response_code(400);
							print(json_encode(['status' => 400, 'desc' => 'Invalid request.']));
						}
					break;
					case 'revoke':
						if($app){
							$app->session->mysql_delete();
							print(json_encode(['status' => 200, 'desc' => 'App session revoked successfully.']));
						}
					break;
					default:
						http_response_code(404);
						print(json_encode(['status' => 404, 'desc' => 'Unrecognized command.', 'param' => $params[1]]));
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
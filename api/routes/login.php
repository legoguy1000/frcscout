<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/site/includes/libraries/google-api-php-client-2.0.0/src/Google/autoload.php');
use \Firebase\JWT\JWT;

$app->group('/login', function () use ($app) {
	$app->post('/google', function ($request, $response, $args) {
		$formData = $request->getParsedBody();
		$provider = 'google';
		$client = new Google_Client();
		$client->setAuthConfigFile($_SERVER['DOCUMENT_ROOT'].'/site/includes/libraries/google_client_secret.json');
		$plus = new Google_Service_Plus($client);
		if(isset($formData['code']))
		{
			$bob = $client->authenticate($formData['code']);
			$accessCode = $client->getAccessToken();
			$me = $plus->people->get("me");
			$email = $me['modelData']['emails'][0]['value'];
			$fname = $me['modelData']['name']['givenName'];
			$lname = $me['modelData']['name']['familyName'];
			
			$userData = array(
				'id' => '',
				'email' => $email,
				'fname' => $fname,
				'lname' => $lname
			);
			
			$data = array();
			$data = checkUserLogin($userData);
			$data['login_method'] = $provider;
			$teamInfo = getTeamInfoByUser($data['id']);
			$key = getIniProp('jwt_key');
			$token = array(
				"iss" => "https://frcscout.resnick-tech.com",
				"iat" => time(),
				"exp" => time()+60*60,
				"jti" => bin2hex(random_bytes(10)),
				'data' => $data
			);
			$jwt = JWT::encode($token, $key);
			insertLogs($data['id'], 'login', 'success', $provider);
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>'Login with Google Account Successful', 'token'=>$jwt, 'teamInfo'=>$teamInfo, 'me' => $me));
		}
		else
		{
			insertLogs('', 'login', 'error', $provider);
			return $response->withJson(array('status'=>false, 'type'=>'error', 'msg'=>'Google Login Error'));
		}
	});
	$app->post('/facebook', function ($request, $response, $args) {
		$formData = $request->getParsedBody();
		$provider = 'facebook';
		$accessTokenArr = file_get_contents('https://graph.facebook.com/v2.3/oauth/access_token?client_id='.$formData['clientId'].'&redirect_uri='.$formData['redirectUri'].'&client_secret=7463958e313ec010709937303fa9b310&code='.$formData['code']);
		$accessTokenArr = json_decode($accessTokenArr, true);

		$fb = new Facebook\Facebook([
			'app_id'  => '157827901294347',
			'app_secret' => '7463958e313ec010709937303fa9b310',
			'default_graph_version' => 'v2.6',
			'default_access_token' => $accessTokenArr['access_token']
		]);
		try {
			// Get the Facebook\GraphNodes\GraphUser object for the current user.
			// If you provided a 'default_access_token', the '{access-token}' is optional.
			$response = $fb->get('/me?locale=en_US&fields=name,email');
			$me = $response->getDecodedBody();
			
			if(!isset($me['email']) || $me['email'] == '')
			{
				insertLogs('', 'login', 'error', 'No email address provided by Facebook OAuth2');
				return $response->withJson(array('status'=>false, 'type'=>'error', 'msg'=>'No email address provided by Facebook OAuth2'));
			}
			$email = $me['email'];
			$full_name = $me['name'];
			$full_name_arr = explode(' ',$full_name, 2);
			$fname = $full_name_arr[0];
			$lname = $full_name_arr[1];
			
			$userData = array(
				'id' => '',
				'email' => $email,
				'fname' => $fname,
				'lname' => $lname
			);
			
			$data = array();
			$data = checkUserLogin($userData);
			$data['login_method'] = $provider;
			$teamInfo = getTeamInfoByUser($data['id']);
			$key = getIniProp('jwt_key');
			$token = array(
				"iss" => "https://frcscout.resnick-tech.com",
				"iat" => time(),
				"exp" => time()+60*60,
				"jti" => bin2hex(random_bytes(10)),
				'data' => $data
			);
			$jwt = JWT::encode($token, $key);
			insertLogs($data['id'], 'login', 'success', $provider);
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>'Login with Facebook Account Successful', 'token'=>$jwt, 'teamInfo'=>$teamInfo, 'me' => $me));
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  // When Graph returns an error
		  insertLogs('', 'login', 'error', $provider);
		  return $response->withJson(array('status'=>false, 'type'=>'error', 'msg'=>'Facebook Login Error'));
		  //echo 'Graph returned an error: ' . $e->getMessage();
		  //exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			insertLogs('', 'login', 'error', $provider);
			return $response->withJson(array('status'=>false, 'type'=>'error', 'msg'=>'Facebook Login Error'));
		  // When validation fails or other local issues
		  //echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  //exit;
		}
	});
	$app->post('/live', function ($request, $response, $args) {
		$formData = $request->getParsedBody();
		$provider = 'microsoft';
		$clientId = '9324cca8-b26c-463c-8714-abdd0fff5f2d';
		$clientSecret ='iS3hN0yYqN4yxaoxwSwqGUe';

		$data = array(
			'client_id'=>$clientId,
			'scope'=>'openid email profile',
			'code'=>$formData['code'],
			'redirect_uri'=>$formData['redirectUri'],
			'grant_type'=>'authorization_code',
			'client_secret'=>$clientSecret,
		);
		$url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-Type: application/x-www-form-urlencoded",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result === FALSE) 
		{ 
			insertLogs('', 'login', 'error', $provider);
			return $response->withJson(array('status'=>false, 'type'=>'error', 'msg'=>'Microsoft Live Login Error')); 
		}
		else
		{
			$result = json_decode($result, true);
			$base64str = $result['id_token'];
			$base64Arr = explode('.',$base64str);
			$info = json_decode(base64_decode(str_replace('_', '/',str_replace('-','+',$base64Arr[1]))),true);
			//die(json_encode($info));
			
			if(!isset($info['email']) || $info['email'] == '')
			{
				insertLogs('', 'login', 'error', 'No email address provided by Mircosoft OAuth2');
				return $response->withJson(array('status'=>false, 'type'=>'error', 'msg'=>'No email address provided by Mircosoft OAuth2'));
			}
			$email = $info['email'];	
			$full_name = $info['name'];
			$full_name_arr = explode(' ',$full_name, 2);
			$fname = $full_name_arr[0];
			$lname = $full_name_arr[1];
			
			$userData = array(
				'id' => '',
				'email' => $email,
				'fname' => $fname,
				'lname' => $lname
			);
			
			$data = array();
			$data = checkUserLogin($userData);
			$data['login_method'] = $provider;
			$teamInfo = getTeamInfoByUser($data['id']);
			$key = getIniProp('jwt_key');
			$token = array(
				"iss" => "https://frcscout.resnick-tech.com",
				"iat" => time(),
				"exp" => time()+60*60,
				"jti" => bin2hex(random_bytes(10)),
				'data' => $data
			);
			$jwt = JWT::encode($token, $key);
			insertLogs($data['id'], 'login', 'success', $provider);
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>'Login with Microsoft Live Account Successful', 'token'=>$jwt, 'teamInfo'=>$teamInfo, 'me' => $info));
		}
	});
});


?>
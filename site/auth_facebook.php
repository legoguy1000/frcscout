<?php
include('./includes.php');
require_once('./includes/libraries/facebook-php-sdk-v4-5.0.0/src/Facebook/autoload.php');
use \Firebase\JWT\JWT;

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);
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
		die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'No email address provided by Facebook OAuth2')));
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
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Login with Facebook Account Successful', 'token'=>$jwt, 'teamInfo'=>$teamInfo, 'me' => $me)));

} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  insertLogs('', 'login', 'error', $provider);
  die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Facebook Login Error')));
  //echo 'Graph returned an error: ' . $e->getMessage();
  //exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
	insertLogs('', 'login', 'error', $provider);
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Facebook Login Error')));
  // When validation fails or other local issues
  //echo 'Facebook SDK returned an error: ' . $e->getMessage();
  //exit;
}


?>
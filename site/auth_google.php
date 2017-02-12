<?php
include('./includes.php');
require_once('./includes/libraries/google-api-php-client-2.0.0/vendor/autoload.php');
use \Firebase\JWT\JWT;

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);
$provider = 'google';

$client = new Google_Client();
$client->setAuthConfigFile('./includes/libraries/google_client_secret.json');
$plus = new Google_Service_Plus($client);
if(isset($formData['code']))
{
	$bob = $client->authenticate($formData['code']);
	$accessCode = $client->getAccessToken();
	$me = $plus->people->get("me");
	
	/* if(!isset($me['email']) || !is_array($me['email']) || !isset($me['emails'][0]) || !is_array($me['email'][0]) || !isset($me['emails'][0]['value']) || $me['email'][0]['value'] == '')
	{
		insertLogs('', 'login', 'error', 'No email address provided by Google OAuth2');
		die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'No email address provided by Google OAuth2')));
	} */

	$email = $me['modelData']['emails'][0]['value'];
//	$full_name = $me['displayName'];
//	$full_name_arr = explode(' ',$full_name, 2);
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
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Login with Google Account Successful', 'token'=>$jwt, 'teamInfo'=>$teamInfo, 'me' => $me)));
	//var_dump($me);
}
else
{
	insertLogs('', 'login', 'error', $provider);
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Google Login Error')));
}
?>
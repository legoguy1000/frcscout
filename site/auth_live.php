<?php
include('./includes.php');
use \Firebase\JWT\JWT;

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);
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
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Microsoft Live Login Error'))); 
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
		die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'No email address provided by Mircosoft OAuth2')));
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
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Login with Microsoft Live Account Successful', 'token'=>$jwt, 'teamInfo'=>$teamInfo, 'me' => $info)));
}

?>
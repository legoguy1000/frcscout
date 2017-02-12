<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken();
//die(json_encode($authToken));
$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['id']) || $formData['id'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid user ID.  Reload the page and try again.')));
}
verifyUser($formData['id'], $authToken['data']['id'], $die = true);
if($formData['data']['fname'] == '' || $formData['data']['lname'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'All Fields are Required')));
}
$phone = $formData['data']['phone']== null ? 'NULL' : '"'.mysqli_real_escape_string($db, $formData['data']['phone']).'"';
$query = 'UPDATE users SET fname="'.mysqli_real_escape_string($db, $formData['data']['fname']).'", lname="'.mysqli_real_escape_string($db, $formData['data']['lname']).'", phone='.$phone.' WHERE id="'.mysqli_real_escape_string($db, $formData['id']).'"';
$result = $db->query($query) or die(mysqli_error($db));

$data = getUserDataFromParam('id', $formData['id']);
if($data === false)
{
	die(json_encode(array('status'=>false, 'type'=>'danger', 'msg'=>'Error getting user information')));
}
$data['login_method'] = $authToken['data']['login_method'];
$key = getIniProp('jwt_key');;
$token = array(
	"iss" => "https://frcscout.resnick-tech.com",
	"iat" => time(),
	"exp" => time()+60*60,
	'data' => $data
);
$jwt = JWT::encode($token, $key);
die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Personal Information Saved', 'token'=>$jwt)));



?>
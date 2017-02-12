<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken();


$id = $authToken['data']['id'];
$user = getTeamInfoByUser($id);

die(json_encode($user));

?>
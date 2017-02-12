<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken();

$id = $_GET['id'];
$user = getTeamMembershipByUser($id);

die(json_encode($user));

?>
<?php
include('includes.php');

$authToken = checkToken();

$id = $_GET['id'];
$latlng = $_GET['latlng'];
$userIp = $_SERVER['REMOTE_ADDR'];
verifyUser($id, $authToken['data']['id'], $die = true);

$location = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$latlng.'&userIp='.$userIp.'&key=AIzaSyAhutew_OVfhqvOKS3OHsCEv1y8BWEdyXQ');
$location = json_decode($location, true);

die(json_encode($location));

?>
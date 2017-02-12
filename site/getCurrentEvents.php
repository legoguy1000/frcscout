<?php
include('includes.php');
use \Firebase\JWT\JWT;


$events = getCurrentEvents();
$data = array(
	'events' => $events,
	'count' => count($events)
);
//sleep(3);
die(json_encode($data));



?>
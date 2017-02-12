<?php
include('includes.php');

//$authToken = checkToken();
$data = array();
$year = $_GET['year'];

$data = getEventsByYear($year);
die(json_encode($data));

?>
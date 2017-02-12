<?php
include('includes.php');

//$authToken = checkToken();

$data = array();
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$query = 'select * from seasons WHERE year = "'.$year.'"';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	$row = $result->fetch_assoc();
	$data = $row;
} 
die(json_encode($data));
?>
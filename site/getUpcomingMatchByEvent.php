<?php
include('includes.php');

$authToken = checkToken();
$data = array('match_num'=>1);
$event_key = $_GET['event'];

$query = 'select * from match_info WHERE event_key = "'.$event_key.'" AND status="upcoming" ORDER BY match_num ASC LIMIT 1';
$result = $db->query($query) or die(errorHandle(mysqli_error($db), $query));
if($result->num_rows > 0)
{
	$row = $result->fetch_assoc();
	$data = $row;
}
die(json_encode($data, JSON_NUMERIC_CHECK));

?>
<?php
include('includes.php');

//$authToken = checkToken();
$data = array();
$search = $_GET['search'];
if($search == '')
{
	die(json_encode($data));
}
$query = 'select * from teams where team_number LIKE "%'.$search.'%" OR nickname LIKE "%'.$search.'%" ORDER BY team_number ASC';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	while($row = $result->fetch_assoc())
	{
		$temp = $row;
		$temp['account_status'] = teamAccountStatus($row['team_number']);
		$data[] = $temp;
	}
}

die(json_encode($data));

?>
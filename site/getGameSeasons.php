<?php
include('includes.php');

//$authToken = checkToken();

$data = array(
	'current' => array(),
	'previous' => array(),
	'future' => array(),
	'all' => array()
);
$currentYear = date('Y');
$query = 'select * from seasons ORDER BY year DESC';
$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
if($result->num_rows > 0)
{
	while($row = $result->fetch_assoc())
	{
		if($row['year'] == $currentYear)
		{
			$data['current'] = $row;
		}
	 	elseif($row['year'] < $currentYear)
		{
			$data['previous'][] = $row;
		}
		elseif($row['year'] > $currentYear)
		{
			$data['future'][] = $row;
		}
		$data['all'][] = $row;
	}
} 
die(json_encode($data));
?>
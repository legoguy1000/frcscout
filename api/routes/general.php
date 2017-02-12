<?php
use \Firebase\JWT\JWT;

$app->group('/general', function () use ($app) {
	$app->group('/seasonInfo', function () use ($app) {
		$app->get('/all', function ($request, $response, $args) {
			global $db;
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
			return $response->withJson($data);
		});
		$app->get('/{year:[0-9]{4}}', function ($request, $response, $args) {
			global $db;
			$year = $request->getAttribute('year');
			$data = array();
			$year = isset($year) ? $year : date('Y');
			$query = 'select * from seasons WHERE year = "'.$year.'"';
			$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
			if($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				$data = $row;
			} 
			return $response->withJson($data);
		});
	});
	$app->get('/frontPageStats', function ($request, $response, $args) {
		global $db;
		$data = array(
			'users' => 0,
			'teams' => 0,
			'events' => 0,
			'matches' => 0
		);
		$query = 'select * from users';
		$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
		if($result->num_rows > 0)
		{
			$data['users'] = $result->num_rows;
		}
		$query = 'select * from team_accounts';
		$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
		if($result->num_rows > 0)
		{
			$data['teams'] = $result->num_rows;
		}
		$query = 'SELECT DISTINCT match_info.event_key FROM `match_data` JOIN match_info ON match_info.match_key=match_data.match_key';
		$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
		if($result->num_rows > 0)
		{
			$data['events'] = $result->num_rows;
		}
		$query = 'select DISTINCT match_key from match_data';
		$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
		if($result->num_rows > 0)
		{
			$data['matches'] = $result->num_rows;
		}
		
		return $response->withJson($data);
	});
	
});

?>
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
		$db = db_connect();
		$data = array(
			'users' => 0,
			'teams' => 0,
			'events' => 0,
			'matches' => 0
		);
		$query = 'select * from users';
		$users = db_select($query);
		$data['users'] = count($users);

		$query = 'select * from team_accounts';
		$teams = db_select($query);
		$data['teams'] = count($teams);

		$query = 'SELECT DISTINCT match_info.event_key FROM `match_data` JOIN match_info ON match_info.match_key=match_data.match_key';
		$events = db_select($query);
		$data['events'] = count($events);

		$query = 'select DISTINCT match_key from match_data';
		$matches = db_select($query);
		$data['matches'] = count($matches);

		return $response->withJson($data);
	});

});

?>

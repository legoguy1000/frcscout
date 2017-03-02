<?php
use \Firebase\JWT\JWT;

$app->group('/users', function () use ($app) {
	$app->get('/all', function ($request, $response, $args) {
		return $response->withJson($data);
	});
	$app->group('/{id:[0-9a-z]{13}}', function () use ($app) {
		$app->get('', function ($request, $response, $args) {
			$authToken = $request->getAttribute("jwt");
			$id = $request->getAttribute('id');
			if(!isset($id) || $id == '') {
				return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid user ID.  Reload the page and try again.'));
			}
			$user = getUserDataFromId($id);
			return $response->withJson($user);
		});
		$app->group('/team', function () use ($app) {
			$app->get('/membership', function ($request, $response, $args) {
				$db = db_connect();
				$authToken = $request->getAttribute("jwt");
				$id = $request->getAttribute('id');
				if(!isset($id) || $id == '') {
					return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid user ID.  Reload the page and try again.'));
				}
				$user = getTeamMembershipByUser($id);
				return $response->withJson($user);
			});
			$app->get('/info', function ($request, $response, $args) {
				$db = db_connect();
				$authToken = $request->getAttribute("jwt");
				$id = $request->getAttribute('id');
				if(!isset($id) || $id == '') {
					return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid user ID.  Reload the page and try again.'));
				}
				$user = getTeamInfoByUser($id);
				return $response->withJson($user);
			});
		});
	});
	$app->post('/updatePersonalInfo', function ($request, $response, $args) {
		$db = db_connect();
		$formData = $request->getParsedBody();
		$authToken = $request->getAttribute("jwt");
		if(!isset($formData['id']) || $formData['id'] == '')
		{
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid user ID.  Reload the page and try again.'));
		}
		verifyUser($formData['id'], $authToken->data->id, $die = true);
		if($formData['data']['fname'] == '' || $formData['data']['lname'] == '')
		{
			return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'All Fields are Required'));
		}
		$phone = $formData['data']['phone']== null ? 'NULL' : ''.db_quote($formData['data']['phone']);
		$query = 'UPDATE users SET fname='.db_quote($formData['data']['fname']).', lname='.db_quote($formData['data']['lname']).', phone='.$phone.' WHERE id='.db_quote($formData['id']);
		$result = db_query($query);
		if(!$result) {
			return $response->withJson(errorHandle(mysqli_error($db), $query));
		}
		$data = getUserDataFromParam('id', $formData['id']);
		if($data === false)
		{
			return $response->withJson(array('status'=>false, 'type'=>'danger', 'msg'=>'Error getting user information'));
		}
		$data['login_method'] = $authToken->data->login_method;
		$key = getIniProp('jwt_key');;
		$token = array(
			"iss" => "https://frcscout.resnick-tech.com",
			"iat" => time(),
			"exp" => time()+60*60,
			'data' => $data
		);
		$jwt = JWT::encode($token, $key);
		return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>'Personal Information Saved', 'token'=>$jwt));
	});
	$app->group('/pushNotification', function () use ($app) {
		$app->post('/subscribe', function ($request, $response, $args) {
			$db = db_connect();
			$authToken = $request->getAttribute("jwt");
			$formData = $request->getParsedBody();
			if(!isset($formData['endpoint']) || $formData['endpoint'] == '' || !isset($formData['key']) || $formData['key'] == '' || !isset($formData['authSecret']) || $formData['authSecret'] == '') {
				return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.'));
			}
			$userId = $authToken->data->id;
			$teamInfo = getTeamInfoByUser($userId);
			$team = $teamInfo['team_number'];

			$id = uniqid();
			$query = 'INSERT INTO notification_endpoints (`id`, `user_id`, `endpoint`, `auth_secret`, `public_key`) VALUES
											('.db_quote($id).',
											 '.db_quote($userId).',
											 '.db_quote($formData['endpoint']).',
											 '.db_quote($formData['authSecret']).',
											 '.db_quote($formData['key']).')';
			$result = db_query($query);
			if(!$result) {
				return $response->withJson(errorHandle(mysqli_error($db), $query));
			}
			$msg = 'Device Subscription Added';
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>$msg));
		});
		$app->post('/unsubscribe', function ($request, $response, $args) {
			$db = db_connect();
			$authToken = $request->getAttribute("jwt");
			$formData = $request->getParsedBody();
			if(!isset($formData['endpoint']) || $formData['endpoint'] == '') {
				return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.'));
			}
			$userId = $authToken->data->id;
			$query = 'DELETE FROM notification_endpoints WHERE endpoint='.db_quote($formData['endpoint']).' AND user_id='.db_quote($userId);
			$result = db_query($query);
			if(!$result) {
				return $response->withJson(errorHandle(mysqli_error($db), $query));
			}
			$msg = 'Device Subscription Removed';
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>$msg));
		});
		$app->post('/endpointUpdate', function ($request, $response, $args) {
			$db = db_connect();
			$authToken = $request->getAttribute("jwt");
			$formData = $request->getParsedBody();
			if(!isset($formData['endpoint']) || $formData['endpoint'] == '') {
				return $response->withJson(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.'));
			}
			$userId = $authToken->data->id;
			$query = 'select * from notification_endpoints WHERE endpoint='.db_quote($formData['endpoint']);
			$result = db_query($query);
			if(!$result) {
				return $response->withJson(errorHandle(mysqli_error($db), $query));
			}
			if($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				if($userId != $row['user_id'])
				{
					$query = 'UPDATE notification_endpoints SET user_id='.db_quote($userId).', auth_secret='.db_quote($formData['authSecret']).', public_key='.db_quote($formData['key']).' WHERE endpoint='.db_quote($formData['endpoint']);
					$result = db_query($query);
					if(!$result) {
						return $response->withJson(errorHandle(mysqli_error($db), $query));
					}
				}
			}
			else
			{
				$id = uniqid();
				$query = 'INSERT INTO notification_endpoints (`id`, `user_id`, `endpoint`, `auth_secret`, `public_key`) VALUES
												('.db_quote($id).',
												 '.db_quote($userId).',
												 '.db_quote($formData['endpoint']).',
												 '.db_quote($formData['authSecret']).',
												 '.db_quote($formData['key']).')';
				$result = db_query($query);
				if(!$result) {
					return $response->withJson(errorHandle(mysqli_error($db), $query));
				}
				$msg = 'Device Subscription Added';
				return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>$msg));
			}
			$msg = 'Device Subscription Endpoint Updated';
			return $response->withJson(array('status'=>true, 'type'=>'success', 'msg'=>$msg));
		});
	});
});

?>

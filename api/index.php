<?php

require $_SERVER['DOCUMENT_ROOT'].'/site/includes.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$app = new \Slim\App(["settings" => $config]);

$app->add(new \Slim\Middleware\JwtAuthentication([
    "secret" => getIniProp('jwt_key'),
	"attribute" => "jwt",
	"path" => ["/"],
	"passthrough" => ["/v1/general", "/v1/login", "/v1/tba", "/v1/events/current", "/v1/teams/search"],
]));

$app->group('/v1', function () use ($app) {
	require $_SERVER['DOCUMENT_ROOT'].'/api/routes/login.php';
	require $_SERVER['DOCUMENT_ROOT'].'/api/routes/blue_alliance.php';
	require $_SERVER['DOCUMENT_ROOT'].'/api/routes/general.php';
	require $_SERVER['DOCUMENT_ROOT'].'/api/routes/users.php';
	require $_SERVER['DOCUMENT_ROOT'].'/api/routes/teams.php';
	require $_SERVER['DOCUMENT_ROOT'].'/api/routes/matches.php';
	require $_SERVER['DOCUMENT_ROOT'].'/api/routes/events.php';
});










$app->run();
?>
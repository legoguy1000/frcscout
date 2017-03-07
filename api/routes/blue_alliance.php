<?php
use \Firebase\JWT\JWT;

$app->group('/tba', function () use ($app) {
	$app->post('/webhook', function ($request, $response, $args) {
		$body = $request->getBody();
		$formData = $request->getParsedBody();
		$secret = getIniProp('tba_secret');;
		$event_key = '';
		$checkSum = $request->getHeader('HTTP-X-TBA-CHECKSUM')[0];
		if(isset($formData) && !empty($formData) && isset($checkSum) && $checkSum==sha1($secret.$body))
		{
			newMessageToQueue('ba_webhook', $formData);
			//error_log('yes', 0);
		}
	});
});


?>

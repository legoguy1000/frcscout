<?php
include('./includes.php');

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);
/* $formData = array(
	'message_data' => array(
		'event_key' => '2016cabb'
	),
	'message_type' => 'schedule_updated'
); */
$headers = apache_request_headers();
//$headers['X-Tba-Checksum'] = 'asdadsfasdfasdf';
$secret = 'a~E:t:"Lhta,/VdPH'."''".'"ke]@+.FXVRG'."'3q]R}g6+E!m5N2R28B";
$event_key = '';
if(isset($formData) && !empty($formData) && isset($headers['X-Tba-Checksum']) && $headers['X-Tba-Checksum']==sha1($secret.$json))
{	
	newMessageToQueue('ba_webhook', $formData);
//	echo 'asdfasdf';
//	$event_key = '2016iri';
//	error_log($headers['X-Tba-Checksum'], 0);
//	error_log($secret, 0);
//	error_log(sha1($secret.$json), 0);
}

?>

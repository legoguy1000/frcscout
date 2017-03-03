<?php
include('includes.php');
$year = date('Y');
$baApiCall = tbaApiCallEventsYear($year);
if($baApiCall !== FALSE) {
	$eventDataArr = json_decode($baApiCall, true);
	foreach($eventDataArr as $event) {
		error_log($event['key'], 0);
		$exists = checkEventInfo($event['key']);
		$matches = checkEventMatches($event['key']);
		if(!$exists) {
			insertNewEvent($event);
		}
		if(!$exists || !$matches) {
			$formData = array(
				'message_type' => 'schedule_updated',
				'message_data' => array(
					'event_key' => $event['key']
				)
			);
			newMessageToQueue('ba_webhook', $formData);
		}
	}
}
if(date("W") >= 45) {
	$baApiCall = tbaApiCallEventsYear($year+1);
	if($baApiCall !== FALSE) {
		$eventDataArr = json_decode($baApiCall, true);
		foreach($eventDataArr as $event) {
			error_log($event['key'], 0);
			$exists = checkEventInfo($event['key']);
			$matches = checkEventMatches($event['key']);
			if(!$exists) {
				insertNewEvent($event);
			}
			if(!$exists || !$matches) {
				$formData = array(
					'message_type' => 'schedule_updated',
					'message_data' => array(
						'event_key' => $event['key']
					)
				);
				newMessageToQueue('ba_webhook', $formData);
			}
		}
	}
}
?>

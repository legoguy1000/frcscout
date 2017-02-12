<?php
include('includes.php');
$year = date('Y');
$baApiCall = tbaApiCallEventsYear($year);
if($baApiCall !== FALSE)
{
	$eventDataArr = json_decode($baApiCall, true);
	foreach($eventDataArr as $event)
	{
		$exists = checkEventInfo($event['key']);
		if(!$exists)
		{
			insertNewEvent($event);
			$formData = array(
				'message_type' => 'schedule_updated',
				'message_data' => array(
					'event_key' => $event
				)
			);
			newMessageToQueue('ba_webhook', $formData);
		}
	}
}
$baApiCall = tbaApiCallEventsYear($year+1);
if($baApiCall !== FALSE)
{
	$eventDataArr = json_decode($baApiCall, true);
	foreach($eventDataArr as $event)
	{
		$exists = checkEventInfo($event['key']);
		if(!$exists)
		{
			insertNewEvent($event);
			$formData = array(
				'message_type' => 'schedule_updated',
				'message_data' => array(
					'event_key' => $event
				)
			);
			newMessageToQueue('ba_webhook', $formData);
		}
	}
}
?>
<?php

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

function newMessageToQueue($msg_type, $msg_data)
{
	$connection = new AMQPConnection('10.100.10.60', 5672, 'guest', 'guest');
	$channel = $connection->channel();

	$queue = false;
	if($msg_type == 'ba_webhook')
	{
		$queue = 'ba_webhook_queue';
	}
	elseif($msg_type == 'user_notification')
	{
		$queue = 'notification_queue';
	}
	elseif($msg_type == 'ba_team_import')
	{
		$queue = 'ba_team_import_queue';
	}
	elseif($msg_type == 'test')
	{
		$queue = 'test_queue';
	}
	
	if($queue !== false)
	{
		$channel->queue_declare($queue, false, false, false, false);
		$data = array(
			'msg_type' => $msg_type,
			'msg_data' => $msg_data
		);
		$data = json_encode($data);

		$msg = new AMQPMessage($data, array('delivery_mode' => 2));
		$channel->basic_publish($msg, '', $queue);

		$channel->close();
		$connection->close();
	}
}

function newMessageToWS($msg)
{
	$context = new ZMQContext();
	$socket = $context->getSocket(ZMQ::SOCKET_PUSH);
	$socket->connect("tcp://10.100.10.60:5555");
	//echo "sending $msg\n";
	if(is_array($msg) || is_object($msg))
	{
		$msg = json_encode($msg);
	}
	$socket->send($msg);
}







?>
<?php
include('includes.php');

$msg = $argv[1];
$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH);
$socket->connect("tcp://10.100.10.60:5555");
echo "sending $msg\n";
$socket->send($msg);



?>
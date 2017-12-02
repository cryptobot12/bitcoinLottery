<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/29/2017
 * Time: 5:33 PM
 */

/*
$entryData = array(
    'category' => 'videos'
, 'title'    => 'spongebob'
, 'article'  => 'I am rich :).'
, 'when'     => time()
);

$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://localhost:5555");

$socket->send(json_encode($entryData));*/

include '../random.php';

echo rand_string(8);
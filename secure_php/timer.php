<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 5/23/18
 * Time: 7:18 PM
 */


try {

    $the_timer = time() % 60;

    if ($the_timer > 10 && $the_timer < 55) {
        $entryData = array('category' => 'all', 'option' => 4, 'time' => (55 - $the_timer));

        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:5555");

        $socket->send(json_encode($entryData));
    } elseif (($the_timer <= 10 || $the_timer >= 55) && $the_timer !== 0) {
        $entryData = array('category' => 'all', 'option' => 4, 'time' => 'LOCKED');

        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:5555");

        $socket->send(json_encode($entryData));
    } elseif ($the_timer === 0) {
        echo exec("php end_game.php");
    }

} catch (ZMQSocketException $e) {
    echo $e->getMessage();
}
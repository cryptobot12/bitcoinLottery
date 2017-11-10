<?php

session_start();


/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/19/2017
 * Time: 12:44 PM
 */

$servername = "localhost";
$username = $_SESSION['username'];
$betNumber = $_POST['betNumber'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=lottery", "root", "5720297Ff");
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //echo "Connected successfully";

    $stmt = $conn->prepare('SELECT game_id FROM game ORDER BY timedate DESC, game_id DESC LIMIT 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_game = $row['game_id'];

    $stmt = $conn->prepare('INSERT INTO numberxuser(game_id, number_id, user_id) VALUES (:game_id , :number_id, (SELECT user_id FROM user
    WHERE username = :username))');

    $stmt->execute(array('game_id' => $current_game, 'number_id' => $betNumber, 'username' => $username));

    $stmt = $conn->prepare('UPDATE user SET balance = balance - 3000 WHERE username = :username');

    $stmt->execute(array('username' => $username));

    //Balance
    $stmt = $conn->prepare('SELECT balance FROM user WHERE username = :username');
    $stmt->execute(array('username' => $username));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance = $row['balance'] / 100;

    //NumbersList
    $arrayOfNumbers = array();
    $stmt = $conn->prepare('SELECT number_id FROM numberxuser WHERE user_id = (SELECT user_id
        FROM user WHERE username = :username) AND game_id = :game_id');
    $stmt->execute(array('username' => $_SESSION['username'], 'game_id' => $current_game));
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($row as $item){
        array_push($arrayOfNumbers, $item['number_id']);
    }

    $returnAjax = array('balance' => $balance, 'numbers' => $arrayOfNumbers);
    $jsonAjax = json_encode($returnAjax);
    echo $jsonAjax;

    //Broadcasting
    $stmt = $conn->prepare('SELECT COUNT(*) AS jackpot FROM numberxuser WHERE game_id = :game_id');
    $stmt->execute(array('game_id' => $current_game));
    $jackpot = $stmt->fetchColumn() * 30;

    $entryData = array('category' => 'all', 'reload' => 0, 'jackpot' => $jackpot);

    $context = new ZMQContext();
    $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
    $socket->connect("tcp://localhost:5555");

    $socket->send(json_encode($entryData));
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}



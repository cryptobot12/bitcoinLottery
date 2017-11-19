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
$betNumber = json_decode(stripslashes($_POST['numbers']));

//Number verification
function legalArray($array){
    $legal = true;

    if (count($array) > 200) {
        $legal = false;
        return $legal;
    }
    else {

        foreach ($array as $item) {
            if (!is_numeric($item) || ($item < 1) || ($item > 50000)) {
                $legal = false;
                break;
            }

        }
    }


    return $legal;
}

if (legalArray($betNumber)) {

    try {
        $conn = new PDO("mysql:host=$servername;dbname=lottery", "root", "5720297Ff");
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        //Checking user's balance
        $stmt = $conn->prepare('SELECT balance FROM user WHERE username = :username');
        $stmt->execute(array('username' => $username));

        array_unique($betNumber); //Removing duplicates
        $plays = count($betNumber);

        if ($balance >= (5000 * $plays)) {

            //Selecting current game
            $stmt = $conn->prepare('SELECT game_id FROM game ORDER BY timedate DESC, game_id DESC LIMIT 1');
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_game = $row['game_id'];

            //Inserting numbers
            $stmt = $conn->prepare('INSERT INTO numberxuser(game_id, number_id, user_id) VALUES (:game_id , :number_id, (SELECT user_id FROM user
    WHERE username = :username))');
            foreach ($betNumber as $number) {
                $stmt->execute(array('game_id' => $current_game, 'number_id' => $number, 'username' => $username));
            }


            //Updating users balance
            $stmt = $conn->prepare('UPDATE user SET balance = balance - (5000 * :plays) WHERE username = :username');
            $stmt->execute(array('username' => $username, 'plays' => $plays));

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
            foreach ($row as $item) {
                array_push($arrayOfNumbers, $item['number_id']);
            }

            //Count
            $stmt = $conn->prepare('SELECT COUNT(number_id) AS countNumbers FROM numberxuser WHERE user_id = (SELECT user_id
        FROM user WHERE username = :username) AND game_id = :game_id');
            $stmt->execute(array('username' => $_SESSION['username'], 'game_id' => $current_game));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $row['countNumbers'];

            $_SESSION["numbers_list"] = $arrayOfNumbers;

            $returnAjax = array('balance' => $balance, 'numbers' => $arrayOfNumbers, 'count' => $count);
            $jsonAjax = json_encode($returnAjax);
            echo $jsonAjax;

            //Broadcasting
            $stmt = $conn->prepare('SELECT COUNT(*) AS jackpot FROM numberxuser WHERE game_id = :game_id');
            $stmt->execute(array('game_id' => $current_game));
            $jackpot = $stmt->fetchColumn() * 50;

            $entryData = array('category' => 'all', 'reload' => 0, 'jackpot' => $jackpot);

            $context = new ZMQContext();
            $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
            $socket->connect("tcp://localhost:5555");

            $socket->send(json_encode($entryData));
        }

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
else {
    echo "Illegal numbers...";
}


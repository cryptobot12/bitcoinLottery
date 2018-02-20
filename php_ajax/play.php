<?php

session_start();


/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/19/2017
 * Time: 12:44 PM
 */

include "../connect.php";
include "../inc/login_checker.php";

$betNumber = json_decode(htmlspecialchars($_POST['numbers']));

function legalArray($array)
{
    $legal = true;

    if (count($array) > 25) {
        $legal = false;
        return $legal;
    } else {

        foreach ($array as $item) {
            if (!is_numeric($item) || ($item < 1) || ($item > 50000)) {
                $legal = false;
                break;
            }

        }
    }


    return $legal;
}


if ($logged_in) {
//Number verification


    if (legalArray($betNumber)) {

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            //Checking user's balance
            $stmt = $conn->prepare('SELECT balance FROM user WHERE username = :username');
            $stmt->execute(array('username' => $username));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $balance = $result['balance'];

            array_unique($betNumber); //Removing duplicates
            $plays = count($betNumber);

            if ($balance >= (10000 * $plays)) {
                //Selecting current game
                $stmt = $conn->prepare('SELECT game_id, amount FROM game ORDER BY game_id DESC LIMIT 1');
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $current_game = $row['game_id'];
                $bonus = $row['amount'];

                //Have you played this round before?
                $stmt = $conn->prepare('SELECT number_id FROM numberxuser WHERE user_id = :user_id 
                AND game_id = :game_id LIMIT 1');
                $stmt->execute(array('user_id' => $user_id, 'game_id' => $current_game));
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $havePlayed = !empty($row);

                //Inserting numbers
                $stmt = $conn->prepare('INSERT INTO numberxuser(game_id, number_id, user_id) 
                                                VALUES (:game_id , :number_id, :user_id)');

                foreach ($betNumber as $number) {
                    $stmt->execute(array('game_id' => $current_game, 'number_id' => $number, 'user_id' => $user_id));
                }

                //Increasing games played
                if (!$havePlayed) {
                    $stmt = $conn->prepare('UPDATE user SET games_played = games_played + 1
                                                     WHERE user_id = :user_id');
                    $stmt->execute(array('user_id' => $user_id));

                    $stmt = $conn->prepare('INSERT INTO gamexuser(game_id, user_id, win, bet, profit) VALUES 
                    (:game_id, :user_id, :win, :bet, :profit)');
                    $stmt->execute(array('game_id' => $current_game, 'user_id' => $user_id, 'win' => 0, 'bet' => 10000 * $plays,
                        'profit' => -10000 * $plays));

                } else {
                    $stmt = $conn->prepare('UPDATE gamexuser SET bet = bet + 10000 * :plays, profit = profit - 10000 * :plays2
                    WHERE user_id = :user_id
                    AND game_id = :game_id');
                    $stmt->execute(array('plays' => $plays, 'plays2' => $plays, 'user_id' => $user_id, 'game_id' => $current_game));
                }


                //Updating users balance
                $stmt = $conn->prepare('UPDATE user SET balance = balance - (10000 * :plays1),
                                                net_profit = net_profit - (10000 * :plays2)
                                                WHERE user_id = :user_id');
                $stmt->execute(array('user_id' => $user_id, 'plays1' => $plays, 'plays2' => $plays));

//                Updating user's bet'


                //Get balance
                $stmt = $conn->prepare('SELECT balance FROM user WHERE user_id = :user_id');
                $stmt->execute(array('user_id' => $user_id));
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $balance = $row['balance'] / 100;

                //NumbersList
                $arrayOfNumbers = array();
                $stmt = $conn->prepare('SELECT number_id FROM numberxuser WHERE user_id = :user_id AND game_id = :game_id');
                $stmt->execute(array('user_id' => $user_id, 'game_id' => $current_game));
                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($row as $item) {
                    array_push($arrayOfNumbers, $item['number_id']);
                }

                //Count
                $stmt = $conn->prepare('SELECT COUNT(number_id) AS countNumbers FROM numberxuser WHERE user_id = :user_id
                                                AND game_id = :game_id');
                $stmt->execute(array('user_id' => $user_id, 'game_id' => $current_game));
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $count = $row['countNumbers'];

                $returnAjax = array('balance' => $balance, 'numbers' => $arrayOfNumbers, 'count' => $count);
                $jsonAjax = json_encode($returnAjax);
                echo $jsonAjax;

                //Broadcasting
                $stmt = $conn->prepare('SELECT COUNT(*) AS jackpot FROM numberxuser WHERE game_id = :game_id');
                $stmt->execute(array('game_id' => $current_game));
                $jackpot = ($stmt->fetchColumn() * 9500 + $bonus) / 100;

                $entryData = array('category' => 'all', 'option' => 1, 'jackpot' => $jackpot);

                $context = new ZMQContext();
                $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
                $socket->connect("tcp://localhost:5555");

                $socket->send(json_encode($entryData));
            }

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        } catch (ZMQSocketException $e) {
            echo $e->getMessage();
        }
    } else {
        echo "Illegal numbers...";
    }

} else {
    echo "You need to login first.";
}
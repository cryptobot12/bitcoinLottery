<?php

session_start();


/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/19/2017
 * Time: 12:44 PM
 */
require_once '/home/luckiestguyever/PhpstormProjects/bitcoinLottery/vendor/autoload.php';

include "../globals.php";
include "../inc/login_checker.php";

$betNumber = json_decode(htmlspecialchars($_POST['numbers']));

function legalArray($array)
{
    $legal = true;

    if (count($array) > 100) {
        $legal = false;
        return $legal;
    } else {

        foreach ($array as $item) {
            if (!is_numeric($item) || ($item < 1) || ($item > 50000) || !is_int($item)) {
                $legal = false;
                break;
            }

        }
    }


    return $legal;
}


if ($logged_in) {
//Number verification
    $the_timer = time() % 60;
    if ($the_timer > 10 && $the_timer < 55) {
        if (legalArray($betNumber)) {

            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


                //Checking user's balance
                $driver = new \Nbobtc\Http\Driver\CurlDriver();
                $driver
                    ->addCurlOption(CURLOPT_VERBOSE, true)
                    ->addCurlOption(CURLOPT_STDERR, '/var/logs/curl.err');


                $client = new \Nbobtc\Http\Client('http://puppetmaster:vz6qGFsHBv5auSSDhTPWPktVu@localhost:18332');
                $client->withDriver($driver);

                //Checking balance

                $command = new \Nbobtc\Command\Command('getbalance', $username);

                /** @var \Nbobtc\Http\Message\Response */
                $response = $client->sendCommand($command);

                /** @var string */
                $output = json_decode($response->getBody()->getContents());

                $balance_in_bits = $output->result * 1000000;

                array_unique($betNumber); //Removing duplicates
                $plays = count($betNumber);

                if ($balance_in_bits >= (25 * $plays)) {

                    $to_jackpot = 20 * $plays / 1000000;
                    $to_next_jackpot = 4 * $plays / 1000000;
                    $to_profit = 1 * $plays / 1000000;
                    $command = new \Nbobtc\Command\Command('move', array($username, "jackpot", $to_jackpot));

                    /** @var \Nbobtc\Http\Message\Response */
                    $response = $client->sendCommand($command);

                    $command = new \Nbobtc\Command\Command('move', array($username, "profit", $to_profit));

                    /** @var \Nbobtc\Http\Message\Response */
                    $response = $client->sendCommand($command);

                    $command = new \Nbobtc\Command\Command('move', array($username, "nextjackpot", $to_next_jackpot));

                    /** @var \Nbobtc\Http\Message\Response */
                    $response = $client->sendCommand($command);

                    //Selecting current game
                    $stmt = $conn->prepare('SELECT game_id FROM game ORDER BY game_id DESC LIMIT 1');
                    $stmt->execute();
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $current_game = $row['game_id'];


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
                        $stmt->execute(array('game_id' => $current_game, 'user_id' => $user_id, 'win' => 0, 'bet' => 2500 * $plays,
                            'profit' => -2500 * $plays));

                    } else {
                        $stmt = $conn->prepare('UPDATE gamexuser SET bet = bet + 2500 * :plays, profit = profit - 2500 * :plays2
                    WHERE user_id = :user_id
                    AND game_id = :game_id');
                        $stmt->execute(array('plays' => $plays, 'plays2' => $plays, 'user_id' => $user_id, 'game_id' => $current_game));
                    }

                    //Getting balance
                    $command = new \Nbobtc\Command\Command('getbalance', $username);

                    /** @var \Nbobtc\Http\Message\Response */
                    $response = $client->sendCommand($command);

                    /** @var string */
                    $output = json_decode($response->getBody()->getContents());

                    $balance_in_bits = $output->result * 1000000;

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

                    $returnAjax = array('balance' => $balance_in_bits, 'numbers' => $arrayOfNumbers, 'count' => $count);
                    $jsonAjax = json_encode($returnAjax);
                    echo $jsonAjax;

                    //Broadcasting
                    $command = new \Nbobtc\Command\Command('getbalance', "jackpot");
                    /** @var \Nbobtc\Http\Message\Response */
                    $response = $client->sendCommand($command);
                    /** @var string */
                    $output = json_decode($response->getBody()->getContents());
                    $jackpot = $output->result * 1000000;

                    $entryData = array('category' => 'all', 'option' => 1, 'jackpot' => $jackpot);

                    $context = new ZMQContext();
                    $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
                    $socket->connect("tcp://localhost:5555");

                    $socket->send(json_encode($entryData));
                } else {
                    echo "Insufficient balance";
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
        echo "Bets are no longer accepted";
    }
} else {
    echo "You need to login first.";
}
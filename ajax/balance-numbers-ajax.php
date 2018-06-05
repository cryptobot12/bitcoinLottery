<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/5/17
 * Time: 6:21 PM
 */
session_start();

require_once '/var/www/bitcoinpvp.net/html/vendor/autoload.php';

include "../globals.php";
include "../inc/login_checker.php";

if ($logged_in) {
    try {

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        //Selecting balance
        //Getting balance
        $stmt = $conn->prepare('SELECT balance FROM balances WHERE username = :username');
        $stmt->execute(array('username' => $username));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $balance = $row['balance'] / 100;

        //Selecting current game
        $stmt = $conn->prepare('SELECT game_id FROM game ORDER BY game_id DESC, game_date DESC LIMIT 1');
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $current_game = $row['game_id'];

        //Counting numbers
        $stmt = $conn->prepare('SELECT COUNT(number_id) AS numberCount FROM numberxuser WHERE user_id = (SELECT user_id
        FROM user WHERE username = :username) AND game_id = :game_id');
        $stmt->execute(array('username' => $username, 'game_id' => $current_game));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $numbersCount = $row['numberCount'];

        //Selecting numbers list
        $stmt = $conn->prepare('SELECT number_id FROM numberxuser WHERE user_id = (SELECT user_id
        FROM user WHERE username = :username) AND game_id = :game_id');
        $stmt->execute(array('username' => $username, 'game_id' => $current_game));
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arrayOfNumbers = array();
        foreach ($row as $item) {
            array_push($arrayOfNumbers, $item['number_id']);
        }

        $returnAjax = array('balance' => $balance, 'numbers' => $arrayOfNumbers, 'count' => $numbersCount);
        $jsonAjax = json_encode($returnAjax);
        echo $jsonAjax;
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
else {
    echo "You need to login first.";
}
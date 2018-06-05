<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 5/25/18
 * Time: 7:53 PM
 */

session_start();

require_once '/var/www/html/bitcoinLottery/vendor/autoload.php';

include "../globals.php";
include "../inc/login_checker.php";

if ($logged_in) {


    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    //Getting current game
    $stmt = $conn->prepare('SELECT balance FROM balances WHERE username = :username');
    $stmt->execute(array('username' => $username));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance = $row['balance'] / 100;

    $returnAjax = array('balance' => $balance);
    $jsonAjax = json_encode($returnAjax);
    echo $jsonAjax;
} else {
    echo "You need to login first.";
}
<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/25/2017
 * Time: 7:08 PM
 */


try {
    $servername = "localhost";
    $conn = new PDO("mysql:host=$servername;dbname=lottery", "root", "5720297Ff");
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    //Getting games played and max jackpot
    $stmt = $conn->prepare('SELECT COUNT(g.game_id) AS gamesp, MAX(amount) AS max_jackpot FROM game AS g');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $games_played = $result['gamesp'];
    $max_jackpot = $result['max_jackpot'];

    //Getting gross profit
    $stmt = $conn->prepare('SELECT SUM(net_profit) AS gross_profit FROM user WHERE net_profit > 0');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $net_profit = $result['gross_profit'];

    //Getting deposits and withdrawals
    $stmt = $conn->prepare('SELECT @dep := SUM(deposits) AS deposits, @with := SUM(withdrawals) AS withdrawals, (@net := @dep - @with) AS net FROM user');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $net = $result['net'];
    $deposits = $result['deposits'];
    $withdrawals = $result['withdrawals'];

}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}

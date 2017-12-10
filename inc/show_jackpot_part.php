<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/22/2017
 * Time: 12:38 PM
 */


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT game_id, amount FROM game ORDER BY timedate DESC, game_id DESC LIMIT 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_game = $row['game_id'];
    $bonus = $row['amount'];

    $stmt = $conn->prepare('SELECT COUNT(*) AS jackpot FROM numberxuser WHERE game_id = :game_id');

    $stmt->execute(array('game_id' => $current_game));

    $jackpot = ($stmt->fetchColumn() * 4500 + $bonus) / 100;

    echo $jackpot;
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}


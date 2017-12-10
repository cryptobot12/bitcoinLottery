<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/11/17
 * Time: 2:00 PM
 */
session_start();

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


    //Selecting current game
    $stmt = $conn->prepare('SELECT game_id FROM game ORDER BY game_id DESC, timedate DESC LIMIT 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_game = $row['game_id'];

    //Selecting numbers list
    $stmt = $conn->prepare('SELECT COUNT(number_id) AS numbersCount FROM numberxuser WHERE user_id = (SELECT user_id
        FROM user WHERE username = :username) AND game_id = :game_id');
    $stmt->execute(array('username' => $_SESSION['username'], 'game_id' => $current_game));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row['numbersCount'] > 1)
        echo $row['numbersCount'] . " numbers";
    else if ($row['numbersCount'] == 1)
        echo $row['numbersCount'] . " number";
    else
        echo "No numbers";
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}
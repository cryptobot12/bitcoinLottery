<?php
session_start();
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/17/2017
 * Time: 7:23 PM
 */

$servername = "localhost";
$username = htmlspecialchars($_POST['username']);
$password = htmlspecialchars($_POST['password']);


try {
    $conn = new PDO("mysql:host=$servername;dbname=lottery", "root", "5720297Ff");
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //echo "Connected successfully";
    $stmt = $conn->prepare('SELECT username, balance, password FROM user WHERE username = :username');

    $stmt->execute(array('username' => $username));

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($password, $row['password'])) {
        echo "Password is correct";


        $_SESSION['username'] = $row['username'];
        $_SESSION['balance'] = $row['balance'];

        //Selecting current game
        $stmt = $conn->prepare('SELECT game_id FROM game ORDER BY game_id DESC, timedate DESC LIMIT 1');
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $current_game = $row['game_id'];

        //Selecting numbers list
        $stmt = $conn->prepare('SELECT number_id FROM numberxuser WHERE user_id = (SELECT user_id
        FROM user WHERE username = :username) AND game_id = :game_id');
        $stmt->execute(array('username' => $_SESSION['username'], 'game_id' => $current_game));
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $arrayOfNumbers = array();
        foreach ($row as $item){
            array_push($arrayOfNumbers, $item['number_id']);
        }

        $_SESSION["numbers_list"] = $arrayOfNumbers;


        header("Location: index.php");
        die();
    }
    else
        echo "Password is incorrect";
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}



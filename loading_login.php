<?php
session_start();
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/17/2017
 * Time: 7:23 PM
 */

$servername = "localhost";
$username = $_POST['username'];
$password = $_POST['password'];


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



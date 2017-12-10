<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/22/2017
 * Time: 6:01 PM
 */
session_start();

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT balance FROM user WHERE username = :username');
    $stmt->execute(array('username' => $_SESSION['username']));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['balance'] / 100;
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}
<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/17/2017
 * Time: 6:02 PM
 */
include '../function.php';

include "../connect.php";
$username = htmlspecialchars($_POST['username']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$email = htmlspecialchars($_POST['email']);
$bit_address = rand_string(15);


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //echo "Connected successfully";
    $stmt = $conn->prepare('INSERT INTO user(username, password, email, bit_address, balance, deposits, withdrawals,
            net_profit, games_played, registration_date, enabled, code, code_expires, new_email) VALUES (:username, :password, :email, :bit_address,
            0, 0, 0, 0, 0, CURRENT_TIMESTAMP, false, null, null, null)');

    $stmt->execute(array('username' => $username, 'password' => $password, 'email' => $email,
        'bit_address' => $bit_address));

    $stmt = $conn->prepare('UPDATE stats SET total_users = total_users + 1');
    $stmt->execute();

    // use exec() because no results are returned
    echo "New record created successfully";
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}



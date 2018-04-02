<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/24/17
 * Time: 9:01 PM
 */

session_start();

include '../globals.php';
include '../inc/login_checker.php';

$last_url = $_SESSION['last_url'];

if ($logged_in) {

    //Deleting session
    session_destroy();


    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        //Deleting auth token from database
        $stmt = $conn->prepare('DELETE FROM auth_token WHERE selector = :selector');
        $stmt->execute(array('selector' => $selector));
        $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

        //Deleting cookie
        setcookie('auth_token', '', time() - 86400, "/");


    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }


}

header("Location: " . $base_dir . $last_url);
die();
<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/25/17
 * Time: 3:04 PM
 */
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require '../vendor/autoload.php';

include "../globals.php";
include "../inc/login_checker.php";
include '../inc/';

$hashed_user_id = $_GET['sel'];
$validator = $_GET['val'];

if (!empty($hashed_user_id) && !empty($validator)) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $stmt = $conn->prepare('SELECT user_id, new_email, validator FROM email_update WHERE hashed_user_id = :hashed_user_id
         AND CURRENT_TIMESTAMP < expires');
        $stmt->execute(array('hashed_user_id' => $hashed_user_id));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $new_email = $row['new_email'];
        $user_id = $row['user_id'];
        $hashed_validator = $row['validator'];

        if (!empty($row) && password_verify($validator, $hashed_validator)) {

            $stmt = $conn->prepare('UPDATE user SET email = :email WHERE user_id = :user_id');
            $stmt->execute(array('email' => $new_email, 'user_id' => $user_id));

            $stmt = $conn->prepare('DELETE FROM email_update WHERE user_id = :user_id');
            $stmt->execute(array('user_id' => $user_id));

            /*************************************
             *
             * Email here
             */

            $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
            try {
                //Server settings
                $mail->SMTPDebug = 0;                                 // Enable verbose debug output
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = 'no-reply@bitcoinpvp.net';                 // SMTP username
                $mail->Password = 'ECV)88y~7C9yrSL8uxhNSnpC+';                           // SMTP password
                $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
                $mail->Port = 587;                                    // TCP port to connect to

                //Recipients
                $mail->setFrom('no-reply@bitconpvp.net', 'BitcoinPVP');
                $mail->addAddress($new_email);     // Add a recipient


                //Content
                $mail->CharSet = 'UTF-8';
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = 'BitcoinPVP Email updated';
                $mail->Body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <style>
        body {
            font-family: \'Roboto\', sans-serif;
        }
    </style>
</head>
<body>
<div style="width: 700px; margin: 0 auto;">
    <div style="background: black"><img src="https://www.bitcoinpvp.net/img/nav-logo.png" height="56"></div>

    <div style="width: 75%; margin: 50px auto;">

        <p>Greetings <span style="color: red;"><b>' . $username . '</b></span>,</p>

        <p>We inform you that your email address for your BitcoinPVP account has been successfully updated.</p>

        <p>For more information on your account — please visit your <a href="' . $base_dir . 'account">Account Management page.</a></p>

        <p>BitcoinPVP Team</p>
    </div>

    <div style="background: black; color: white; padding: 10px;">© 2018 Copyright BitcoinPVP</div>
</div>

';

                $mail->send();
                echo 'Message has been sent';
            } catch (Exception $e) {
                echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
            }

            $_SESSION['account_management_success'] = 1;
            if ($logged_in) {
                header('Location: ' . $base_dir . 'account');
                die();
            } else {
                header('Location: ' . $base_dir . 'unlogged-success');
                die();
            }

        } else {
            header('Location: ' . $base_dir . 'expired-link');
            die();
        }

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
} else {
    header("Location: " . $base_dir . "lost");
    die();
}
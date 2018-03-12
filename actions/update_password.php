<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/3/17
 * Time: 11:51 AM
 */
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require '../vendor/autoload.php';

include "../connect.php";
include "../inc/login_checker.php";

$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_new_password = $_POST['confirm_new_password'];
$user_id = $_SESSION['user_id'];

$recaptcha_response = $_POST['g-recaptcha-response'];

/* Captcha verifying */
$privatekey = "6Lf1d0EUAAAAAPhwWXktY_b1rBWR_ClydgLfj8g1";


$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = array(
    'secret' => $privatekey,
    'response' => $_POST["g-recaptcha-response"]
);
$options = array(
    'http' => array(
        'method' => 'POST',
        'content' => http_build_query($data)
    )
);
$context = stream_context_create($options);
$verify = file_get_contents($url, false, $context);
$captcha_success = json_decode($verify);

if ($logged_in) {
    if ($captcha_success->success) {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);

            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $stmt = $conn->prepare('SELECT password, email FROM user WHERE user_id = :user_id');
            $stmt->execute(array('user_id' => $user_id));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($current_password, $row['password']) && strlen($new_password) >= 8
                && $new_password == $confirm_new_password && $new_password != $current_password) {

                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE user SET password = :new_password
            WHERE user_id = :user_id');
                $stmt->execute(array('new_password' => $hashed_password, 'user_id' => $user_id));


                /*************************SEND EMAIL HERE*******************/

                /* Send email with code here */

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
                    $mail->addAddress($email);     // Add a recipient


                    //Content
                    $mail->CharSet = 'UTF-8';
                    $mail->isHTML(true);                                  // Set email format to HTML
                    $mail->Subject = 'BitcoinPVP Password changed';
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
    <div style="background: black"><img src="http://www.bitcoinpvp.net/img/nav-logo.png" height="56"></div>

    <div style="width: 75%; margin: 50px auto;">

<p>Greetings <span style="color: red;"><b>' . $row['email'] . '</b></span>,</p>
        <p>Your password has been recently changed.</p>

        <p>If you did not change your password, your account credentials 
        might have been compromised.</p>

        <p>For more information on your account — please visit your <a href="http://localhost/bitcoinLottery/account.php">Account Management page.</a></p>

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

                /************************************************************/
                $_SESSION['account_management_success'] = 2;
                header("Location: ../account.php");
                die();


            } else {

                if (strlen($new_password) < 8) {
                    $_SESSION['incorrect_length'] = true;
                }


                if (!password_verify($current_password, $row['password']))
                    $_SESSION['incorrect_cp'] = true;
                elseif ($current_password == $new_password)
                    $_SESSION['diff_pass'] = true;

                if ($new_password != $confirm_new_password)
                    $_SESSION['unmatch_p'] = true;


                $_SESSION['current_password'] = $current_password;
                $_SESSION['new_password'] = $new_password;
                $_SESSION['confirm_new_password'] = $confirm_new_password;

                header("Location: ../account.php");
                die();
            }

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    } else {
        echo "Cata failed.";
    }
} else {
    header("Location: ../index.php");
    die();
}


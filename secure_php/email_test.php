<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 2/19/2018
 * Time: 5:50 PM
 */

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require '../vendor/autoload.php';

$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
try {
    //Server settings
    $mail->SMTPDebug = 2;                                 // Enable verbose debug output
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'no-reply@bitcoinpvp.net';                 // SMTP username
    $mail->Password = 'ECV)88y~7C9yrSL8uxhNSnpC+';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to

    //Recipients
    $mail->setFrom('no-reply@bitconpvp.net', 'BitcoinPVP');
    $mail->addAddress('andrew.montejo1@gmail.com');     // Add a recipient


    //Content
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Verify your email address';
    $mail->Body    = '<!DOCTYPE html>
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
        <p>Greetings <span style="color: red;"><b>Andrew</b></span>,<br>
            Please click the link below to verify your email address with BitcoinPVP:</p>

        <a href="https://www.redtube.com/2828197">Click here</a>

        <p>Verifying your email address ensures an extra layer of security for your account. We know we have the correct info on
            file should you need assistance with your account.</p>

        <p>For more information on your account — please visit your <a>Account Management page.</a></p>

        <p>BitcoinPVP Team</p>
    </div>

    <div style="background: black; color: white; padding: 10px;">© 2018 Copyright BitcoinPVP</div>
</div>
</body>
</html>';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
}
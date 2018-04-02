<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 1/25/2018
 * Time: 1:21 PM
 */
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require '../vendor/autoload.php';

include "../globals.php";
include "../inc/login_checker.php";

if ($logged_in) {
    //Send email again and do the stuff here

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $stmt = $conn->prepare('SELECT user_id, last_send, hashed_user_id, validator,
 DATE_ADD(CURRENT_TIMESTAMP, INTERVAL -10 MINUTE) AS now FROM email_confirmation WHERE user_id = :user_id');
        $stmt->execute(array('user_id' => $user_id));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $last_send = $result['last_send'];
        $now = $result['now'];

        if (!empty($result['user_id'])) {

            if (strtotime($last_send) < strtotime($now)) {

                $hashed_user_id = $result['hashed_user_id'];

                $stmt = $conn->prepare('SELECT username, email FROM user WHERE user_id = :user_id');
                $stmt->execute(array('user_id' => $user_id));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $username = $result['username'];
                $email = $result['email'];

                $confirmation_code = bin2hex(random_bytes(32));
                $hashed_confirmation_code = password_hash($confirmation_code, PASSWORD_DEFAULT);


                $stmt = $conn->prepare('UPDATE email_confirmation SET last_send = CURRENT_TIMESTAMP,
 validator = :validator, expires = ADDDATE(CURRENT_TIMESTAMP, INTERVAL 3 HOUR) WHERE
user_id = :user_id');
                $stmt->execute(array('validator' => $hashed_confirmation_code, 'user_id' => $user_id));

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
                    $mail->Subject = 'Verify your email address';
                    $mail->Body = '<div style="width: 700px; margin: 0 auto;">
    <div style="background: black"><img src="http://www.bitcoinpvp.net/img/nav-logo.png" height="40"></div>

    <div style="width: 75%; margin: 50px auto; color: black;">
        <p>Greetings <span style="color: red;"><b>' . $username . '</b></span>,<br>
            Please click the link below to verify your email address with BitcoinPVP:</p>

        <a href="' . $base_dir . 'confirm-email/' . $hashed_user_id . '/' . $confirmation_code . '">Click here</a>

        <p>Verifying your email address ensures an extra layer of security for your account. We know we have the correct info on
            file should you need assistance with your account.</p>

        <p>For more information on your account — please visit your <a href="' . $base_dir . 'account">Account Management page.</a></p>

        <p>BitcoinPVP Team</p>
    </div>

    <div style="background: black; color: white; padding: 10px;">© 2018 Copyright BitcoinPVP</div>
</div>';

                    $mail->send();
                    $_SESSION['confirm_email_sent_again_success'] = true;

                } catch (Exception $e) {
                    echo $mail->ErrorInfo;
                }


            } else {
                $_SESSION['too_soon_to_send_confirm_email_again'] = true;
            }

            header("Location: " . $base_dir . "account");
            die();
        } else {

            header("Location: " . $base_dir . "lost");
            die();
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }



} else {
    header("Location: " . $base_dir . "lost");
    die();
}
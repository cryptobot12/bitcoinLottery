<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 3/9/18
 * Time: 1:10 AM
 */
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
include '../globals.php';

$selector = $_POST['s'];
$validator = $_POST['v'];
$new_password = $_POST['new_password'];
$confirm_new_password = $_POST['confirm_new_password'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT user_id, expires, current_timestamp AS now FROM password_reset WHERE hashed_user_id = :selector
AND validator = :validator');
    $stmt->execute(array('selector' => $selector, 'validator' => $validator));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $reset_user_id = $result['user_id'];
    $expires = $result['expires'];
    $now = $result['now'];

    if (!empty($reset_user_id)) {
        if (strtotime($expires) < strtotime($now)) {
            header("Location: " . $base_dir . "lost");
            die();
        } else {

            if (!empty($new_password) && strlen($new_password) >= 8 && $new_password == $confirm_new_password) {

                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare('UPDATE user SET password = :hashed_password WHERE user_id = :user_id');
                $stmt->execute(array('hashed_password' => $hashed_password, 'user_id' => $reset_user_id));

                $stmt = $conn->prepare('DELETE FROM password_reset WHERE user_id = :user_id');
                $stmt->execute(array('user_id' => $reset_user_id));

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
                    $mail->Subject = 'Password Updated';
                    $mail->Body = '
<div style="width: 700px; margin: 0 auto;">
    <div style="background: black"><img src="http://www.bitcoinpvp.net/img/nav-logo.png" height="40"></div>

    <div style="width: 75%; margin: 50px auto; color: black;">

<p>Greetings <span style="color: red;"><b>' . $username . '</b></span>,</p>
        <p>Your password has been recently changed.</p>

        <p>If you did not change your password, your account credentials 
        might have been compromised.</p>

        <p>For more information on your account — please visit your <a href="'. $base_dir .'account">Account Management page.</a></p>

        <p>BitcoinPVP Team</p>
    </div>

    <div style="background: black; color: white; padding: 10px;">© ' . date('Y') . ' Copyright BitcoinPVP</div>
</div>

';

                    $mail->send();
                    echo 'Message has been sent';
                } catch (Exception $e) {
                    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
                }

                $_SESSION['account_management_success'] = 2;

                header("Location: " . $base_dir . "unlogged-success");
                die();
            } else {
                if (strlen($new_password) < 8)
                    $_SESSION['short_password'] = true;

                if ($new_password != $confirm_new_password)
                    $_SESSION['unmatched_password'] = true;

                $_SESSION['new_password_input'] = $new_password;
                $_SESSION['confirm_new_password_input'] = $confirm_new_password;

                header("Location: " . $base_dir . "password-reset/" . $selector . "/" . $validator);
                die();
            }
        }
    } else {
        header("Location: " . $base_dir . "lost");
        die();
    }


} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
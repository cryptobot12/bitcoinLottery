<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/1/17
 * Time: 7:24 PM
 */
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require '../vendor/autoload.php';

include '../function.php';
include '../globals.php';
include "../inc/login_checker.php";

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


if ($captcha_success->success) {
    if ($logged_in) {
        $new_email = strtolower(filter_var($_POST['new-email'], FILTER_SANITIZE_EMAIL));
        $confirm_email = strtolower(filter_var($_POST['confirm-email'], FILTER_SANITIZE_EMAIL));

        if ($new_email == $confirm_email) {
            if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                //Valid email

                $code = rand_string(4);

                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                    // set the PDO error mode to exception
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                    /*****Checking if email is taken ****/

                    $stmt = $conn->prepare('SELECT COUNT(email) AS email_count FROM user WHERE email = :new_email');
                    $stmt->execute(array('new_email' => $new_email));
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $email_count = $result['email_count'];

                    if ($email_count == 0) {

                        $stmt = $conn->prepare('SELECT email FROM user WHERE user_id = :user_id');
                        $stmt->execute(array('user_id' => $user_id));
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $email = $result['email'];

                        $salt = bin2hex(random_bytes(32));
                        $hashed_user_id = hash('sha256', $user_id . $salt);
                        //CREATE CONFIRMATION CODE
                        $confirmation_code = bin2hex(random_bytes(32));

                        $stmt = $conn->prepare('INSERT INTO email_update(user_id, new_email,hashed_user_id, validator, expires)
                  VALUES(:user_id, :new_email, :hashed_user_id, :validator, DATE_ADD(NOW(), INTERVAL 24 HOUR))');
                        $stmt->execute(array('user_id' => $user_id, 'new_email' => $new_email, 'hashed_user_id' => $hashed_user_id,
                            'validator' => $confirmation_code));

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
                            $mail->Subject = 'Email Update Request';
                            $mail->Body = '<div style="width: 700px; margin: 0 auto;">
    <div style="background: black"><img src="http://www.bitcoinpvp.net/img/nav-logo.png" height="40"></div>

    <div style="width: 75%; margin: 50px auto; color: black;">

<p>Greetings <span style="color: red;"><b>' . $username . '</b></span>,</p>
        <p>We\'ve received an email update request for your BitcoinPVP account.<br>
            To update your email, click the link below: </p>

        <a href="' . $base_dir . 'actions/update-email/' . $hashed_user_id . '/' . $confirmation_code . '">Update email</a>

        <p>This link will expire in 24 hours. If you did not request an email update, your account credentials
        might have been compromised, and we encourage to change your password.</p>

        <p>For more information on your account — please visit your <a href="' . $base_dir . 'account">Account Management page.</a></p>

        <p>BitcoinPVP Team</p>
    </div>

    <div style="background: black; color: white; padding: 10px;">© ' . date('Y') . ' Copyright BitcoinPVP</div>
</div>';

                            $mail->send();

                        } catch (Exception $e) {
                            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
                        }

                        /*****************************/
                    } else {
                        $_SESSION['new-email'] = $new_email;
                        $_SESSION['confirm-email'] = $confirm_email;
                        $_SESSION['expand_email'] = true;
                    }

                    header("Location: " . $base_dir . "account");
                    die();
                } catch (PDOException $e) {
                    echo "Connection failed: " . $e->getMessage();
                }
            } else {
                //Invalid email
                $_SESSION['new-email'] = $new_email;
                $_SESSION['confirm-email'] = $confirm_email;
                $_SESSION['expand_email'] = true;
                header("Location: " . $base_dir . "account.php");
                die();
            }
        } else {
            $_SESSION['new-email'] = $new_email;
            $_SESSION['confirm-email'] = $confirm_email;
            $_SESSION['expand_email'] = true;
            header("Location: " . $base_dir . "account");
            die();
        }
    } // Not logged in
    else {
        header("Location: " . $base_dir . "lost");
        die();
    }
} //No captcha passed
else {
    $_SESSION['captcha_failed_email'] = true;
    $_SESSION['expand_email'] = true;
    header("Location: " . $base_dir . "account");
    die();
}
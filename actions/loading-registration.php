<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/17/2017
 * Time: 6:02 PM
 */
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require '../vendor/autoload.php';

include '../function.php';
include "../globals.php";
include "../inc/login_checker.php";


$username = strtolower($_POST['username']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$email = strtolower($_POST['email']);

$bit_address = rand_string(15);
/* Implement bitcoin stuff here


*/
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
    if (!$logged_in) {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            //Checking username input
            if (!empty($username)) {
                $not_empty_username = true;

                if (preg_match("/^[a-z0-9_-]{4,19}$/", $username)) {
                    $valid_username = true;

                    $stmt = $conn->prepare('SELECT user_id FROM user WHERE username = :username');
                    $stmt->execute(array('username' => $username));
                    $user_exists_row = $stmt->fetch(PDO::FETCH_ASSOC);


                } else {
                    $valid_username = false;
                }

            } else {
                $_SESSION['username_empty'] = true;
                $not_empty_username = false;
            }

            //Checking email input
            if (!empty($email)) {
                $not_empty_email = true;

                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                    $valid_email = true;

                    $stmt = $conn->prepare('SELECT user_id FROM user WHERE email = :email');
                    $stmt->execute(array('email' => $email));
                    $email_exists_row = $stmt->fetch(PDO::FETCH_ASSOC);


                } else {
                    $valid_email = false;
                }

            } else {
                $not_empty_email = false;
                $_SESSION['email_empty'] = true;
            }


            //Checking password input
            if (!empty($password)) {

                $not_empty_password = true;

                if ($password == $confirm_password)
                    $password_equals = true;
                else {
                    $password_equals = false;
                }

                if (strlen($password) >= 8 && strlen($password) <= 72) {
                    $password_length_valid = true;
                } else {
                    $password_length_valid = false;
                }

            } else {
                $not_empty_password = false;
                $_SESSION['password_empty'] = true;
            }

            if (empty($user_exists_row) && empty($email_exists_row) && $password_equals && $valid_username && $password_length_valid
                && $valid_email && $not_empty_username && $not_empty_email && $not_empty_password) {

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                //CREATE NEW USER
                $stmt = $conn->prepare('INSERT INTO user(username, password, email, bit_address, balance, 
            net_profit, games_played, registration_date, enabled) VALUES (:username, :password, :email, :bit_address,
            0, 0, 0, CURRENT_TIMESTAMP, FALSE)');

                $stmt->execute(array('username' => $username, 'password' => $hashed_password, 'email' => $email,
                    'bit_address' => $bit_address));

                //Adding to stats
                $stmt = $conn->prepare('UPDATE stats SET total_users = total_users + 1');
                $stmt->execute();

                //Getting user_id for new user
                $stmt = $conn->prepare('SELECT user_id FROM user WHERE username = :username');
                $stmt->execute(array('username' => $username));
                $user_id = $stmt->fetch(PDO::FETCH_ASSOC)['user_id'];
                $salt = bin2hex(random_bytes(32));
                $hashed_user_id = hash('sha256', $user_id . $salt);

                //CREATE CONFIRMATION CODE
                $confirmation_code = bin2hex(random_bytes(32));
                $hashed_confirmation_code = password_hash($confirmation_code, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO email_confirmation(user_id, hashed_user_id, validator, expires)
            VALUES (:user_id, :hashed_user_id, :validator, ADDDATE(CURRENT_TIMESTAMP, INTERVAL 3 HOUR))');
                $stmt->execute(array('user_id' => $user_id, 'hashed_user_id' => $hashed_user_id, 'validator' => $hashed_confirmation_code));

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

                    //Let's login the new user
                    $_SESSION['auth_token'] = json_encode(array('username' => $username, 'user_id' => $user_id));

                    header("Location: " . $base_dir . "account");
                    die();
                } catch (Exception $e) {
                    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
                }


            } else {

                $_SESSION['input_username'] = $username;
                $_SESSION['input_password'] = $password;
                $_SESSION['input_confirm_password'] = $confirm_password;
                $_SESSION['input_email'] = $email;

                header("Location: " . $base_dir . "registration");
                die();
            }


        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
    else {
        header("Location: " . $base_dir . "lost");
        die();
    }
} else {
    $_SESSION['captcha_failed'] = 1;
    header("Location: " . $base_dir . "registration");

}
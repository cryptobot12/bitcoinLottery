<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 3/7/18
 * Time: 3:49 PM
 */
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require '../vendor/autoload.php';

include '../globals.php';

$recaptcha_response = $_POST['g-recaptcha-response'];
$email_input = $_POST['email_input'];

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
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        /*****Checking if email exists ****/

        $stmt = $conn->prepare('SELECT user_id, email, username FROM user WHERE email = :email');
        $stmt->execute(array('email' => $email_input));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $result['email'];
        $user_id = $result['user_id'];
        $username = $result['username'];

        if (!empty($email)) {

            $salt = bin2hex(random_bytes(32));
            $hashed_user_id = hash('sha256', $user_id . $salt);
            //CREATE CONFIRMATION CODE
            $confirmation_code = bin2hex(random_bytes(32));
            $hashed_confirmation_code = password_hash($confirmation_code, PASSWORD_DEFAULT);

            $stmt = $conn->prepare('SELECT user_id, expires, CURRENT_TIMESTAMP  AS now  FROM password_reset WHERE user_id = :user_id');
            $stmt->execute(array('user_id' => $user_id));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $already_exists = !empty($result['user_id']);
            $now = $result['now'];
            $expires = $result['expires'];

            if (!$already_exists) {

                $stmt = $conn->prepare('INSERT INTO password_reset(user_id, hashed_user_id, validator, expires, last_send) 
                  VALUES(:user_id, :hashed_user_id, :validator, DATE_ADD(NOW(), INTERVAL 24 HOUR), DATE_ADD(NOW(), INTERVAL -10 MINUTE))');
                $stmt->execute(array('user_id' => $user_id, 'hashed_user_id' => $hashed_user_id,
                    'validator' => $hashed_confirmation_code));

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
                    $mail->Subject = 'Password Reset';
                    $mail->Body = '
<div style="width: 700px; margin: 0 auto;">
    <div style="background: black"><img src="https://www.bitcoinpvp.net/img/nav-logo.png" height="40"></div>

    <div style="width: 75%; margin: 50px auto; color: black;">

<p>Greetings <span style="color: red;"><b>' . $username . '</b></span>,</p>
        <p>We\'ve received a password reset request for your BitcoinPVP account.<br>
            To reset your password, click the link below: </p>

        <a href="' . $base_dir . 'password-reset/' . $hashed_user_id . '/' . $confirmation_code . '">Reset password</a>

        <p>This link will expire in 24 hours. If you did not request a password reset, you can ignore this email.</p>

        <p>For more information on your account — please visit your <a href="' . $base_dir . 'account">Account Management page.</a></p>

        <p>BitcoinPVP Team</p>
    </div>

    <div style="background: black; color: white; padding: 10px;">© ' . date('Y') . ' Copyright BitcoinPVP</div>
</div>';

                    $mail->send();
                } catch (Exception $e) {
                    echo $mail->ErrorInfo;
                }
            } else {
                $stmt = $conn->prepare('SELECT last_send, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL -10 MINUTE) AS now 
              FROM password_reset 
                WHERE user_id = :user_id');
                $stmt->execute(array('user_id' => $user_id));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $last_send = $result['last_send'];
                $now = $result['now'];

                if (strtotime($last_send) < strtotime($now)) {

                    $stmt = $conn->prepare('UPDATE password_reset SET hashed_user_id = :hashed_user_id, 
validator = :validator, expires = DATE_ADD(NOW(), INTERVAL 24 HOUR),
                    last_send = CURRENT_TIMESTAMP WHERE user_id = :user_id');
                    $stmt->execute(array('hashed_user_id' => $hashed_user_id, 'validator' => $hashed_confirmation_code, 'user_id' => $user_id));

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
                        $mail->Subject = 'Password Reset';
                        $mail->Body = '
<div style="width: 700px; margin: 0 auto;">
    <div style="background: black"><img src="https://www.bitcoinpvp.net/img/nav-logo.png" height="40"></div>

    <div style="width: 75%; margin: 50px auto; color: black;">

<p>Greetings <span style="color: red;"><b>' . $username . '</b></span>,</p>
        <p>We\'ve received a password reset request for your BitcoinPVP account.<br>
            To reset your password, click the link below: </p>

        <a href="' . $base_dir . 'password-reset/' . $hashed_user_id . '/' . $confirmation_code . '">Reset password</a>

        <p>This link will expire in 24 hours. If you did not request a password reset, you can ignore this email.</p>

        <p>For more information on your account — please visit your <a href="' . $base_dir . 'account">Account Management page.</a></p>

        <p>BitcoinPVP Team</p>
    </div>

    <div style="background: black; color: white; padding: 10px;">© ' . date('Y') . ' Copyright BitcoinPVP</div>
</div>';

                        $mail->send();

                    } catch (Exception $e) {
                        echo $mail->ErrorInfo;
                    }


                } else {
                    $_SESSION['too_soon_to_send_email_again'] = true;
                }

            }


            $_SESSION['password_reset_token'] = true;
            $_SESSION['password_reset_user_id'] = $user_id;
            header("Location: " . $base_dir . "password-reset-email-send");
            die();

            /*****************************/
        } else {
            $_SESSION['email_does_not_exist'] = true;
            $_SESSION['email_input'] = $email_input;
            header("Location: " . $base_dir . "forgot-password");
            die();
        }


    } catch
    (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

} else {
    $_SESSION['captcha_failed'] = true;
    header("Location: " . $base_dir . "forgot-password");
    die();
}
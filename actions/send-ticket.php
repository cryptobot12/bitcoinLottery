<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/22/17
 * Time: 6:07 PM
 */

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require '../vendor/autoload.php';

include '../function.php';
include '../globals.php';
include '../inc/login_checker.php';

$subject = htmlspecialchars($_POST['support_subject']);
$content = htmlspecialchars($_POST['support_content']);
$recaptcha_response = $_POST['g-recaptcha-response'];


$subject_max_length = 78;
$content_max_length = 2000;
$content_min_length = 60;

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
        // Success
        //Not empty and not longer than its max-length
        if (strlen($subject) <= $subject_max_length && strlen($content) <= $content_max_length && strlen($content) >= $content_min_length) {
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                $stmt = $conn->prepare('SELECT email FROM user WHERE user_id = :user_id');
                $stmt->execute(array('user_id' => $user_id));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $user_email = $result['email'];

                $validator = bin2hex(random_bytes(32));
                $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);

                $stmt = $conn->prepare('INSERT INTO ticket(user_id, submitted_on, validator, subject, content)
                                          VALUES(:user_id, CURRENT_TIMESTAMP, :hashed_validator, :subject, :content)');
                $stmt->execute(array('user_id' => $user_id, 'hashed_validator' => $hashed_validator, 'subject' => $subject,
                    'content' => $content));
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt = $conn->prepare('SELECT LAST_INSERT_ID() AS ticket_id');
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $ticket_id = $result['ticket_id'];


                $_SESSION['account_management_success'] = 5;

                /* Send email with code here */

                //TO SUPPORT

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
                    $mail->setFrom('no-reply@bitconpvp.net', 'BitcoinPVP Bot');
                    $mail->addAddress('support@bitcoinpvp.net');     // Add a recipient


                    //Content
                    $mail->CharSet = 'UTF-8';
                    $mail->isHTML(true);                                  // Set email format to HTML
                    $mail->Subject = $subject;
                    $mail->Body = '<div style="width: 700px; margin: 0 auto;">
    <div style="background: black"><img src="http://www.bitcoinpvp.net/img/nav-logo.png" height="40"></div>

    <div style="width: 75%; margin: 50px auto; color: black;">


<b>REPLY TO: </b><span>' . $user_email . '</span><br>
<b>USERNAME: </b><span>' . $username . '</span><br>
<b>TICKET ID: </b><span>' . $ticket_id . '</span>

<p>' . $content . '</p>

<a href="' . $base_dir . 'support/respond-ticket/' . $ticket_id . '/' . $validator . '">Click here to respond</a>
    </div>

    <div style="background: black; color: white; padding: 10px;">© ' . date('Y') . ' Copyright BitcoinPVP</div>
</div>';

                    $mail->send();
                    $mail->ClearAllRecipients();
                    // TO USER

                    $mail->setFrom('support@bitconpvp.net', 'BitcoinPVP Support Team');
                    $mail->addAddress($user_email);     // Add a recipient


                    //Content
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = '[BitcoinPVP Support] Ticket #' . $ticket_id;
                    $mail->Body = '<div style="width: 700px; margin: 0 auto;">
    <div style="background: black"><img src="http://www.bitcoinpvp.net/img/nav-logo.png" height="40"></div>

    <div style="width: 75%; margin: 50px auto; color: black;">


<p>Greetings <span style="color: red;"><b>' . $username . '</b></span>,<br><br>
            Thanks for contacting our Support Team! We\'ve received your ticket (#' . $ticket_id . ').</p>
            
            <div style="width: 90%; margin: auto; color: black;">
            <p><b>' . $subject . '</b></p>
            <p><i>' . $content . '</i></p>
            </div>
            
<p>Looks like a response will take a little longer than normal due to a high volume of requests, sit tight and one of
 our specialists will answer you as soon as they can. Try not to bump your ticket unless you have additional information
  that we could use to help you since bumping your ticket repeatedly will increase your wait time.</p>

        <p>For more information on your account — please visit your <a href="' . $base_dir . 'account">Account Management page.</a></p>

        <p>BitcoinPVP Support Team</p>


    </div>

    <div style="background: black; color: white; padding: 10px;">© ' . date('Y') . ' Copyright BitcoinPVP</div>
</div>';

                    $mail->send();


                } catch (Exception $e) {
                    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
                }

                header("Location: " . $base_dir . "account");
                die();

            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
        } else {

            $_SESSION['ticket_input_subject'] = $subject;
            $_SESSION['ticket_input_content'] = $content;
            $_SESSION['expand_ticket'] = true;
            header("Location: " . $base_dir . "account");
            die();

        }
    } else {
        $_SESSION['captcha_failed_ticket'] = true;

        $_SESSION['ticket_input_subject'] = $subject;
        $_SESSION['ticket_input_content'] = $content;
        $_SESSION['expand_ticket'] = true;
        header("Location: " . $base_dir . "account");
        die();
    }
} else {
    header("Location: " . $base_dir . "lost");
    die();
}




<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/22/17
 * Time: 6:07 PM
 */

session_start();

include '../function.php';
include '../connect.php';

$subject = htmlspecialchars($_POST['support_subject']);
$content = htmlspecialchars($_POST['support_content']);
$recaptcha_response = $_POST['g-recaptcha-response'];

$user_id = $_SESSION['user_id'];

$subject_max_length = 80;
$content_max_length = 2000;
$content_min_length = 50;

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
    // Success
    //Not empty and not longer than its max-length
    if (strlen($subject) <= $subject_max_length && strlen($content) <= $content_max_length && strlen($content) >= $content_min_length) {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $stmt = $conn->prepare('INSERT INTO ticket(subject, user_id, content, submitted_on)
                                          VALUES(:subject, :user_id, :content, CURRENT_TIMESTAMP)');
            $stmt->execute(array('subject' => $subject, 'user_id' => $user_id, 'content' => $content));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $_SESSION['account_management_success'] = 5;

            header("Location: ../account.php");
            die();

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
    else {

        if (strlen($subject) > $content_min_length)
            $_SESSION['ticket_subject_error'] = 1;

        if (strlen($content) > $content_max_length)
            $_SESSION['ticket_content_error'] = 1;

        if (strlen($content) < $content_min_length)
            $_SESSION['ticket_content_error'] = 2;

        $_SESSION['ticket_input_subject'] = $subject;
        $_SESSION['ticket_input_content'] = $content;

        header("Location: ../account.php");
        die();

    }
} else {
    echo "Are you a bot?";
}




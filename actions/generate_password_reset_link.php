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

include '../connect.php';
include "../inc/login_checker.php";

$recaptcha_response = $_POST['g-recaptcha-response'];

/* Captcha verifying */
$privatekey = "6Lf1d0EUAAAAAPhwWXktY_b1rBWR_Cl";


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
    echo "WOIRKDE";
} else {
    $_SESSION['captcha_failed'] = 1;
    header("Location: ../forgot_password");
    die();
}
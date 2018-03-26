<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/18/17
 * Time: 5:41 PM
 */
session_start();

include '../globals.php';
include '../function.php';
include '../inc/login_checker.php';


$amount = htmlspecialchars($_POST['transfer_amount']);
$to_user = htmlspecialchars($_POST['transfer_user']);
$hash = rand_string(64);
/*
 * HERE YOU SHOULD DO SOMETHING TO REPLACE THIS FAKE HASH
 *
 *
 *
 *
 * */

$recaptcha_response = $_POST['g-recaptcha-response'];
var_dump($recaptcha_response);

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

var_dump($captcha_success);

if ($logged_in) {
    if ($captcha_success->success) {
        if (!empty($amount) && !empty($to_user)) {
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


                    //Selecting user_id for to_user
                    $stmt = $conn->prepare('SELECT user_id FROM user WHERE username = :username');
                    $stmt->execute(array('username' => $to_user));
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!empty($result['user_id'])) {
                        $user_exists = true;

                        $to_user_id = $result['user_id'];

                        if ($result['user_id'] == $user_id)
                            $is_the_same_user = true;
                        else
                            $is_the_same_user = false;
                    } else
                        $user_exists = false;

                if (ctype_digit($amount) && $amount > 100) {
                    //Checking balance
                    $stmt = $conn->prepare('SELECT balance FROM user WHERE user_id = :user_id');
                    $stmt->execute(array('user_id' => $user_id));
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $balance = $result['balance'];

                    if ($balance >= ($amount * 100 + 10000))
                        $enough_balance = true;
                    else
                        $enough_balance = false;
                }

            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }

            if (ctype_digit($amount) && $amount > 100 && $user_exists && $enough_balance && !$is_the_same_user) {

                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                    // set the PDO error mode to exception
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                    $amount = $amount * 100;

                    //Insert into transfer history
                    $stmt = $conn->prepare('INSERT INTO transfer(user_id, to_user, hash, request_date, completed_on, amount)
         VALUES (:user_id, :to_user, :hash, CURRENT_DATE(), NULL, :amount)');
                    $stmt->execute(array('user_id' => $user_id, 'to_user' => $to_user_id, 'hash' => $hash, 'amount' => $amount));

                    //Updating user balance
                    $stmt = $conn->prepare('UPDATE user SET balance = balance - :to_subtract WHERE user_id = :user_id');
                    $stmt->execute(array('to_subtract' => $amount + 10000, 'user_id' => $user_id));

                    /*
                     *
                     *
                     *  BITCOIN TRANSACTION HERE
                     *
                     *
                     * */


                    $_SESSION['account_management_success'] = 4;

                    header('Location: ' . $base_dir . 'account');
                    die();

                } catch (PDOException $e) {
                    echo "Connection failed: " . $e->getMessage();
                }

            } else {

                if (!$enough_balance) {
                    $_SESSION['transfer_not_enough_balance'] = true;
                }

                $_SESSION['transfer_amount_input'] = $amount;
                $_SESSION['transfer_user_input'] = $to_user;

                header('Location: ' . $base_dir . 'account');
                die();
            }
        } else {
            $_SESSION['transfer_empty_fields'] = true;
            $_SESSION['transfer_amount_input'] = $amount;
            $_SESSION['transfer_user_input'] = $to_user;

            header('Location: ' . $base_dir . 'account');
            die();
        }
    } else {

        $_SESSION['captcha_failed_transfer'] = true;
        $_SESSION['transfer_amount_input'] = $amount;
        $_SESSION['transfer_user_input'] = $to_user;

        header('Location: ' . $base_dir . 'account');
        die();
    }
} else {
    header("Location: " . $base_dir . "lost");
    die();
}
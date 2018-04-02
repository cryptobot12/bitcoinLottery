<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/10/17
 * Time: 4:36 PM
 *
 */
session_start();

include '../globals.php';
include '../function.php';
include '../inc/login_checker.php';


$withdraw_address = $_POST['withdraw_address'];
$amount = $_POST['withdraw_amount'];
$hash = rand_string(64);
/*
 * HERE YOU SHOULD DO SOMETHING TO REPLACE THIS FAKE HASH
 *
 *
 *
 *
 * */

$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_new_password = $_POST['confirm_new_password'];

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


if ($logged_in) {
    if ($captcha_success->success) {
        if (!empty($amount) && !empty($withdraw_address)) {
            if (ctype_digit($amount)) {
                if ($amount <= 100) {
                    $_SESSION['withdraw_amount_input'] = $amount;
                    $_SESSION['withdraw_address_input'] = $withdraw_address;
                    $_SESSION['expand_withdraw'] = true;
                    header("Location: " . $base_dir . "account");
                    die();
                } else {
//        HERE YOU SHOULD VALIDATE THE BITCOIN ADDRESS
                    if (true) {

                        try {
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                            // set the PDO error mode to exception
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                            $stmt = $conn->prepare('SELECT balance FROM user WHERE user_id = :user_id');
                            $stmt->execute(array('user_id' => $user_id));
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            $balance = $result['balance'];

                        } catch (PDOException $e) {
                            echo "Connection failed: " . $e->getMessage();
                        }

                        $amount = $amount * 100;

                        if (($balance + 10000) >= $amount) {


                            try {
                                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                                // set the PDO error mode to exception
                                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                                $stmt = $conn->prepare('INSERT INTO 
                  withdrawal(user_id, hash, request_date, completed_on, amount) VALUES
                    (:user_id, :hash, CURRENT_TIMESTAMP, NULL, :amount)');
                                $stmt->execute(array('user_id' => $user_id, 'hash' => $hash, 'amount' => $amount));

                                $stmt = $conn->prepare('UPDATE user SET balance = balance - :subtract
                        WHERE user_id = :user_id');

                                $stmt->execute(array('subtract' => ($amount + 10000), 'user_id' => $user_id));

                            } catch (PDOException $e) {
                                echo "Connection failed: " . $e->getMessage();
                            }


                            /*******DO THE BITCOIN TRANSACTION HERE*******/


                            /**********************************************/
                            $_SESSION['account_management_success'] = 4;
                            header("Location: " . $base_dir . "account");
                            die();
                        } else {
                            $amount = $amount / 100;
                            $_SESSION['withdraw_amount_input'] = $amount;
                            $_SESSION['withdraw_address_input'] = $withdraw_address;
                            $_SESSION['withdraw_insufficient'] = true;
                            $_SESSION['expand_withdraw'] = true;

                            header("Location: " . $base_dir . "account");
                            die();
                        }
                    } else {
                        $_SESSION['withdraw_amount_input'] = $amount;
                        $_SESSION['withdraw_address_input'] = $withdraw_address;
                        $_SESSION['withdraw_invalid_address'] = true;
                        $_SESSION['expand_withdraw'] = true;

                        header("Location: " . $base_dir . "account");
                        die();
                    }

                }
            } else {
                $_SESSION['withdraw_amount_input'] = $amount;
                $_SESSION['withdraw_address_input'] = $withdraw_address;
                $_SESSION['expand_withdraw'] = true;

                header("Location: " . $base_dir . "account");
                die();
            }
        } else {
            $_SESSION['withdraw_empty_fields'] = true;
            $_SESSION['withdraw_amount_input'] = $amount;
            $_SESSION['withdraw_address_input'] = $withdraw_address;
            $_SESSION['expand_withdraw'] = true;

            header("Location: " . $base_dir . "account");
            die();
        }
    } else {
        $_SESSION['captcha_failed_withdraw'] = true;
        $_SESSION['withdraw_amount_input'] = $amount;
        $_SESSION['withdraw_address_input'] = $withdraw_address;
        $_SESSION['expand_withdraw'] = true;

        header("Location: " . $base_dir . "account");
        die();
    }
} else {
    header("Location: " . $base_dir . "lost");
    die();
}



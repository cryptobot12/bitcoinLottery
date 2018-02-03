<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/17/2017
 * Time: 6:02 PM
 */
session_start();

include '../function.php';
include "../connect.php";


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

                if (!empty($user_exists_row))
                    $_SESSION['user_already_exists'] = true;

            } else {
                $valid_username = false;
                $_SESSION['invalid_username'] = true;
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

                if (!empty($email_exists_row))
                    $_SESSION['email_already_exists'] = true;

            } else {
                $valid_email = false;
                $_SESSION['invalid_email'] = true;
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
                $_SESSION['password_not_match'] = true;
            }

            if (strlen($password) >= 8 && strlen($password) <= 72) {
                $password_length_valid = true;
            } else {
                $password_length_valid = false;
                $_SESSION['password_length_error'] = true;
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
            $hashed_user_id = hash('sha256', $user_id);

            //CREATE CONFIRMATION CODE
            $confirmation_code = bin2hex(random_bytes(32));
            $stmt = $conn->prepare('INSERT INTO email_confirmation(user_id, hashed_user_id, validator, expires)
            VALUES (:user_id, :hashed_user_id, :validator, ADDDATE(CURRENT_TIMESTAMP, INTERVAL 3 HOUR))');
            $stmt->execute(array('user_id' => $user_id, 'hashed_user_id' => $hashed_user_id, 'validator' => $confirmation_code));

            //Let's login the new user
            $_SESSION['auth_token'] = json_encode(array('username' => $username, 'user_id' => $user_id));

            header("Location: ../account.php");
            die();


        } else {

            $_SESSION['input_username'] = $username;
            $_SESSION['input_password'] = $password;
            $_SESSION['input_confirm_password'] = $confirm_password;
            $_SESSION['input_email'] = $email;

            header("Location: ../registration.php");
            die();
        }


    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    } catch (Exception $e) {
        echo $e->getMessage();
    }

} else {
    $_SESSION['captcha_failed'] = 1;
    header("Location: ../registration.php");

}
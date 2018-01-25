<?php
session_start();
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/17/2017
 * Time: 7:23 PM
 */

include "../connect.php";
$username = htmlspecialchars($_POST['username']);
$password = htmlspecialchars($_POST['password']);
$remember_me = (!empty($_POST['remember_me']) ? true : false);
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

        $stmt = $conn->prepare('SELECT user_id, username, balance, password FROM user WHERE username = :username');
        $stmt->execute(array('username' => $username));
        $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

        // If user exists
        if (!empty($user_info)) {

            // If password is correct
            if (password_verify($password, $user_info['password'])) {

                if ($remember_me) {

                    //Making sure selector is unique
                    do {
                        $selector = bin2hex(random_bytes(6));

                        $stmt = $conn->prepare('SELECT auth_token_id FROM auth_token 
                          WHERE selector = :selector');
                        $stmt->execute(array('selector' => $selector));
                        $auth_token_result = $stmt->fetch(PDO::FETCH_ASSOC);

                    } while (!empty($auth_token_result));

                    $validator = bin2hex(random_bytes(32));
                    $hashed_validator = hash('sha256', $validator);
                    $user_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 512) : '';
                    $ip_address = $_SERVER['REMOTE_ADDR'];

                    //Remember me
                    $stmt = $conn->prepare('SELECT auth_token_id FROM auth_token 
                          WHERE user_id = :user_id
                          AND user_agent = :user_agent
                          AND ip_address = :ip_address');
                    $stmt->execute(array('user_id' => $user_info['user_id'], 'user_agent' => $user_agent, 'ip_address' => $ip_address));
                    $auth_token_result = $stmt->fetch(PDO::FETCH_ASSOC);

//                    If exists
                    if (empty($auth_token_result)) {
                        //Creating auth token
                        $stmt = $conn->prepare('INSERT INTO auth_token(selector, hashed_validator, user_id, user_agent, ip_address, expires)
                    VALUES(:selector, :hashed_validator, :user_id, :user_agent, :ip_address, (ADDDATE(CURRENT_TIMESTAMP, INTERVAL 7 DAY)))');
                        $stmt->execute(array('selector' => $selector, 'hashed_validator' => $hashed_validator, 'user_id' => $user_info['user_id'], 'user_agent' => $user_agent, 'ip_address' => $ip_address));
                    } else {
//                        Just update expire time and selector and validator
                        $stmt = $conn->prepare('UPDATE auth_token SET selector = :selector, hashed_validator = :hashed_validator,
                        expires = (ADDDATE(CURRENT_TIMESTAMP, INTERVAL 7 DAY))
                        WHERE user_id = :user_id
                          AND user_agent = :user_agent
                          AND ip_address = :ip_address');
                        $stmt->execute(array('selector' => $selector, 'hashed_validator' => $hashed_validator, 'user_id' => $user_info['user_id'], 'user_agent' => $user_agent, 'ip_address' => $ip_address));
                    }

                    $expires = time() + (86400 * 365);
                    $cookie_data = array('selector' => $selector, 'validator' => $validator, 'expires' => $expires);
                    setcookie('auth_token', json_encode($cookie_data), $cookie_data['expires'], "/");

                } else {
                    $_SESSION['auth_token'] = json_encode(array('username' => $user_info['username'], 'user_id' => $user_info['user_id']));
                }

                if (!empty($_SESSION['last_url']))
                    header("Location: ../" . $_SESSION['last_url']);
                else
                    header("Location: ../index.php");
                die();
            } else {

                $_SESSION['login_error'] = 3;
                header("Location: ../login.php");
                die();
            }
        } else {
            $_SESSION['login_error'] = 2;
            header("Location: ../login.php");
            die();
        }

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    } catch (Exception $e) {
        echo $e->getMessage();
    }

} else {
    $_SESSION['login_error'] = 1;
    header("Location: ../login.php");
    die();
}
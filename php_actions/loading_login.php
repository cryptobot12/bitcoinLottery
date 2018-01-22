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
        //echo "Connected successfully";
        $stmt = $conn->prepare('SELECT user_id, username, balance, password FROM user WHERE username = :username');
        $stmt->execute(array('username' => $username));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // If user exists
        if (!empty($row)) {

            // If password is correct
            if (password_verify($password, $row['password'])) {

                $selector = bin2hex(random_bytes(6));
                $validator = bin2hex(random_bytes(32));
                $hashed_validator = hash('sha256', $validator);
                $user_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 512) : '';
                $ip_address = $_SERVER['REMOTE_ADDR'];

                if ($remember_me) {
                    //Remember me
                    $stmt = $conn->prepare('SELECT auth_token_id FROM auth_token 
                          WHERE user_id = :user_id
                          AND user_agent = :user_agent
                          AND ip_address = :ip_address');
                    $stmt->execute(array('user_id' => $row['user_id'], 'user_agent' => $user_agent, 'ip_address' => $ip_address));
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

//                    If exists
                    if (!empty($row)) {
                        //Creating auth token
                        $stmt = $conn->prepare('INSERT INTO auth_token(selector, hashed_validator, user_id, user_agent, ip_address, expires)
                    VALUES(:selector, :hashed_validator, :user_id, :user_agent, :ip_address, (ADDDATE(CURRENT_TIMESTAMP, INTERVAL 7 DAY)))');
                        $stmt->execute(array('selector' => $selector, 'hashed_validator' => $hashed_validator, 'user_id' => $row['user_id'], 'user_agent' => $user_agent, 'ip_address' => $ip_address));
                    } else {
//                        Just update expire time and selector and validator
                        $stmt = $conn->prepare('UPDATE auth_token SET selector = :selector, hashed_validator = :hashed_validator,
                        expires = (ADDDATE(CURRENT_TIMESTAMP, INTERVAL 7 DAY))
                        WHERE user_id = :user_id
                          AND user_agent = :user_agent
                          AND ip_address = :ip_address');
                        $stmt->execute(array('selector' => $selector, 'hashed_validator' => $hashed_validator, 'user_id' => $row['user_id'], 'user_agent' => $user_agent, 'ip_address' => $ip_address));
                    }

                    setcookie('selector', $selector, time() + (86400 * 30), "/");
                    setcookie('validator', $validator, time() + (86400 * 30), "/");

                } else {
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['user_id'] = $row['user_id'];
                }

                //Selecting current game
                $stmt = $conn->prepare('SELECT game_id FROM game ORDER BY game_id DESC, timedate DESC LIMIT 1');
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $current_game = $row['game_id'];

                //Selecting numbers list
                $stmt = $conn->prepare('SELECT number_id FROM numberxuser WHERE user_id = (SELECT user_id
        FROM user WHERE username = :username) AND game_id = :game_id');
                $stmt->execute(array('username' => $_SESSION['username'], 'game_id' => $current_game));
                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);


                $arrayOfNumbers = array();
                foreach ($row as $item) {
                    array_push($arrayOfNumbers, $item['number_id']);
                }

                $_SESSION["numbers_list"] = $arrayOfNumbers;


                header("Location: " . $_SESSION['url']);
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
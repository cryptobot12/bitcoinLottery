<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 1/22/2018
 * Time: 3:26 AM
 */

//Is there a logged in user?

if (!empty($_SESSION['auth_token'])) {
    $auth_token = json_decode($_SESSION['auth_token']);

    $logged_in = true;

    $username = $auth_token->username;
    $user_id = $auth_token->user_id;

} elseif (!empty($_COOKIE['auth_token'])) {
    $auth_token = json_decode($_COOKIE['auth_token']);

    //If cookie is not expired, assuming it is sent to the server
    if ($auth_token->expires >= time()) {

        $selector = $auth_token->selector;
        $validator = $auth_token->validator;

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $stmt = $conn->prepare('SELECT selector, user_id, hashed_validator, expires, CURRENT_TIMESTAMP FROM auth_token WHERE selector = :selector');
            $stmt->execute(array('selector' => $selector));
            $login_token = $stmt->fetch(PDO::FETCH_ASSOC);

            $user_agent = substr($_SERVER['HTTP_USER_AGENT'], 0, 8192);
            $ip_address = $_SERVER['REMOTE_ADDR'];

            if (!empty($login_token)) {
                if (hash_equals(hash('sha256', $validator), $login_token['hashed_validator'])
                    && strtotime($login_token['CURRENT_TIMESTAMP']) < strtotime($login_token['expires'])) {

                    $user_id = $login_token['user_id'];
//                    Updating expire time every time this is loaded
                    $stmt = $conn->prepare('UPDATE auth_token SET user_agent = :user_agent,
                                                  ip_address = :ip_address
                                                  WHERE selector = :selector');
                    $stmt->execute(array('user_agent' => $user_agent, 'ip_address' => $ip_address, 'selector' => $selector));

//                    Getting username
                    $stmt = $conn->prepare('SELECT username FROM user WHERE user_id = :user_id');
                    $stmt->execute(array('user_id' => $user_id));
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    $username = $result['username'];
                    $logged_in = true;
                }
            } else {
                $logged_in = false;
            }

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
} else
    $logged_in = false;

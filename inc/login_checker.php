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

    //If cookie is not expires, assuming it is sent to the server
    if (strtotime($auth_token->expires) < time()) {

        $selector = $auth_token->selector;
        $validator = $auth_token->validator;

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $stmt = $conn->prepare('SELECT user_id, hashed_validator, user_agent, ip_address, expires, CURRENT_TIMESTAMP FROM auth_token WHERE selector = :selector');
            $stmt->execute(array('selector' => $selector));
            $login_token = $stmt->fetch(PDO::FETCH_ASSOC);

            $user_agent = substr($_SERVER['HTTP_USER_AGENT'], 0, 512);
            $ip_address = $_SERVER['REMOTE_ADDR'];

            if (!empty($login_token)) {
                if (hash_equals(hash('sha256', $validator), $login_token['hashed_validator'])
                    && $user_agent == $login_token['user_agent']
                    && $ip_address == $login_token['ip_address']
                    && strtotime($login_token['CURRENT_TIMESTAMP']) < strtotime($login_token['expires'])) {

                    $user_id = $login_token['user_id'];
//                    Updating expire time every time this is loaded
                    $stmt = $conn->prepare('UPDATE auth_token SET expires = (ADDDATE(CURRENT_TIMESTAMP, INTERVAL 7 DAY))
                                                  WHERE user_id = :user_id
                                                  AND user_agent = :user_agent
                                                  AND ip_address = :ip_address');
                    $stmt->execute(array('user_id' => $user_id, 'user_agent' => $user_agent, 'ip_address' => $ip_address));

//                    Getting username
                    $stmt = $conn->prepare('SELECT username FROM user WHERE user_id = :user_id');
                    $stmt->execute(array('user_id' => $user_id));
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    $username = $result['username'];
                    $logged_in = true;
                }
            }

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
} else
    $logged_in = false;






<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 3/9/18
 * Time: 1:10 AM
 */
session_start();

include '../connect.php';
include '../inc/base-dir.php';

$selector = $_POST['s'];
$validator = $_POST['v'];
$new_password = $_POST['new_password'];
$confirm_new_password = $_POST['confirm_new_password'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT user_id, expires, current_timestamp AS now FROM password_reset WHERE hashed_user_id = :selector
AND validator = :validator');
    $stmt->execute(array('selector' => $selector, 'validator' => $validator));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $reset_user_id = $result['user_id'];
    $expires = $result['expires'];
    $now = $result['now'];

    if (!empty($reset_user_id)) {
        if (strtotime($expires) < strtotime($now)) {
            header("Location: " . $base_dir . "lost");
            die();
        } else {

            if (!empty($new_password) && strlen($new_password) >= 8 && $new_password == $confirm_new_password) {
                echo "TEST PASSED";
            } else {
                if (strlen($new_password) < 8)
                    $_SESSION['short_password'] = true;

                if ($new_password != $confirm_new_password)
                    $_SESSION['unmatched_password'] = true;

                $_SESSION['new_password_input'] = $new_password;
                $_SESSION['confirm_new_password_input'] = $confirm_new_password;

                header("Location: " . $base_dir . "password-reset/" . $selector . "/" . $validator);
                die();
            }
        }
    } else {
        header("Location: " . $base_dir . "lost");
        die();
    }


} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
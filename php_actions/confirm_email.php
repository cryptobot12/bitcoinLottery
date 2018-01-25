<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 1/25/2018
 * Time: 1:28 PM
 */
session_start();

include "../connect.php";

$hashed_user_id = $_GET['sel'];
$code = $_GET['val'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT user_id, expires, CURRENT_TIMESTAMP FROM email_confirmation WHERE hashed_user_id = :hashed_user_id
    AND code = :code');
    $stmt->execute(array('hashed_user_id' => $hashed_user_id, 'code' => $code));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($result)) {
        $user_id = $result['user_id'];
        $expires = $result['expires'];
        $current_time = $result['CURRENT_TIMESTAMP'];

        var_dump($current_time);
        var_dump($expires);

        if (strtotime($current_time) < strtotime($expires)) {
            $stmt = $conn->prepare('UPDATE user SET enabled = TRUE WHERE user_id = :user_id');
            $stmt->execute(array('user_id' => $user_id));

            $stmt = $conn->prepare('UPDATE email_confirmation SET expires = CURRENT_TIMESTAMP WHERE hashed_user_id = :hashed_user_id
    AND code = :code');
            $stmt->execute(array('hashed_user_id' => $hashed_user_id, 'code' => $code));

            header("Location: ../account.php");
            die();
        } else {

            header("Location: ../expired_link.php");
            die();
        }

    } else {
        header("Location: ../error.php");
        die();
    }


} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
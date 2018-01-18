<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/25/17
 * Time: 3:04 PM
 */
session_start();

include "../connect.php";

$codeInput = strtoupper($_POST['code']);
$user_id = $_SESSION['user_id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT code, code_expires FROM user WHERE user_id = :user_id');
    $stmt->execute(array('user_id' => $user_id));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $code = $row['code'];
    $code_expires = $row['code_expires'];

    //Selecting current time
    $stmt = $conn->prepare('SELECT NOW()');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_time = $result['NOW()'];

    if ($code == $codeInput && (strtotime($code_expires) > $current_time)) {

        $stmt = $conn->prepare('UPDATE user SET email = new_email, code_expires = (CURRENT_TIMESTAMP - INTERVAL 1 DAY) WHERE user_id = :user_id');
        $stmt->execute(array('user_id' => $user_id));

        $_SESSION['email_updated'] = true;

        /*************************************
         *
         * Email here
         */

        $_SESSION['account_management_success'] = 1;

        header('Location: ../account.php');
        die();
    }
    else {
        $_SESSION['incorrect_code'] = true;
        $_SESSION['input_code'] = $codeInput;
        $_SESSION['upd_email'] = true;
        header("Location: ../account.php" );
        die();
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

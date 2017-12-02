<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/25/17
 * Time: 3:04 PM
 */
session_start();

include "../connect.php";

$codeInput = $_POST['code'];
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

    if ($code == $codeInput && (strtotime($code_expires) > time())) {

        $stmt = $conn->prepare('UPDATE user SET email = new_email, code_expires = CURRENT_TIMESTAMP WHERE user_id = :user_id');
        $stmt->execute(array('user_id' => $user_id));

        $_SESSION['email_updated'] = true;

        /*************************************
         *
         * Email here
         */

        header("Location: ../email_updated.php");
    }
    else {
        $_SESSION['incorrect_code'] = true;
        header("Location: ../account.php" );
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

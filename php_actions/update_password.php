<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/3/17
 * Time: 11:51 AM
 */
session_start();

include "../connect.php";

$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_new_password = $_POST['confirm_new_password'];
$user_id = $_SESSION['user_id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);

    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT password FROM user WHERE user_id = :user_id');
    $stmt->execute(array('user_id' => $user_id));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($current_password, $row['password']) && strlen($new_password) >= 8
        && $new_password == $confirm_new_password && $new_password != $current_password) {

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE user SET password = :new_password
            WHERE user_id = :user_id');
        $stmt->execute(array('new_password' => $hashed_password, 'user_id' => $user_id));


        /*************************SEND EMAIL HERE*******************/


        /************************************************************/
        $_SESSION['account_management_success'] = 2;
        header("Location: ../account.php");
        die();


    } else {

        if (strlen($new_password) < 8) {
            $_SESSION['incorrect_length'] = true;
        }


        if (!password_verify($current_password, $row['password']))
            $_SESSION['incorrect_cp'] = true;
        elseif ($current_password == $new_password)
            $_SESSION['diff_pass'] = true;

        if ($new_password != $confirm_new_password)
            $_SESSION['unmatch_p'] = true;


        $_SESSION['current_password'] = $current_password;
        $_SESSION['new_password'] = $new_password;
        $_SESSION['confirm_new_password'] = $confirm_new_password;

        header("Location: ../account.php");
        die();
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}


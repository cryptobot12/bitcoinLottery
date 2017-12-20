<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/18/17
 * Time: 5:41 PM
 */
session_start();

include '../connect.php';
include '../function.php';


$amount = htmlspecialchars($_POST['transfer_amount']);
$user_id = $_SESSION['user_id'];
$to_user = htmlspecialchars($_POST['transfer_user']);
$hash = rand_string(64);
/*
 * HERE YOU SHOULD DO SOMETHING TO REPLACE THIS FAKE HASH
 *
 *
 *
 *
 * */

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    if (!empty($to_user)) {
        //Selecting user_id for to_user
        $stmt = $conn->prepare('SELECT user_id FROM user WHERE username = :username');
        $stmt->execute(array('username' => $to_user));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($result['user_id'])) {
            $user_exists = true;

            $to_user_id = $result['user_id'];

            if ($result['user_id'] == $user_id)
                $is_the_same_user = true;
            else
                $is_the_same_user = false;
        } else
            $user_exists = false;
    }

    if (!empty($amount) && ctype_digit($amount) && $amount > 100) {
        //Checking balance
        $stmt = $conn->prepare('SELECT balance FROM user WHERE user_id = :user_id');
        $stmt->execute(array('user_id' => $user_id));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $balance = $result['balance'];

        if ($balance >= ($amount * 100 + 10000))
            $enough_balance = true;
        else
            $enough_balance = false;
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

if (ctype_digit($amount) && $amount > 100 && $user_exists && $enough_balance && !$is_the_same_user && !empty($to_user)
    && !empty($amount)) {

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $amount = $amount * 100;

        //Insert into transfer history
        $stmt = $conn->prepare('INSERT INTO transfer(user_id, to_user, hash, request_date, completed_on, amount)
         VALUES (:user_id, :to_user, :hash, CURRENT_DATE(), NULL, :amount)');
        $stmt->execute(array('user_id' => $user_id, 'to_user' => $to_user_id, 'hash' => $hash, 'amount' => $amount));

        //Updating user balance
        $stmt = $conn->prepare('UPDATE user SET balance = balance - :to_subtract WHERE user_id = :user_id');
        $stmt->execute(array('to_subtract' => $amount + 10000,'user_id' => $user_id));

        /*
         *
         *
         *  BITCOIN TRANSACTION HERE
         *
         *
         * */


        $_SESSION['account_management_success'] = 4;

        header('Location: ../account.php');
        die();

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

} else {

    if (empty($amount))
        $_SESSION['transfer_user_error'] = 1;
    elseif (!ctype_digit($amount)) {
        $_SESSION['transfer_amount_error'] = 2;
    } elseif ($amount <= 100) {
        $_SESSION['transfer_amount_error'] = 3;
    } elseif (!$enough_balance) {
        $_SESSION['transfer_amount_error'] = 4;
    }

    if (empty($to_user))
        $_SESSION['transfer_user_error'] = 1;
    elseif (!$user_exists)
        $_SESSION['transfer_user_error'] = 2;
    elseif ($is_the_same_user)
        $_SESSION['transfer_user_error'] = 3;

    $_SESSION['transfer_amount_input'] = $amount;
    $_SESSION['transfer_user_input'] = $to_user;

    header('Location: ../account.php');
    die();
}
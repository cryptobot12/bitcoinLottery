<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/10/17
 * Time: 4:36 PM
 *
 */
session_start();

include '../globals.php';
include '../function.php';

function decodeBase58($input)
{
    $alphabet = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";

    $out = array_fill(0, 25, 0);
    for ($i = 0; $i < strlen($input); $i++) {
        if (($p = strpos($alphabet, $input[$i])) === false) {
            throw new \Exception("Invalid character found");
        }
        $c = $p;
        for ($j = 25; $j--;) {
            $c += (int)(58 * $out[$j]);
            $out[$j] = (int)($c % 256);
            $c /= 256;
            $c = (int)$c;
        }
        if ($c != 0) {
            throw new \Exception("Address too long");
        }
    }

    $result = "";
    foreach ($out as $val) {
        $result .= chr($val);
    }

    return $result;
}

function validate($address)
{
    $decoded = decodeBase58($address);

    $d1 = hash("sha256", substr($decoded, 0, 21), true);
    $d2 = hash("sha256", $d1, true);

    if (substr_compare($decoded, $d2, 21, 4)) {
        throw new \Exception("Invalid address");
    }
    return true;
}


$withdraw_address = $_POST['withdraw_address'];
$amount = $_POST['withdraw_amount'];
$user_id = $_SESSION['user_id'];
$hash = rand_string(64);
/*
 * HERE YOU SHOULD DO SOMETHING TO REPLACE THIS FAKE HASH
 *
 *
 *
 *
 * */

if (ctype_digit($amount)) {
    if ($amount <= 100) {
        $_SESSION['withdraw_amount_error'] = "Amount must be greater than 100";
        $_SESSION['withdraw_amount_input'] = $amount;
        $_SESSION['withdraw_address_input'] = $withdraw_address;
        header("Location: ../account.php");
        die();
    } else {
        try {
            if (validate($withdraw_address)) {

                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                    // set the PDO error mode to exception
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                    $stmt = $conn->prepare('SELECT balance FROM user WHERE user_id = :user_id');
                    $stmt->execute(array('user_id' => $user_id));
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $balance = $result['balance'];

                }
                catch(PDOException $e)
                {
                    echo "Connection failed: " . $e->getMessage();
                }

                $amount = $amount * 100;

                if (($balance + 10000) >= $amount) {


                    try {
                        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                        // set the PDO error mode to exception
                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                        $stmt = $conn->prepare('INSERT INTO 
                  withdrawal(user_id, hash, request_date, completed_on, amount) VALUES
                    (:user_id, :hash, CURRENT_DATE(), NULL, :amount)');
                        $stmt->execute(array('user_id' => $user_id, 'hash' => $hash, 'amount' => $amount));

                        $stmt = $conn->prepare('UPDATE user SET balance = balance - :subtract
                        WHERE user_id = :user_id');

                        $stmt->execute(array('subtract' => ($amount + 10000), 'user_id' => $user_id));

                    } catch (PDOException $e) {
                        echo "Connection failed: " . $e->getMessage();
                    }


                    /*******DO THE BITCOIN TRANSACTION HERE*******/


                    /**********************************************/
                    $_SESSION['account_management_success'] = 4;
                    header("Location: ../account.php");
                    die();
                }
                else {
                    $_SESSION['withdraw_amount_input'] = $amount;
                    $_SESSION['withdraw_address_input'] = $withdraw_address;
                    $_SESSION['withdraw_insufficient'] = true;
                    header("Location: ../account.php");
                    die();
                }
            }
        } catch (Exception $e) {

            $_SESSION['withdraw_address_error'] = $e->getMessage();
            $_SESSION['withdraw_address_input'] = $withdraw_address;
            $_SESSION['withdraw_amount_input'] = $amount;

            header("Location: ../account.php");
            die();
        }


    }
} else {
    $_SESSION['withdraw_amount_error'] = "Amount must be an integer number";
    $_SESSION['withdraw_amount_input'] = $amount;
    $_SESSION['withdraw_address_input'] = $withdraw_address;
    header("Location: ../account.php");
    die();
}



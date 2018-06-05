<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/18/17
 * Time: 5:41 PM
 */
session_start();

require_once '/var/www/html/bitcoinLottery/vendor/autoload.php';

include '../globals.php';
include '../function.php';
include '../inc/login_checker.php';


$amount = htmlspecialchars($_POST['transfer_amount']);
$to_user = htmlspecialchars($_POST['transfer_user']);

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

if ($logged_in) {
    if ($captcha_success->success) {
        $driver = new \Nbobtc\Http\Driver\CurlDriver();
        $driver
            ->addCurlOption(CURLOPT_VERBOSE, true)
            ->addCurlOption(CURLOPT_STDERR, '/var/logs/curl.err');

        $client = new \Nbobtc\Http\Client('http://puppetmaster:vz6qGFsHBv5auSSDhTPWPktVu@localhost:18332');
        $client->withDriver($driver);

        if (!empty($amount) && !empty($to_user)) {
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


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

                if (ctype_digit($amount)) {
                    //Checking balance

                    $stmt = $conn->prepare('SELECT balance FROM balances WHERE username = :username');
                    $stmt->execute(array('username' => $username));
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $balance = $row['balance'];

                    $amount_in_bitcoin = $amount / 1000000;
                    $balance_in_bitcoin = $balance / 100000000;

                    if ($balance_in_bitcoin >= $amount_in_bitcoin)
                        $not_enough_balance = false;
                    else
                        $not_enough_balance = true;
                }

            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }

            if (ctype_digit($amount) && $user_exists && !$not_enough_balance && !$is_the_same_user) {

                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                    // set the PDO error mode to exception
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                    $amount_in_satoshi = $amount * 100;

                    //Insert into transfer history
                    $stmt = $conn->prepare('INSERT INTO transfer(user_id, to_user, transfer_time, amount)
         VALUES (:user_id, :to_user, CURRENT_TIMESTAMP, :amount)');
                    $stmt->execute(array('user_id' => $user_id, 'to_user' => $to_user_id, 'amount' => $amount_in_satoshi));

                    /*
                     *
                     *
                     *  BITCOIN TRANSACTION HERE
                     *
                     *
                     */


                    $command = new \Nbobtc\Command\Command('move', array($username, $to_user, $amount_in_bitcoin));

                    /** @var \Nbobtc\Http\Message\Response */
                    $response = $client->sendCommand($command);

                     /*         * */


                    $_SESSION['account_management_success'] = 4;

                    header('Location: ' . $base_dir . 'account');
                    die();

                } catch (PDOException $e) {
                    echo "Connection failed: " . $e->getMessage();
                }

            } else {

                if ($not_enough_balance) {
                    $_SESSION['transfer_not_enough_balance'] = true;
                }

                $_SESSION['transfer_amount_input'] = $amount;
                $_SESSION['transfer_user_input'] = $to_user;
                $_SESSION['expand_transfer'] = true;

                header('Location: ' . $base_dir . 'account');
                die();
            }
        } else {
            $_SESSION['transfer_empty_fields'] = true;
            $_SESSION['transfer_amount_input'] = $amount;
            $_SESSION['transfer_user_input'] = $to_user;
            $_SESSION['expand_transfer'] = true;

            header('Location: ' . $base_dir . 'account');
            die();
        }
    } else {

        $_SESSION['captcha_failed_transfer'] = true;
        $_SESSION['transfer_amount_input'] = $amount;
        $_SESSION['transfer_user_input'] = $to_user;
        $_SESSION['expand_transfer'] = true;

        header('Location: ' . $base_dir . 'account');
        die();
    }
} else {
    header("Location: " . $base_dir . "lost");
    die();
}
<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/10/17
 * Time: 4:36 PM
 *
 */
session_start();

require_once '/home/luckiestguyever/PhpstormProjects/bitcoinLottery/vendor/autoload.php';

include '../globals.php';
include '../function.php';
include '../inc/login_checker.php';


$withdraw_address = $_POST['withdraw_address'];
$amount = $_POST['withdraw_amount'];

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
        if (!empty($amount) && !empty($withdraw_address)) {
            if (ctype_digit($amount)) {
                if ($amount <= 200) {
                    $_SESSION['withdraw_amount_input'] = $amount;
                    $_SESSION['withdraw_address_input'] = $withdraw_address;
                    $_SESSION['expand_withdraw'] = true;
                    header("Location: " . $base_dir . "account");
                    die();
                } else {
//        HERE YOU SHOULD VALIDATE THE BITCOIN ADDRESS
                    $driver = new \Nbobtc\Http\Driver\CurlDriver();
                    $driver
                        ->addCurlOption(CURLOPT_VERBOSE, true)
                        ->addCurlOption(CURLOPT_STDERR, '/var/logs/curl.err');

                    $client = new \Nbobtc\Http\Client('http://puppetmaster:vz6qGFsHBv5auSSDhTPWPktVu@localhost:18332');
                    $client->withDriver($driver);

                    $command = new \Nbobtc\Command\Command('validateaddress', $withdraw_address);

                    /** @var \Nbobtc\Http\Message\Response */
                    $response = $client->sendCommand($command);

                    /** @var string */
                    $output = json_decode($response->getBody()->getContents());

                    $valid_address = $output->result->isvalid;
                    if ($valid_address) {


                        $command = new \Nbobtc\Command\Command('getbalance', $username);

                        /** @var \Nbobtc\Http\Message\Response */
                        $response = $client->sendCommand($command);

                        /** @var string */
                        $output = json_decode($response->getBody()->getContents());

                        $balance = $output->result;
                        $amount_in_bitcoin = $amount / 1000000;

                        if ($balance >= $amount_in_bitcoin) {

                            /*******DO THE BITCOIN TRANSACTION HERE*******/

                            $command = new \Nbobtc\Command\Command('sendfrom', array($username, $withdraw_address, $amount_in_bitcoin));

                            /** @var \Nbobtc\Http\Message\Response */
                            $response = $client->sendCommand($command);

                            $output = json_decode($response->getBody()->getContents());

                            $transaction_id = $output->result;

                            if (!empty($transaction_id)) {

                                try {
                                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                                    // set the PDO error mode to exception
                                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


                                    //Selecting user_id for to_user
                                    $stmt = $conn->prepare('INSERT INTO withdrawal(user_id, txid, inserted_on) 
                                    VALUES (:user_id, :txid, CURRENT_TIMESTAMP)');
                                    $stmt->execute(array('user_id' => $user_id, 'txid' => $transaction_id));
                                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                } catch (PDOException $e) {
                                    echo "Connection failed: " . $e->getMessage();
                                }

                            } else {
                                $error = $output->error->message;

                                $_SESSION['withdraw_amount_input'] = $amount;
                                $_SESSION['withdraw_address_input'] = $withdraw_address;
                                $_SESSION['withdraw_blockchain_error'] = $error;
                                $_SESSION['expand_withdraw'] = true;

                                header("Location: " . $base_dir . "account");
                                die();
                            }


                            /**********************************************/
                            $_SESSION['account_management_success'] = 4;
                            header("Location: " . $base_dir . "account");
                            die();
                        } else {

                            $_SESSION['withdraw_amount_input'] = $amount;
                            $_SESSION['withdraw_address_input'] = $withdraw_address;
                            $_SESSION['withdraw_insufficient'] = true;
                            $_SESSION['expand_withdraw'] = true;

                            header("Location: " . $base_dir . "account");
                            die();
                        }
                    } else {
                        $_SESSION['withdraw_amount_input'] = $amount;
                        $_SESSION['withdraw_address_input'] = $withdraw_address;
                        $_SESSION['withdraw_invalid_address'] = true;
                        $_SESSION['expand_withdraw'] = true;

                        header("Location: " . $base_dir . "account");
                        die();
                    }

                }
            } else {
                $_SESSION['withdraw_amount_input'] = $amount;
                $_SESSION['withdraw_address_input'] = $withdraw_address;
                $_SESSION['expand_withdraw'] = true;

                header("Location: " . $base_dir . "account");
                die();
            }
        } else {
            $_SESSION['withdraw_empty_fields'] = true;
            $_SESSION['withdraw_amount_input'] = $amount;
            $_SESSION['withdraw_address_input'] = $withdraw_address;
            $_SESSION['expand_withdraw'] = true;

            header("Location: " . $base_dir . "account");
            die();
        }
    } else {
        $_SESSION['captcha_failed_withdraw'] = true;
        $_SESSION['withdraw_amount_input'] = $amount;
        $_SESSION['withdraw_address_input'] = $withdraw_address;
        $_SESSION['expand_withdraw'] = true;

        header("Location: " . $base_dir . "account");
        die();
    }
} else {
    header("Location: " . $base_dir . "lost");
    die();
}



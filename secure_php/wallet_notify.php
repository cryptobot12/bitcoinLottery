<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 6/4/18
 * Time: 7:24 PM
 */


require_once '/var/www/bitcoinpvp.net/html/vendor/autoload.php';

include '/var/www/html/bitcoinpvp.net/html/globals.php';
$tx = $argv[1];

$driver = new \Nbobtc\Http\Driver\CurlDriver();
$driver
    ->addCurlOption(CURLOPT_VERBOSE, true)
    ->addCurlOption(CURLOPT_STDERR, '/var/logs/curl.err');

$client = new \Nbobtc\Http\Client('http://puppetmaster:vz6qGFsHBv5auSSDhTPWPktVu@localhost:8332');
$client->withDriver($driver);

$command = new \Nbobtc\Command\Command('gettransaction', $tx);

/** @var \Nbobtc\Http\Message\Response */
$response = $client->sendCommand($command);

/** @var string */
$output = json_decode($response->getBody()->getContents());

foreach ($output->result->details as $i) {
    if ($i->category == "receive") {
        if (!empty($i->account)) {

            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                $stmt = $conn->prepare('SELECT user_id FROM user WHERE username = :username');
                $stmt->execute(array('username' => $i->account));

                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $user_id = $result['user_id'];


                //Checking if transaction exists
                $stmt = $conn->prepare('SELECT txid FROM deposit WHERE txid = :tx AND user_id = :user_id');
                $stmt->execute(array('tx' => $tx, 'user_id' => $user_id));

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!empty($result)) {
                    if ($output->result->confirmations > 0) {
                        $stmt = $conn->prepare('UPDATE deposit SET status = TRUE WHERE txid = :tx AND user_id = :user_id');
                        $stmt->execute(array('tx' => $tx, 'user_id' => $user_id));
                        $stmt = $conn->prepare('UPDATE balances SET balance = balance + :amount WHERE username = :username');
                        $stmt->execute(array('amount' => $i->amount * 100000000, 'username' => $i->account));
                    }
                }
                else{


                    $stmt = $conn->prepare('INSERT INTO deposit(user_id, txid, amount, inserted_on, status)  
                    VALUES(:user_id, :txid, :amount, CURRENT_TIMESTAMP, FALSE)');
                    $stmt->execute(array('user_id' => $user_id,'txid' => $tx, 'amount' => $i->amount * 100000000));
                }
            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
        }
    } elseif ($i->category == "send") {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $stmt = $conn->prepare('SELECT user_id FROM user WHERE username = :username');
            $stmt->execute(array('username' => $i->account));

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_id = $result['user_id'];


            //Checking if transaction exists
            $stmt = $conn->prepare('SELECT txid FROM withdrawal WHERE txid = :tx AND user_id = :user_id');
            $stmt->execute(array('tx' => $tx, 'user_id' => $user_id));

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!empty($result)) {
                if ($output->result->confirmations > 0) {
                    $stmt = $conn->prepare('UPDATE withdrawal SET status = TRUE, amount = :amount, fee = :fee WHERE txid = :tx AND user_id = :user_id');
                    $stmt->execute(array('amount' => $i->amount * 100000000, 'fee' => $i->fee * 100000000,'tx' => $tx, 'user_id' => $user_id));

                }
            }
            else{
                $stmt = $conn->prepare('INSERT INTO withdrawal(user_id, txid, fee, amount, inserted_on, status)  
                    VALUES(:user_id, :txid,:fee, :amount, CURRENT_TIMESTAMP, FALSE)');
                $stmt->execute(array('user_id' => $user_id,'txid' => $tx, 'fee' => $i->fee * 100000000, 'amount' => $i->amount * 100000000));

                $subtract = abs($i->fee + $i->amount) * 100000000;
                $stmt = $conn->prepare('UPDATE balances SET balance = balance - :amount WHERE
                                      username = :username');
                $stmt->execute(array('amount' => $subtract, 'username' => $i->account));
            }
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
}


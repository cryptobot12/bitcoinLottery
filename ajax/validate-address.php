<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 5/25/18
 * Time: 8:18 PM
 */

session_start();

require_once '/var/www/html/bitcoinLottery/vendor/autoload.php';

include "../globals.php";
include "../inc/login_checker.php";

$wallet_address = $_POST['wallet_address'];

if ($logged_in) {

    $driver = new \Nbobtc\Http\Driver\CurlDriver();
    $driver
        ->addCurlOption(CURLOPT_VERBOSE, true)
        ->addCurlOption(CURLOPT_STDERR, '/var/logs/curl.err');

    $client = new \Nbobtc\Http\Client('http://puppetmaster:vz6qGFsHBv5auSSDhTPWPktVu@localhost:18332');
    $client->withDriver($driver);

    $command = new \Nbobtc\Command\Command('validateaddress', $wallet_address);

    /** @var \Nbobtc\Http\Message\Response */
    $response = $client->sendCommand($command);

    /** @var string */
    $output = json_decode($response->getBody()->getContents());

    $is_valid = $output->result->isvalid;

    $returnAjax = array('is_valid' => $is_valid);

    $jsonAjax = json_encode($returnAjax);
    echo $jsonAjax;
}
else {
    echo "You need to login first.";
}
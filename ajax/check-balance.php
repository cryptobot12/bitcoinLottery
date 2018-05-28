<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 5/25/18
 * Time: 7:53 PM
 */

session_start();

require_once '/home/luckiestguyever/PhpstormProjects/bitcoinLottery/vendor/autoload.php';

include "../globals.php";
include "../inc/login_checker.php";

if ($logged_in) {

        $driver = new \Nbobtc\Http\Driver\CurlDriver();
        $driver
            ->addCurlOption(CURLOPT_VERBOSE, true)
            ->addCurlOption(CURLOPT_STDERR, '/var/logs/curl.err');

        $client = new \Nbobtc\Http\Client('http://puppetmaster:vz6qGFsHBv5auSSDhTPWPktVu@localhost:18332');
        $client->withDriver($driver);

        $command = new \Nbobtc\Command\Command('getbalance', $username);

        /** @var \Nbobtc\Http\Message\Response */
        $response = $client->sendCommand($command);

        /** @var string */
        $output = json_decode($response->getBody()->getContents());

        $balance = $output->result * 1000000;

        $returnAjax = array('balance' => $balance);
        $jsonAjax = json_encode($returnAjax);
        echo $jsonAjax;
}
else {
    echo "You need to login first.";
}
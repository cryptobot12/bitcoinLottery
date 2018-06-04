<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 5/25/18
 * Time: 7:53 PM
 */

session_start();

require_once '/var/www/bitcoinpvp.net/html/vendor/autoload.php';

include "../globals.php";
include "../inc/login_checker.php";

if ($logged_in) {


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
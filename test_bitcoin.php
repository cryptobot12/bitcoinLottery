<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 5/23/18
 * Time: 8:51 PM
 */

require __DIR__ . '/vendor/autoload.php';

$driver = new \Nbobtc\Http\Driver\CurlDriver();
$driver
    ->addCurlOption(CURLOPT_VERBOSE, true)
    ->addCurlOption(CURLOPT_STDERR, '/var/logs/curl.err');

$client = new \Nbobtc\Http\Client('http://puppetmaster:vz6qGFsHBv5auSSDhTPWPktVu@localhost:18332');
$client->withDriver($driver);

$command = new \Nbobtc\Command\Command('move', array("jackpot", "test123456", 4));

/** @var \Nbobtc\Http\Message\Response */
$response = $client->sendCommand($command);


/** @var string */
$output = json_decode($response->getBody()->getContents());

var_dump($output);

echo "<br><br>";

//foreach ($output->result as $transaction) {
//    var_dump($transaction);
//    echo "<br>";
//}
//

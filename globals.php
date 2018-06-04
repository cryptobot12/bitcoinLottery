<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/24/17
 * Time: 8:52 PM
 */

require_once '/var/www/bitcoinpvp.net/html/vendor/autoload.php';

$servername = "localhost";
$dbname = "lottery";
$dbuser = "puppetmaster";
$dbpass = "7IUG#z@JzzpVFRaT1B2W*1r";
$base_dir = "https://www.bitcoinpvp.net/";
$support_base_dir = "https://www.bitcoinpvp.net/support/";

$driver = new \Nbobtc\Http\Driver\CurlDriver();
$driver
    ->addCurlOption(CURLOPT_VERBOSE, true)
    ->addCurlOption(CURLOPT_STDERR, '/var/logs/curl.err');

$client = new \Nbobtc\Http\Client('http://puppetmaster:vz6qGFsHBv5auSSDhTPWPktVu@localhost:8332');
$client->withDriver($driver);
<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/24/17
 * Time: 9:01 PM
 */
session_start();
$last_url = $_SESSION['url'];
session_destroy();

setcookie('selector', '', time() - 86400, "/");
setcookie('validator', '', time() - 86400, "/");

header("Location: " . $last_url);
die();

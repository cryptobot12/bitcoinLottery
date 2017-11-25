<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/24/17
 * Time: 9:01 PM
 */
session_start();

if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    unset($_SESSION['username']);
    unset($_SESSION['numbers_list']);
    unset($_SESSION['user_id']);
    echo $_SESSION['url'];
    header("Location: " . $_SESSION['url']);
}
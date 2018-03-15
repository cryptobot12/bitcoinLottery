<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 1/25/2018
 * Time: 1:21 PM
 */
session_start();

include "../globals.php";
include "../inc/login_checker.php";

if ($logged_in) {
    //Send email again and do the stuff here

    //Then redirect
    header("Location: ../account.php");
    die();
}
else {
    header("Location: ../index.php");
    die();
}
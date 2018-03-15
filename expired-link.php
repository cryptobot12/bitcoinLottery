<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 1/25/2018
 * Time: 1:49 PM
 */
session_start();

include 'globals.php';
include 'inc/login_checker.php';

include 'inc/header.php';

display_header("BitcoinPVP - Expired Lik", "", "", false, $base_dir, $username, $balance); ?>
<main class="valign-wrapper">
    <div class="container">
        <div class="row centerWrap">
            <div class="centeredDiv">
                <span class="h5Span"><i class="material-icons left">error</i>This link has expired</span>
            </div>
        </div>
    </div>
</main>
<?php include 'inc/footer.php' ?>

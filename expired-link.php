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

$title = "Expired Link - BitcoinPVP";
include 'inc/header.php'; ?>
<main class="valign-wrapper">
    <div class="container">
        <div class="row centerWrap">
            <div class="centeredDiv">
                <span class="h5Span"><i class="material-icons left">error</i>This link has expired</span>
            </div>
        </div>
    </div>
</main>
<!-- Jquery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
<script>
    $(document).ready(function () {
        M.AutoInit();

    });
</script>
<?php include 'inc/footer.php' ?>

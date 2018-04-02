<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 3/9/18
 * Time: 1:15 AM
 */
session_start();

include 'globals.php';
include 'inc/login_checker.php';

$title = "Lost - BitcoinPVP";

include 'inc/header.php'; ?>
<main class="valign-wrapper">
    <div class="container">
        <div class="row centerWrap">
            <div class="centeredDiv">
                <span class="h5Span"><i class="material-icons left">error_outline</i>Page not found</span>
            </div>
        </div>
        <div class="row centerWrap">
            <div class="centeredDiv col l8 offset-l2 m10 offset-m1 s12">
                    <span><b>Did you accidentally get lost?</b><br>
                <i>“The road of excess leads to the palace of wisdom...You never know what is enough until you know what is more than enough.”</i>
                ― William Blake, Proverbs of Hell</span>
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
<?php include 'inc/footer.php'; ?>



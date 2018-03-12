<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 2/26/18
 * Time: 11:07 AM
 */
session_start();

include 'connect.php';
include 'inc/login_checker.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BitcoinPVP</title>
    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>

    <!-- Custom scripts -->

    <!-- Custom style -->
    <link href="css/style.css" rel="stylesheet">

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<header>
    <?php include 'inc/header.php'; ?>
</header>z
<main class="valign-wrapper">
    <div class="container">
        <div class="row centerWrap">
            <div class="centeredDiv">
                <span class="h5Span"><i class="material-icons left">error</i><?php

                    switch ($_SESSION['account_management_success']) {
                        case 1:
                            echo "Email successfully updated.";
                            break;
                        case 2:
                            echo "Password successfully updated.";
                            break;
                        case 3:
                            echo "Your withdrawal is being processed.";
                            break;
                        case 4:
                            echo "Your transfer is being processed.";
                            break;
                        case 5:
                            echo "Your support ticket has been successfully created! Please allow up to 72 hours for a response.";
                            break;
                    }

                    ?></span>
            </div>
        </div>
    </div>
</main>
<?php include 'inc/footer.php';
unset($_SESSION['account_management_success']);
?>



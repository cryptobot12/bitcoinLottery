<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 2/26/18
 * Time: 11:07 AM
 */
session_start();

include 'globals.php';
include 'inc/login_checker.php';

if (empty($_SESSION['account_management_success'])) {
    header("Location: " . $base_dir . "lost");
    die();
}
$title = "Success - BitcoinPVP";

include 'inc/header.php'; ?>
<main class="valign-wrapper">
    <div class="container">
        <div class="row centerWrap">
            <div class="centeredDiv">
                <span class="h5Span"><i class="material-icons left">check</i><?php

                    switch ($_SESSION['account_management_success']) {
                        case 1:
                            echo "Email successfully updated.";
                            break;
                        case 2:
                            echo "Your password has been changed.";
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
<!-- Jquery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
<script>
    $(document).ready(function () {
        M.AutoInit();
    });
</script>
<?php include 'inc/footer.php';
unset($_SESSION['account_management_success']);
?>



<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 3/7/18
 * Time: 9:24 PM
 */
session_start();

include 'globals.php';
include 'inc/login_checker.php';

if (empty($_SESSION['password_reset_token']) || $_SESSION['password_reset_token'] == false) {
    header("Location: " . $base_dir . "lost");
    die();
}

$successfully_resent = !empty($_SESSION['email_sent_again_success']) ? $_SESSION['email_sent_again_success'] : false;
$failed_resent = !empty($_SESSION['too_soon_to_send_email_again']) ? $_SESSION['too_soon_to_send_email_again'] : false;

unset($_SESSION['email_sent_again_success']);
unset($_SESSION['too_soon_to_send_email_again']);

$title = "Password Reset - BitcoinPVP";
include "inc/header.php";

?>
<main class="valign-wrapper">
    <div class="container">
        <div class="row">
            <div class="col l8 m10 s12 offset-l2 offset-m1">
                <?php if ($successfully_resent): ?>
                    <blockquote class="blockquote-green w900">
                        Email successfully sent.
                    </blockquote>
                <?php endif; ?>
                <?php if ($failed_resent): ?>
                    <blockquote class="blockquote-error w900">
                        Wait at least 10 minutes to resend the link.
                    </blockquote>
                <?php endif; ?>
                <div class="card">
                    <div class="card-content">
                        <span class="card-title"><b>Email Confirmation</b></span>
                        <p>A link was sent to your email account. Click on it to reset your password.
                            You might need to check your junk folder.</p>
                    </div>
                    <div class="card-action">
                        <a href="actions/resend-password-reset-email">Resend email</a>
                    </div>
                </div>
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

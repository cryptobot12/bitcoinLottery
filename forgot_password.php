<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 3/7/18
 * Time: 2:51 PM
 */


session_start();

include 'connect.php';
include 'inc/login_checker.php';

if (!empty($_SESSION['captcha_failed'])) {
    $captcha_failed = true;
} else {
    $captcha_failed = false;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BitcoinPVP - Login</title>
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

    <!-- Recaptcha-->
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script>
        function submitForm() {
            $("#password_reset_form").submit();

        }
    </script>

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<header>
    <?php include 'inc/header.php'; ?>
</header>
<main class="valign-wrapper">
    <div class="container">
        <div class="row">
            <div class="col l6 offset-l3 m8 offset-m2 s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title"><b>Recover your account</b></span>
                        <div class="row"></div>
                        <div class="row">
                            <div class="col s10 offset-s1">
                        <span>We can help you reset your password.
                            First, enter your email in the field below.</span>
                            </div>
                        </div>
                        <?php if (!empty($captcha_failed)): ?>
                            <div class="col m10 offset-m1 s12">
                                <blockquote class="blockquote-error w900">reCAPTCHA validation failed
                                </blockquote>
                            </div>
                        <?php endif; ?>
                        <div class="row no-marg-bot">
                            <form id="password_reset_form" class="col m10 s12 offset-m1" action="actions/generate_password_reset_link">
                                <div class="row">
                                    <div class="input-field col s12">
                                        <i class="material-icons prefix">email</i>
                                        <input id="email_input" type="email">
                                        <label for="email_input">Email</label>
                                    </div>
                                    <button id="ticket_button"
                                            class="waves-effect waves-light btn g-recaptcha right amber darken-3"
                                            data-sitekey="6Lf1d0EUAAAAAHlf_-pGuqjxWwBfy-UVkdJt-xLf"
                                            data-callback="submitForm">Next
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'inc/footer.php' ?>
</body>
</html>

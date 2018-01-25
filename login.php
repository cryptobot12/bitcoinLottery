<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/17/2017
 * Time: 7:22 PM
 */

session_start();

include 'connect.php';
include 'inc/login_checker.php';

if (!$logged_in) {
    $login_error = !empty($_SESSION['login_error']) ? $_SESSION['login_error'] : 0;
    unset($_SESSION['login_error']);
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
        function submitTicket() {
            $("#login").submit();

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
            <?php if ($logged_in): ?>
            <div class="centerWrap">
                <div class="centeredDiv">
                <span class="h5Span"><i class="material-icons left">error</i>You are already logged in</span>
                </div>
            </div>
            <?php else: ?>
            <div class="col l6 offset-l3 m8 offset-m2 s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title"><b>Login</b></span>
                        <span>with your BitcoinPVP Account</span>
                        <div class="row"></div>
                        <form id="login" method="post" action="php_actions/loading_login.php">
                            <?php if (!empty($login_error)): ?>
                                    <div class="col m10 offset-m1 s12">
                                        <blockquote class="blockquote-error w900">
                                            <?php
                                            if ($login_error == 1)
                                                echo "reCAPTCHA validation failed";
                                            elseif ($login_error == 2)
                                                echo "User does not exist";
                                            else
                                                echo "Incorrect password";
                                            ?>
                                        </blockquote>
                                    </div>
                            <?php endif; ?>
                            <div class="input-field col m10 offset-m1 s12">
                                <i class="material-icons prefix">account_circle</i>
                                <input id="username" type="text" name="username">
                                <label for="username">Username</label>
                            </div>
                            <div class="input-field col m10 offset-m1 s12">
                                <i class="material-icons prefix">lock</i>
                                <input id="password" type="password" name="password">
                                <label for="password">Password</label>
                            </div>
                            <div class="col col m10 offset-m1 s12">
                                <input type="checkbox" id="remember_me" name="remember_me" value="1"/>
                                <label for="remember_me">Remember me</label>
                            </div>
                            <div class="row">
                                <div class="input-field col m10 offset-m1 s12">
                                    <button id="ticket_button"
                                            class="waves-effect waves-light btn g-recaptcha right amber darken-3"
                                            data-sitekey="6Lf1d0EUAAAAAHlf_-pGuqjxWwBfy-UVkdJt-xLf"
                                            data-callback="submitTicket">Login<i class="material-icons right">send</i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
</main>
<?php include 'inc/footer.php' ?>
</body>
</html>
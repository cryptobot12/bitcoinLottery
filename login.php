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
include 'inc/base-dir.php';

if (!$logged_in) {
    $login_error = !empty($_SESSION['login_error']) ? $_SESSION['login_error'] : 0;
    unset($_SESSION['login_error']);
}

include 'inc/header.php';

$scripts = 'function submitTicket() {
            $("#login").submit();

        }';
display_header("BitcoinPVP - Login", "", $scripts, true, $base_dir, $username, $balance);

?>
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
                        <form id="login" method="post" action="actions/loading-login">
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
                            <div class="row"></div>
                            <div class="row">
                                <div class="col m6 s12 offset-m1">
                                <a href="forgot-password">Forgot password?</a>
                                </div>
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
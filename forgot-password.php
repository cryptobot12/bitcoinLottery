<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 3/7/18
 * Time: 2:51 PM
 */


session_start();

include 'globals.php';
include 'inc/login_checker.php';

$captcha_failed = !empty($_SESSION['captcha_failed']) ? $_SESSION['captcha_failed'] : false;
$email_does_not_exist = !empty($_SESSION['email_does_not_exist']) ? $_SESSION['email_does_not_exist'] : false;
$email_input = !empty($_SESSION['email_input']) ? $_SESSION['email_input'] : '';

unset($_SESSION['captcha_failed']);
unset($_SESSION['email_input']);
unset($_SESSION['email_does_not_exist']);

if ($captcha_failed || $email_does_not_exist)
    $show_error = true;
else
    $show_error = false;

$title = "Password Recovery - BitcoinPVP";
include 'inc/header.php'; ?>
    <main class="valign-wrapper">
        <div class="container">
            <div class="row"></div>
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
                            <?php if ($show_error): ?>
                                <div class="col m10 offset-m1 s12">
                                    <blockquote class="blockquote-error w900">
                                        <ul>
                                            <?php if ($captcha_failed)
                                                echo "<li>reCAPTCHA validation failed.</li>"; ?>
                                            <?php if ($email_does_not_exist)
                                                echo "<li>This email is not recognized.</li>"; ?>
                                        </ul>
                                    </blockquote>
                                </div>
                            <?php endif; ?>
                            <div class="row no-marg-bot">
                                <form id="password_reset_form" method="post" class="col m10 s12 offset-m1"
                                      action="<?php echo $base_dir; ?>actions/generate-password-reset-link">
                                    <div class="row">
                                        <div class="input-field col s12">
                                            <i class="material-icons prefix">email</i>
                                            <input id="email_input" name="email_input" type="email"
                                                   value="<?php echo $email_input; ?>">
                                            <label for="email_input">Email</label>
                                            <span id="email_helper" class="helper-text">myemail@email.com</span>
                                        </div>
                                        <button id="forgot_password_button"
                                                disabled
                                                class="waves-effect waves-light btn g-recaptcha right amber darken-3 disabled"
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
    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
    <script>
        function toggle_button() {
            var email_input = $("#email_input");
            var button = $("#forgot_password_button");

            if (email_input.hasClass('valid')) {
                button.prop('disabled', false);
                button.removeClass('disabled');
            } else {
                button.prop('disabled', true);
                button.addClass('disabled');
            }
        }

        function submitForm() {
            $("#password_reset_form").submit();

        }

        function isEmail(email) {
            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }

        $(function () {
            var email_input_el = $("#email_input");
            var email_helper_el = $("#email_helper");

            email_input_el.on('keyup input', function () {
                email_input_el.removeClass('invalid');
                email_input_el.removeClass('valid');

                toggle_button();
                var email_val = email_input_el.val();

                if (email_val.length > 0) {
                    if (isEmail(email_val)) {
                        email_input_el.addClass('valid');
                        toggle_button();
                    } else {
                        email_input_el.addClass('invalid');
                        email_helper_el.attr('data-error', "Invalid email");
                    }
                }
                else {
                    email_input_el.addClass('invalid');
                    email_helper_el.attr('data-error', "Cannot be empty");
                }

            });
        });

        $(document).ready(function () {
            M.AutoInit();
        });

    </script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
<?php include 'inc/footer.php' ?>
<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/17/2017
 * Time: 5:51 PM
 */

session_start();

$empty_user = !empty($_SESSION['username_empty']) ? $_SESSION['username_empty'] : false;
$empty_email = !empty($_SESSION['email_empty']) ? $_SESSION['email_empty'] : false;
$empty_password = !empty($_SESSION['password_empty']) ? $_SESSION['password_empty'] : false;
$invalid_recaptcha = !empty($_SESSION['captcha_failed']) ? $_SESSION['captcha_failed'] : false;
$user_already_exists = !empty($_SESSION['user_already_exists']) ? $_SESSION['user_already_exists'] : false;
$email_already_exists = !empty($_SESSION['email_already_exists']) ? $_SESSION['email_already_exists'] : false;
$passwords_not_match = !empty($_SESSION['password_not_match']) ? $_SESSION['password_not_match'] : false;
$invalid_username = !empty($_SESSION['invalid_username']) ? $_SESSION['invalid_username'] : false;
$password_length_invalid = !empty($_SESSION['password_length_error']) ? $_SESSION['password_length_error'] : false;
$invalid_email = !empty($_SESSION['invalid_email']) ? $_SESSION['invalid_email'] : false;

$input_username = !empty($_SESSION['input_username']) ? $_SESSION['input_username'] : '';
$input_password = !empty($_SESSION['input_password']) ? $_SESSION['input_password'] : '';
$input_confirm_password = !empty($_SESSION['input_confirm_password']) ? $_SESSION['input_confirm_password'] : '';
$input_email = !empty($_SESSION['input_email']) ? $_SESSION['input_email'] : '';

unset($_SESSION['input_username']);
unset($_SESSION['input_password']);
unset($_SESSION['input_confirm_password']);
unset($_SESSION['input_email']);

unset($_SESSION['user_already_exists']);
unset($_SESSION['email_already_exists']);
unset($_SESSION['password_not_match']);
unset($_SESSION['invalid_username']);
unset($_SESSION['password_length_error']);
unset($_SESSION['invalid_email']);
unset($_SESSION['username_empty']);
unset($_SESSION['email_empty']);
unset($_SESSION['password_empty']);

if ($user_already_exists || $email_already_exists || $passwords_not_match || $invalid_username || $password_length_invalid
    || $invalid_email || $invalid_recaptcha || $empty_email || $empty_password || $empty_user) {
    $show_error = true;
} else {
    $show_error = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BitcoinPVP - Registration</title>
    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>

    <!-- Custom scripts -->
    <script src="js/registration_script.js"></script>
    <!-- Custom style -->
    <link href="css/style.css" rel="stylesheet">

    <!-- Recaptcha-->
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script>
        function register() {
            $("#registration").submit();

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
            <div class="col l8 offset-l2 m10 offset-m1 s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title"><b>Create</b></span>
                        <span>a BitcoinPVP Account</span>
                        <div class="row"></div>
                        <form id="registration" method="post" action="php_actions/loading_registration.php">
                            <?php if ($show_error): ?>
                                <div class="col m10 offset-m1 s12">
                                    <blockquote class="blockquote-error w900">
                                        <ul>
                                            <?php
                                            //Captcha
                                            if ($invalid_recaptcha)
                                                echo "<li>reCAPTCHA validation failed</li>";

                                            //Username
                                            if ($empty_user)
                                                echo "<li>Username is required</li>";
                                            if ($invalid_username)
                                                echo "<li>Invalid username</li>";
                                            if ($user_already_exists)
                                                echo "<li>Username is already taken</li>";

                                            //Password
                                            if ($empty_password)
                                                echo "<li>Password is required</li>";
                                            if ($passwords_not_match)
                                                echo "<li>Passwords do not match</li>";
                                            if ($password_length_invalid)
                                                echo "<li>Password must be between 8 and 72 characters long.</li>";

                                            //Email
                                            if ($empty_email)
                                                echo "<li>Email is required</li>";
                                            if ($email_already_exists)
                                                echo "<li>Email is already taken</li>";
                                            if ($invalid_email)
                                                echo "<li>Invalid email</li>";

                                            ?>
                                        </ul>
                                    </blockquote>
                                </div>
                            <?php endif; ?>
                            <div class="input-field col m10 offset-m1 s12">
                                <i class="material-icons prefix">account_circle</i>
                                <input type="text" name="username" id="username" value="<?php echo $input_username; ?>">
                                <label id="username_label" for="username" data-error="" data-success="">Username</label>
                            </div>
                            <div class="input-field col m10 offset-m1 s12">
                                <i class="material-icons prefix">lock_outline</i>
                                <input id="password" type="password" name="password"
                                       value="<?php echo $input_password; ?>"
                                       placeholder="At least 8 characters long">
                                <label id="password_label" for="password" data-error="" data-success="">Password</label>
                            </div>
                            <div class="input-field col m10 offset-m1 s12">
                                <i class="material-icons prefix">lock</i>
                                <input id="confirm_password" type="password" name="confirm_password"
                                       value="<?php echo $input_confirm_password; ?>">
                                <label id="confirm_password_label" for="confirm_password" data-error="" data-success="">Confirm
                                    Password</label>
                            </div>
                            <div class="input-field col m10 offset-m1 s12">
                                <i class="material-icons prefix">email</i>
                                <input id="email" type="text" name="email" placeholder="Make sure it is correct."
                                       value="<?php echo $input_email; ?>">
                                <label id="email_label" for="email" data-error="" data-success="">Email </label>
                            </div>
                            <div class="row">
                                <div class="input-field col m10 offset-m1 s12">
                                    <button id="register_button"
                                            class="waves-effect waves-light btn g-recaptcha right amber darken-1 disabled"
                                            data-sitekey="6Lf1d0EUAAAAAHlf_-pGuqjxWwBfy-UVkdJt-xLf"
                                            data-callback="register" disabled>Register
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'inc/footer.php' ?>
</body>
</html>



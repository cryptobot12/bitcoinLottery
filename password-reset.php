<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 3/7/18
 * Time: 9:12 PM
 */
session_start();

include 'globals.php';
include 'inc/login_checker.php';
include 'inc/';

$selector = $_GET['sel'];
$validator = $_GET['val'];

if (!empty($selector) && !empty($validator)) {

    unset($_SESSION['password_reset_token']);
    unset($_SESSION['password_reset_user_id']);

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $stmt = $conn->prepare('SELECT user_id, expires, validator, current_timestamp AS now 
FROM password_reset WHERE hashed_user_id = :selector');
        $stmt->execute(array('selector' => $selector));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $reset_user_id = $result['user_id'];
        $expires = $result['expires'];
        $now = $result['now'];
        $hashed_validator = $result['validator'];

        if (!empty($reset_user_id) && password_verify($validator, $hashed_validator)) {
            if (strtotime($expires) < strtotime($now)) {
                header("Location: " . $base_dir . "expired-link");
                die();
            }
        } else {
            header("Location: " . $base_dir . "lost");
            die();
        }


    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }


} else {
    header("Location: " . $base_dir . "lost");
    die();
}

$new_password_input = !empty($_SESSION['new_password_input']) ? $_SESSION['new_password_input'] : "";
$confirm_new_password_input = !empty($_SESSION['confirm_new_password_input']) ? $_SESSION['confirm_new_password_input'] : "";
$short_password = !empty($_SESSION['short_password']) ? $_SESSION['short_password'] : false;
$unmatched_password = !empty($_SESSION['unmatched_password']) ? $_SESSION['unmatched_password'] : false;

$new_password_class = $short_password ? "invalid" : "";
$confirm_new_password_class = $unmatched_password ? "invalid" : "";

unset($_SESSION['new_password_input']);
unset($_SESSION['confirm_new_password_input']);
unset($_SESSION['short_password']);
unset($_SESSION['unmatched_password']);

$title = "Password Reset - BitcoinPVP";
include 'inc/header.php'; ?>
<main class="valign-wrapper">
    <div class="container">
        <div class="row"></div>
        <div class="row">
            <div class="col l6 offset-l3 m8 offset-m2 s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title"><b>Password Reset</b></span>
                        <div class="row"></div>
                        <form id="login" method="post" action="<?php echo $base_dir; ?>actions/change-password">
                            <div class="col m10 offset-m1 s12">
                                <blockquote class="blockquote-green w900">
                                    Your new password must be at least 8 characters long. We
                                    encourage
                                    you
                                    to use a combination of symbols, numbers and letters for
                                    your
                                    new
                                    password in order to protect your account.
                                </blockquote>
                            </div>
                            <div class="input-field col m10 offset-m1 s12">
                                <i class="material-icons prefix">lock_outline</i>
                                <input id="new_password" type="password" name="new_password"
                                       class="<?php echo $new_password_class; ?>"
                                       value="<?php echo $new_password_input; ?>">
                                <label for="new_password">New
                                    Password</label>
                                <span class="helper-text" data-error="Your password must be at least 8 characters long">
                                    At leas 8 characters long.
                                </span>
                            </div>
                            <div class="input-field col m10 offset-m1 s12">
                                <i class="material-icons prefix">lock</i>
                                    <input id="confirm_new_password" type="password" name="confirm_new_password"
                                           class="<?php echo $confirm_new_password_class;?>"
                                    value="<?php echo $confirm_new_password_input; ?>">
                                <label for="confirm_new_password" data-error="Passwords do not match">Confirm
                                    New Password</label>
                                <span class="helper-text" data-error="Passwords do not match"></span>
                            </div>
                            <div class="row">
                                <div class="input-field col m10 offset-m1 s12">
                                    <button id="password_reset_button" type="submit" disabled
                                            class="waves-effect waves-light btn right amber darken-3 disabled">Reset
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" value="<?php echo $selector; ?>" name="s">
                            <input type="hidden" value="<?php echo $validator; ?>" name="v">
                        </form>
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
<script src="<?php echo $base_dir; ?>js/password-reset.js"></script>
<script>
    $(document).ready(function () {
        M.AutoInit();
    });
</script>
<?php include 'inc/footer.php' ?>


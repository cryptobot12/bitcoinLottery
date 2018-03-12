<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 3/7/18
 * Time: 9:12 PM
 */
session_start();

include 'connect.php';
include 'inc/login_checker.php';
include 'inc/base-dir.php';

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

        $stmt = $conn->prepare('SELECT user_id, expires, current_timestamp AS now FROM password_reset WHERE hashed_user_id = :selector
AND validator = :validator');
        $stmt->execute(array('selector' => $selector, 'validator' => $validator));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $reset_user_id = $result['user_id'];
        $expires = $result['expires'];
        $now = $result['now'];

        if (!empty($reset_user_id)) {
            if (strtotime($expires) < strtotime($now)) {
                header("Location: " . $base_dir . "expired-link");
                die();
            }
        } else {
            header("Location: " . $base_dir . "expired-link");
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

if ($short_password)
    $new_password_de = "Your password must be at least 8 characters long";
else
    $new_password_de = "";

if ($unmatched_password)
    $confirm_new_password_de = "Passwords do not match";
else
    $confirm_new_password_de = "";

unset($_SESSION['new_password_input']);
unset($_SESSION['confirm_new_password_input']);
unset($_SESSION['short_password']);
unset($_SESSION['unmatched_password']);

include 'inc/header.php';

display_header("Password Reset - BitcoinPVP", "", "", false, $base_dir, $username, $balance); ?>
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
                                <?php if (empty($new_password_de)): ?>
                                    <input id="new_password" type="password" name="new_password">
                                <?php else: ?>
                                    <input id="new_password" type="password" name="new_password" class="invalid">
                                <?php endif; ?>
                                <label for="new_password" data-error="<?php echo $new_password_de; ?>">New
                                    Password</label>
                            </div>
                            <div class="input-field col m10 offset-m1 s12">
                                <i class="material-icons prefix">lock</i>
                                <?php if (empty($confirm_new_password_de)): ?>
                                    <input id="confirm_new_password" type="password" name="confirm_new_password">
                                <?php else: ?>
                                    <input id="confirm_new_password" type="password" name="confirm_new_password"
                                           class="invalid">
                                <?php endif; ?>
                                <label for="confirm_new_password" data-error="<?php echo $new_password_de; ?>">Confirm
                                    New Password</label>
                            </div>
                            <div class="row">
                                <div class="input-field col m10 offset-m1 s12">
                                    <button type="submit" id="ticket_button"
                                            class="waves-effect waves-light btn right amber darken-3">Reset
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
<script src=\"https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js\"></script>";
<?php include 'inc/footer.php' ?>


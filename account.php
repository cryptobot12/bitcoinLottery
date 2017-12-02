<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/24/17
 * Time: 11:12 PM
 */
session_start();

include "function.php";
include "connect.php";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    //Getting email
    $stmt = $conn->prepare('SELECT email FROM user WHERE user_id = :user_id');
    $stmt->execute(array('user_id' => $_SESSION['user_id']));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $email = $result['email'];

    //Selecting code
    $stmt = $conn->prepare('SELECT code, code_expires FROM user WHERE user_id = :user_id');
    $stmt->execute(array('user_id' => $_SESSION['user_id']));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $code_expires = $result['code_expires'];

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$email = hide_mail($email);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bitcoin</title>
    <!--    Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
    <script src="js/autobahn.js"></script>
    <script src="js/account_script.js"></script>

    <link href="css/style.css" rel="stylesheet">

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<header>
    <?php include "header.php" ?>
</header>
<main class="<?php
if (!(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])))
    echo 'valign-wrapper'; ?>">
    <div class="container">
        <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
            <div class="row top-buffer-30">
                <div class="col l10 offset-l1 m10 offset-m1 s12">
                    <ul class="collapsible popout" data-collapsible="accordion">
                        <li>
                            <div class="collapsible-header active"><i class="material-icons">email</i>Update email</div>
                            <div class="collapsible-body">
                                <div class="row">
                                    <?php if ((empty($result['code'])) || (time() > strtotime($code_expires))): ?>
                                        <form class="col s8 offset-s2" action="php_actions/update_email_code.php"
                                              method="post">
                                            <div class="row">
                                                <div class="input-field col s12">
                                                    <input disabled name="old-email" id="old-email" type="email"
                                                           value="<?php echo $email; ?>">
                                                    <label id="oldEmailLabel" for="old-email"
                                                           data-error="Invalid email">Current
                                                        Email</label>
                                                </div>
                                                <div class="input-field col s12">
                                                    <input name="new-email" id="new-email" type="email" class="">
                                                    <label id="newEmailLabel" for="new-email"
                                                           data-error="Invalid email">New
                                                        Email
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                </div>
                                                <div class="input-field col s12">
                                                    <input name="confirm-email" id="confirm-email" type="email"
                                                           class="">
                                                    <label id="confirmEmailLabel" for="confirm-email"
                                                           data-error="Invalid email">Confirm New Email
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <button type="submit" id="updateEmailButton"
                                                        class="waves-effect waves-light btn right disabled">Update Email
                                                </button>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                            <form class="col s8 offset-s2"
                                                  action="php_actions/update_email.php"
                                                  method="post">
                                                <div class="row">
                                                    <div class="input-field col s12">
                                                        <input disabled name="old-email" id="old-email" type="email"
                                                               value="<?php echo $email; ?>">
                                                        <label id="oldEmailLabel" for="old-email"
                                                               data-error="Invalid email">Current
                                                            Email</label>
                                                    </div>
                                                    <div class="col s12">
                                                        <p>A code was sent to your current email. Insert the code to
                                                            update your email.</p><br>
                                                    </div>
                                                    <div class="input-field col s2 offset-s5">
                                                        <input name="code" id="code" type="text"
                                                               class="upperCaseInput"
                                                               maxlength="4">
                                                        <label id="codeLabel" for="code">CODE
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                    </div>
                                                    <?php if (isset($_SESSION['incorrect_code']) && !empty($_SESSION['incorrect_code']) &&
                                                    $_SESSION == true):?>
                                                    <div class="col s2">
                                                    <p class="input-alert">* Incorrect code</p>
                                                    </div>
                                                    <?php unset($_SESSION['incorrect_code']); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="row">
                                                    <button type="submit" id="updateEmailCodeButton"
                                                            class="waves-effect waves-light btn right disabled">
                                                        Update Email
                                                    </button>
                                                </div>
                                            </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="collapsible-header active"><i class="material-icons">lock</i>Update password
                            </div>
                            <div class="collapsible-body"><span>Lorem ipsum dolor sit amet.</span></div>
                        </li>
                        <li>
                            <div class="collapsible-header"><i class="material-icons">monetization_on</i>Deposit</div>
                            <div class="collapsible-body"><span>Lorem ipsum dolor sit amet.</span></div>
                        </li>
                        <li>
                            <div class="collapsible-header"><i class="material-icons">local_atm</i>Withdraw</div>
                            <div class="collapsible-body"><span>Lorem ipsum dolor sit amet.</span></div>
                        </li>
                        <li>
                            <div class="collapsible-header"><i class="material-icons">swap_horiz</i>Transfer</div>
                            <div class="collapsible-body"><span>Lorem ipsum dolor sit amet.</span></div>
                        </li>
                        <li>
                            <div class="collapsible-header"><i class="material-icons">live_help</i>Support</div>
                            <div class="collapsible-body"><span>Lorem ipsum dolor sit amet.</span></div>
                        </li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <h3 class="center-align"><i class="medium material-icons vmid">error</i> You must be logged in.</h3>
        <?php endif; ?>
    </div>
</main>
<?php include "footer.php"; ?>
</body>

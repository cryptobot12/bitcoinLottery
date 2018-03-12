<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 2/26/18
 * Time: 10:35 AM
 */?>
<!--EMAIL UPDATE CODE-->
<?php if (1 == 0): ?>
    <form class=""
          action="actions/update_email.php"
          method="post">
        <div class="row">
            <div class="input-field col s12">
                <input disabled name="old-email" id="old-email"
                       type="email"
                       value="<?php echo $email; ?>">
                <label id="oldEmailLabel" for="old-email"
                       data-error="Invalid email">Current
                    Email</label>
            </div>
            <div class="col s12">
                <blockquote class="blockquote-green w900">A code was
                    sent to
                    your current
                    email.
                    Type the code to
                    update your email.
                </blockquote>
                <br>
            </div>
            <div class="input-field col s4 offset-s4">
                <?php if (isset($_SESSION['incorrect_code']) && !empty($_SESSION['incorrect_code']) &&
                    $_SESSION == true): ?>
                    <input name="code" id="code" type="text"
                           class="upperCaseInput invalid"
                           maxlength="4"
                           value="<?php echo $_SESSION['input_code']; ?>">
                    <label id="codeLabel" for="code"
                           data-error="Incorrect code">CODE
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                    <?php unset($_SESSION['incorrect_code']);
                    ?>
                <?php else: ?>
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
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <button type="submit" id="updateEmailCodeButton"
                    class="waves-effect waves-light btn right disabled">
                Update Email
            </button>
        </div>
    </form>
<?php endif; ?>
<!--RESPONSIVE CARDS-->



    <!-- Game history card (medium and down only) -->
    <div class="card z-depth-1 hide-on-large-only">
        <div class="card-content">
            <span class="card-title"><b>Game history</b></span>
            <table id="game_history_table_small" class="bordered">
                <thead>
                <tr>
                    <th>Game #</th>
                    <th>Jackpot</th>
                    <th>Number</th>
                    <th>Time</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($game_history_table as $item): ?>
                    <tr>
                        <td>
                            <a href="game_info.php?game_id=<?php echo $item["game_id"] ?>"
                               target="_blank"><?php echo $item["game_id"] ?></a>
                        </td>
                        <td><?php echo $item['amount'] / 100; ?> bits</td>
                        <td>
                            <div class='chip'><?php echo $item['winner_number']; ?></div>
                        </td>
                        <td><?php echo $item['time']; ?></td>
                    </tr>

                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!--            Play card   (small only)-->
    <div class="row hide-on-med-and-up">
        <div class="card z-depth-1">
            <div class="card-content">
                <?php if ($logged_in): ?>
                    <div class="row">
                        <ul class="tabs">
                            <li class="tab col s4 green-text"><a href="#textarea_div_small">Text</a></li>
                            <li class="tab col s4"><a href="#random_div_small">Random</a></li>
                            <li class="tab col s4"><a href="#sequence_div_small">Sequence</a></li>
                        </ul>

                        <!--       Text Area Input          -->
                        <div id="textarea_div_small" class="col s12">
                            <blockquote class="blockquote-green w900">
                                Each number costs 100 bits.
                                Only 25 numbers allowed. Numbers
                                must be
                                between 1 and 50000.
                            </blockquote>
                            <div class="input-field col s12">
                                <label for="field"></label>
                                <textarea id="numbers_textarea_small" class="materialize-textarea"
                                          placeholder="Type your numbers separated by spaces."></textarea>
                                <label for="numbers_textarea_small"></label>
                            </div>
                            <p class="center-align">
                                <a class="waves-effect waves-light btn disabled modal-trigger"
                                   id="textarea_button_small"
                                   href="#confirm_numbers_modal_small">Buy</a>
                            </p>
                        </div>

                        <!--   Random Input    -->
                        <div id="random_div_small" class="col s12">
                            <blockquote class="blockquote-green w900">
                                Each number costs 50 bits.
                                Only 200 numbers allowed. Numbers
                                must be
                                between 1 and 50000.
                            </blockquote>
                            <div class="row top-buffer-15">
                                <div class="input-field col s4">
                                    <input placeholder="Start" id="start_random_small" type="number"
                                           class=""
                                           value="1">
                                    <label for="start_random_small">Start range</label>
                                </div>
                                <div class="input-field col s4">
                                    <input placeholder="End" id="end_random_small" type="number" class=""
                                           value="200">
                                    <label for="end_random_small">End range</label>
                                </div>
                                <div class="input-field col s4">
                                    <input placeholder="How many numbers?" id="how_many_numbers_small"
                                           type="number"
                                           class="" value="20">
                                    <label for="how_many_numbers_small">How many numbers?</label>
                                </div>
                            </div>
                            <p class="center-align">
                                <a class="waves-effect waves-light btn" id="random_button_small">Bet</a>
                            </p>
                        </div>

                        <!--     Sequence Input                       -->
                        <div id="sequence_div_small" class="col s12">
                            <blockquote class="blockquote-green w900">
                                Each number costs 50 bits.
                                Only 200 numbers allowed. Numbers
                                must be
                                between 1 and 50000.
                            </blockquote>
                            <div class="row top-buffer-15">
                                <div class="input-field col s4">
                                    <input placeholder="Start number" id="start_sequence_small"
                                           type="number"
                                           class=""
                                           value="1">
                                    <label for="start_sequence_small">Start range</label>
                                </div>
                                <div class="input-field col s4">
                                    <input placeholder="End number" id="end_sequence_small" type="number"
                                           class=""
                                           value="200">
                                    <label for="end_sequence_small">End range</label>
                                </div>
                            </div>
                            <p class="center-align">
                                <a class="waves-effect waves-light btn" id="sequence_button_small">Bet</a>
                            </p>
                        </div>

                        <!-- Modal Structure -->
                        <div id="confirm_numbers_modal_small" class="modal">
                            <div class="modal-content">
                                <h4>Check your numbers<span class="subText" id="count_numbers_confirm_med">&nbsp;&nbsp;&nbsp;&nbsp;0 numbers (100 bits)</span><br>
                                    <span class="balance-alert hidden" id="insufficient_balance_med">&nbsp;&nbsp;Insufficient balance</span>
                                </h4>
                                <div id="confirmation_numbers_small"></div>
                            </div>
                            <div class="modal-footer">
                                <a id="play_button_small"
                                   class="modal-action modal-close waves-effect waves-green btn-flat">Confirm</a>
                                <a href="#!"
                                   class="modal-action modal-close waves-effect waves-green btn-flat">Cancel</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="center-align"><a class="waves-effect waves-light btn" href="login.php">Login
                            to
                            play</a><br>
                        <a href="registration.php">or register</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php if ($logged_in): ?>
    <!--            Numbers card  -->
    <div id="numbers_card_small"
         class="row scale-transition <?php echo $scale_status; ?> hide-on-med-and-up">
        <div class="card z-depth-1">
            <div class="card-content">
                            <span id="count_numbers_small"
                                  class="card-title"><b><?php echo $numbers_title; ?></b></span>
                <div id="numbers_list_small">
                    <?php
                    foreach ($numbers_list_result as $item) {
                        echo '<div class="chip small-chip">' . $item['number_id'] . '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!--GOOGLE RECAPTCHA-->
data-sitekey="6Lf1d0EUAAAAAHlf_-pGuqjxWwBfy-UVkdJt-xLf"
data-callback="submitTicket"

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
                header("Location: /bitcoinLottery/expired-link");
                die();
            }
        } else {
            header("Location: /bitcoinLottery/expired-link");
            die();
        }


    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }


} else {
//    header("Location: /bitcoinLottery/lost");
//    die();
}

include 'inc/header.php';

display_header("Password Reset - BitcoinPVP", "", "", false, $base_dir, $username, $balance);?>
<main class="valign-wrapper">
    <div class="container">
        <div class="row"></div>
        <div class="row">
            <div class="col l6 offset-l3 m8 offset-m2 s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title"><b>Password Reset</b></span>
                        <div class="row"></div>
                        <form id="login" method="post" action="actions/loading-login.php">
                            <div class="col m10 offset-m1 s12">
                                <!--                                        <blockquote class="blockquote-green w900">-->
                                Your new password must be at least 8 characters long. We
                                encourage
                                you
                                to use a combination of symbols, numbers and letters for
                                your
                                new
                                password in order to protect your account.
                                <!--                                        </blockquote>-->
                            </div>
                            <div class="input-field col m10 offset-m1 s12">
                                <i class="material-icons prefix">lock_outline</i>
                                <input id="new_password" type="password" name="new_password">
                                <label for="new_password">New PasswordARE YOU FUCKING STUPID</label>
                            </div>
                            <div class="input-field col m10 offset-m1 s12">
                                <i class="material-icons prefix">lock</i>
                                <input id="confirm_new_password" type="password" name="confirm_new_password">
                                <label for="confirm_new_password">Confirm New Password</label>
                            </div>
                            <div class="row">
                                <div class="input-field col m10 offset-m1 s12">
                                    <button type="submit" id="ticket_button"
                                            class="waves-effect waves-light btn right amber darken-3">Reset
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


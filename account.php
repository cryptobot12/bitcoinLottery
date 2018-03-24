<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/24/17
 * Time: 11:12 PM
 */
session_start();

include "function.php";
include "globals.php";
include "inc/login_checker.php";

$rowPerPage = 7;

if ($logged_in) {

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        //Check if user is enabled
        $stmt = $conn->prepare('SELECT enabled FROM user WHERE user_id = :user_id');
        $stmt->execute(array('user_id' => $user_id));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $is_user_enabled = $result['enabled'];

        if ($is_user_enabled) {

            //Getting email
            $stmt = $conn->prepare('SELECT bit_address, email FROM user WHERE user_id = :user_id');
            $stmt->execute(array('user_id' => $user_id));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $email = $result['email'];
            $bit_address = $result['bit_address'];

            //Selecting code
            $stmt = $conn->prepare('SELECT COUNT(user_id) AS email_update_requests FROM email_update WHERE user_id = :user_id
            AND CURRENT_TIMESTAMP < expires');
            $stmt->execute(array('user_id' => $user_id));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $email_update_requests = $result['email_update_requests'];

            //Selecting current time
            $stmt = $conn->prepare('SELECT NOW()');
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_time = $result['NOW()'];

            //Deposits pageCount
            $stmt = $conn->prepare('SELECT COUNT(hash) AS the_count FROM deposit WHERE user_id = :user_id');
            $stmt->execute(array('user_id' => $user_id));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $depositRowCount = $result['the_count'];
            $pageCount = ceil($depositRowCount / $rowPerPage);

            if (!empty($_GET['p'])) {
                $page = htmlspecialchars($_GET['p']);
                filterOnlyNumber($page, 1, $pageCount, 1);
                $page_deposit_parameter = $page;
            } else {
                $page = 1;
                $page_deposit_parameter = 0;
            }

            //Withdraws pageCount
            $stmt = $conn->prepare('SELECT COUNT(hash) AS the_count FROM withdrawal WHERE user_id = :user_id');
            $stmt->execute(array('user_id' => $user_id));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $withdrawRowCount = $result['the_count']; //Number of pages withdraw
            $pageWithdrawCount = ceil($withdrawRowCount / $rowPerPage); //Number of pages

            if (!empty($_GET['pw'])) {
                $pageWithdraw = htmlspecialchars($_GET['pw']);
                filterOnlyNumber($pageWithdraw, 1, $pageWithdrawCount, 1);
                $page_withdraw_parameter = $pageWithdraw;
            } else {
                $pageWithdraw = 1;
                $page_withdraw_parameter = 0;
            }

            //Transfers pageCount
            $stmt = $conn->prepare('SELECT COUNT(transfer_id) AS the_count FROM transfer WHERE user_id = :user_id');
            $stmt->execute(array('user_id' => $user_id));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $transferRowCount = $result['the_count']; //Number of pages transfer
            $pageTransferCount = ceil($transferRowCount / $rowPerPage); //Number of pages

            if (!empty($_GET['pt'])) {
                $pageTransfer = htmlspecialchars($_GET['pt']);
                filterOnlyNumber($pageTransfer, 1, $pageTransferCount, 1);
                $page_transfer_parameter = $pageTransfer;
            } else {
                $pageTransfer = 1;
                $page_transfer_parameter = 0;
            }

            //Selecting deposits
            $stmt = $conn->prepare('SELECT hash, amount, DATE_FORMAT(deposit_date, "%M %D, %Y") AS deposit_date, deposit_date AS dpt FROM deposit WHERE user_id = :user_id
                                      ORDER BY dpt DESC LIMIT :rows OFFSET :the_offset');
            $stmt->execute(array('user_id' => $user_id, 'rows' => $rowPerPage, 'the_offset' => (($page - 1) * $rowPerPage)));
            $rowTableDeposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

            //Selecting withdrawals
            $stmt = $conn->prepare('SELECT hash, amount, DATE_FORMAT(request_date, "%M %D, %Y") AS request_date,
 DATE_FORMAT(completed_on, "%M %D, %Y") AS completed_on, request_date AS rdt FROM withdrawal WHERE user_id = :user_id
                                      ORDER BY rdt DESC LIMIT :rows OFFSET :the_offset');
            $stmt->execute(array('user_id' => $user_id, 'rows' => $rowPerPage, 'the_offset' => (($pageWithdraw - 1) * $rowPerPage)));
            $rowTableWithdraws = $stmt->fetchAll(PDO::FETCH_ASSOC);

            //Selecting transfers
            $stmt = $conn->prepare('SELECT u.username, hash, amount, DATE_FORMAT(request_date, "%M %D, %Y") AS request_date, request_date AS rdt,
 DATE_FORMAT(completed_on, "%M %D, %Y") AS completed_on FROM transfer AS t 
  INNER JOIN user AS u
  ON t.to_user = u.user_id
  WHERE t.user_id = :user_id
                                      ORDER BY rdt DESC LIMIT :rows OFFSET :the_offset');
            $stmt->execute(array('user_id' => $user_id, 'rows' => $rowPerPage, 'the_offset' => (($pageTransfer - 1) * $rowPerPage)));
            $rowTableTransfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            /* Ticket form*/
            $ticket_content_error = (!empty($_SESSION['ticket_content_error']) ? $_SESSION['ticket_content_error'] : 0);


            $ticket_subject_error = (!empty($_SESSION['ticket_subject_error']) ? $_SESSION['ticket_subject_error'] : 0);

            $is_ticket_subject_invalid = !empty($_SESSION['ticket_subject_error']) ? 'invalid' : 'valid';

            if ($ticket_subject_error == 1)
                $ticket_subject_data_error = 'Subject is too long';
            else
                $ticket_subject_data_error = '';

            unset($_SESSION['ticket_subject_error']);
            unset($_SESSION['ticket_content_error']);


//Hiding part of email for security purposes
            $email = hide_mail($email);

            $error_update_email = !empty($_SESSION['expand_email']) ? $_SESSION['expand_email'] : false;
            $email_update_captcha_failed = !empty($_SESSION['captcha_failed_email']) ? $_SESSION['captcha_failed_email'] : false;
            $expand_email_collapsible = $email_update_requests || $error_update_email;

            unset($_SESSION['expand_email']);
            unset($_SESSION['captcha_failed_email']);

            $new_email_input = !empty($_SESSION['new-email']) ? $_SESSION['new-email'] : "";
            $confirm_email_input = !empty($_SESSION['confirm-email']) ? $_SESSION['confirm-email'] : "";

            unset($_SESSION['new-email']);
            unset($_SESSION['confirm-email']);

            $expand_password_collapsible = !empty($_SESSION['expand_password']) ? $_SESSION['expand_password'] : false;

            if (!empty($_SESSION['captcha_failed_password']) && $_SESSION['captcha_failed_password'])
                $password_error_message = "reCAPTCHA validation failed.";
            elseif (!empty($_SESSION['incorrect_password']) && $_SESSION['incorrect_password'])
                $password_error_message = "Incorrect password";
            elseif (!empty($_SESSION['diff_pass']) && $_SESSION['diff_pass'])
                $password_error_message = "New password must be different from current password";
            else
                $password_error_message = "";

            unset($_SESSION['expand_password']);
            unset($_SESSION['captcha_failed_password']);
            unset($_SESSION['incorrect_password']);
            unset($_SESSION['diff_pass']);

            $new_password_input = !empty($_SESSION['new_password']) ? $_SESSION['new_password'] : "";
            $confirm_new_password_input = !empty($_SESSION['confirm_new_password']) ? $_SESSION['confirm_new_password'] : "";

            unset($_SESSION['new_password']);
            unset($_SESSION['confirm_new_password']);


            /*WITHDRAW STUFF*/

            $withdraw_address_input = !empty($_SESSION['withdraw_address_input']) ? $_SESSION['withdraw_address_input'] : "";
            $withdraw_amount_input = !empty($_SESSION['withdraw_amount_input']) ? $_SESSION['withdraw_amount_input'] : "";

            unset($_SESSION['withdraw_address_input']);
            unset($_SESSION['withdraw_amount_input']);

            if (!empty($_SESSION['captcha_failed_withdraw']) && $_SESSION['captcha_failed_withdraw'])
                $withdraw_error_message = "reCAPTCHA validation failed.";
            elseif (!empty($_SESSION['withdraw_invalid_address']) && $_SESSION['withdraw_invalid_address'])
                $withdraw_error_message = "Invalid Bitcoin address.";
            elseif (!empty($_SESSION['withdraw_insufficient']) && $_SESSION['withdraw_insufficient'])
                $withdraw_error_message = "You do not have enough bits to do this transaction.";
            else
                $withdraw_error_message = "";

            unset($_SESSION['captcha_failed_withdraw']);
            unset($_SESSION['withdraw_invalid_address']);
            unset($_SESSION['withdraw_insufficient']);
        }

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

}
$title = "Account - BitcoinPVP";

include "inc/header.php";
?>
    <main class="valign-wrapper">
        <div class="container">
            <?php if ($logged_in): ?>
                <div class="row"></div>
                <div class="row">
                    <div class="col l10 offset-l1 m10 offset-m1 s12">
                        <?php if ($is_user_enabled): ?>
                            <?php if (!empty($_SESSION['account_management_success'])) : ?>
                                <div class="row centerWrap">
                                    <div class="centeredDiv">
                                        <blockquote class="blockquote-green w900">
                                            <?php

                                            switch ($_SESSION['account_management_success']) {
                                                case 1:
                                                    echo "Email successfully updated.";
                                                    break;
                                                case 2:
                                                    echo "Password successfully updated.";
                                                    break;
                                                case 3:
                                                    echo "Your withdrawal is being processed.";
                                                    break;
                                                case 4:
                                                    echo "Your transfer is being processed.";
                                                    break;
                                                case 5:
                                                    echo "Your support ticket has been successfully created! Please allow up to 72 hours for a response.";
                                                    break;
                                            }

                                            ?>
                                        </blockquote>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="row"></div>
                            <ul class="collapsible popout" data-collapsible="expandable">
                                <li>
                                    <div class="collapsible-header <?php if ($expand_email_collapsible) echo "active"; ?>">
                                        <i class="material-icons">email</i>Update email
                                    </div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col l8 offset-l2 m10 offset-m1 s12">
                                                <blockquote class="blockquote-green w900">
                                                    <?php if ($email_update_requests == 0): ?>
                                                        This email account is used to recover your password and to keep
                                                        you
                                                        updated about changes in your account.
                                                    <?php else: ?>
                                                        An email update confirmation link was sent to your current email.
                                                        Please check your email before sending a new request.
                                                    <?php endif; ?>
                                                </blockquote>
                                                <?php if ($email_update_captcha_failed == true) : ?>
                                                    <div class="col s12">
                                                        <blockquote class="blockquote-error w900">
                                                            reCAPTCHA validation failed
                                                        </blockquote>
                                                    </div>
                                                <?php endif; ?>
                                                <form id="email_update_form" class=""
                                                      action="<?php echo $base_dir; ?>actions/update-email-code"
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
                                                        <div class="input-field col s12">
                                                            <i class="material-icons prefix">mail_outline</i>
                                                            <input name="new-email" id="new-email" type="email"
                                                                   class="" value="<?php echo $new_email_input; ?>">
                                                            <label id="newEmailLabel" for="new-email"
                                                                   data-error="" data-success="Email is available">New
                                                                Email</label>
                                                        </div>
                                                        <div class="input-field col s12">
                                                            <i class="material-icons prefix">mail</i>
                                                            <input name="confirm-email" id="confirm-email"
                                                                   type="email"
                                                                   class="" value="<?php echo $confirm_email_input; ?>">
                                                            <label id="confirmEmailLabel" for="confirm-email"
                                                                   data-error="Emails do not match">Confirm New
                                                                Email</label>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <button id="updateEmailButton" disabled
                                                                class="g-recaptcha waves-effect waves-light btn right disabled
                                                                amber darken-3">
                                                            Update Email
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="collapsible-header <?php if ($expand_password_collapsible) echo "active"; ?>">
                                        <i class="material-icons">lock</i>Update password
                                    </div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col l8 offset-l2 m10 offset-m1 s12">
                                                <form id="update_password_form"
                                                      action="<?php echo $base_dir; ?>actions/update-password"
                                                      method="post">
                                                    <div class="row">
                                                        <blockquote class="blockquote-green w900">
                                                            Your new password must be at least 8 characters long. We
                                                            encourage
                                                            you
                                                            to use a combination of symbols, numbers and letters for
                                                            your
                                                            new
                                                            password in order to protect your account.
                                                        </blockquote>
                                                        <?php if ($password_error_message != "") : ?>
                                                            <div class="col s12">
                                                                <blockquote class="blockquote-error w900">
                                                                    <?php echo $password_error_message; ?>
                                                                </blockquote>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="input-field col s12">
                                                            <i class="material-icons prefix">lock_outline</i>
                                                            <input name="current_password" id="current_password"
                                                                   type="password">
                                                            <label id="current_password-label" for="current_password">Current
                                                                Password</label>
                                                        </div>
                                                        <div class="input-field col s12">
                                                            <i class="material-icons prefix">lock</i>
                                                            <input name="new_password" id="new_password" type="password"
                                                                   value="<?php echo $new_password_input; ?>">
                                                            <label id="new_password-label" for="new_password"
                                                                   data-error="Password must be at least 8 characters long">New
                                                                Password</label>
                                                        </div>
                                                        <div class="input-field col s12">
                                                            <i class="material-icons prefix">enhanced_encryption</i>
                                                            <input name="confirm_new_password" id="confirm_new_password"
                                                                   type="password"
                                                                   value="<?php echo $confirm_new_password_input; ?>">
                                                            <label id="confirm_new_password-label"
                                                                   for="confirm_new_password"
                                                                   data-error="Passwords do not match">Confirm
                                                                New Password</label>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <button id="update_password_button" disabled
                                                                class="amber darken-3 waves-effect waves-light btn right disabled g-recaptcha">
                                                            Update Password
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="collapsible-header <?php

                                    if ($page_deposit_parameter > 0)
                                        echo "active";

                                    ?>"><i class="material-icons">account_balance</i>Deposit
                                    </div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col l8 offset-l2 m10 offset-m1 s12">
                                                <div class="row">
                                                    <blockquote class="blockquote-green w900">
                                                        This is your BitcoinPVP wallet. Transfer bitcoin to this wallet
                                                        to fund your account.
                                                    </blockquote>
                                                </div>
                                                <div class="row">
                                                    <div class="input-field">
                                                        <i class="material-icons prefix">account_balance_wallet</i>
                                                        <input id="deposit_address" type="text"
                                                               value="<?php echo $bit_address; ?>" readonly>
                                                        <label id="deposit_address_label" for="deposit_address">Deposit
                                                            address</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <h4>Deposits history</h4>
                                                    <div class="col s10 offset-s1">
                                                        <table>
                                                            <thead>
                                                            <tr>
                                                                <th>Amount</th>
                                                                <th>Link</th>
                                                                <th>Date</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php

                                                            if ($depositRowCount > 0) {
                                                                foreach ($rowTableDeposits as $i) {
                                                                    echo "<tr>
                                                        <td>" . $i['amount'] / 100 . " bits</td>" .
                                                                        "<td><a href='" . $i['hash'] . "'>Click here</a></td>" .
                                                                        "<td>" . $i['deposit_date'] . "</td>" .
                                                                        "</tr>";
                                                                }

                                                            } else {
                                                                echo "<tr><td colspan='3'>No deposits yet.</td></tr>";
                                                            }
                                                            ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="row centerWrap">
                                                    <div class="centeredDiv">
                                                        <?php if ($pageCount > 1): ?>
                                                            <ul class="pagination">
                                                                <!-- Left pagination -->
                                                                <li class="<?php

                                                                if ($page > 1)
                                                                    echo "waves-effect";
                                                                else
                                                                    echo "disabled";


                                                                ?>"><a href="<?php

                                                                    if ($page > 1)
                                                                        echo $base_dir . "account/" . ($page_deposit_parameter - 1) . $page_withdraw_parameter . $page_transfer_parameter;
                                                                    else
                                                                        echo "#!";

                                                                    ?>"><i class="material-icons">chevron_left</i></a>
                                                                </li>
                                                                <!-- Numbers pagination -->
                                                                <?php if ($pageCount <= 7): ?>
                                                                    <?php for ($i = 1; $i <= $pageCount; $i++) : ?>
                                                                        <li class="<?php
                                                                        if ($i == $page)
                                                                            echo "active";
                                                                        else
                                                                            echo "waves-effect";

                                                                        ?>">
                                                                            <a href="<?php echo $base_dir . "account/" . $i . $page_withdraw_parameter . $page_transfer_parameter; ?>">
                                                                                <?php echo $i; ?>
                                                                            </a>
                                                                        </li>
                                                                    <?php endfor; ?>
                                                                <?php else: ?>
                                                                    <?php if ($page <= 3): ?>
                                                                        <?php for ($i = 1; $i <= 6; $i++): ?>
                                                                            <li class="<?php
                                                                            if ($i == $page)
                                                                                echo "active";
                                                                            else
                                                                                echo "waves-effect";

                                                                            ?>">
                                                                                <a href="<?php echo $base_dir . "account/" . $i . $page_withdraw_parameter . $page_transfer_parameter; ?>">
                                                                                    <?php echo $i; ?>
                                                                                </a>
                                                                            </li>
                                                                        <?php endfor; ?>
                                                                        <li class="">...</li>
                                                                        <li class="waves-effect">
                                                                            <a href="<?php echo $base_dir . "account/" . $pageCount . $page_withdraw_parameter . $page_transfer_parameter; ?>">
                                                                                <?php echo $pageCount; ?>
                                                                            </a>
                                                                        </li>
                                                                    <?php elseif ($page > 3 && $page < ($pageCount - 3)): ?>
                                                                        <li class="waves-effect"><a
                                                                                    href="<?php echo $base_dir . "account/1" . $page_withdraw_parameter . $page_transfer_parameter; ?>">1</a>
                                                                        </li>
                                                                        <li>...</li>
                                                                        <?php for ($i = $page - 2; $i <= $page + 2; $i++): ?>
                                                                            <li class="<?php
                                                                            if ($i == $page)
                                                                                echo "active";
                                                                            else
                                                                                echo "waves-effect";

                                                                            ?>">
                                                                                <a href="<?php echo $base_dir . "account/" . $i . $page_withdraw_parameter . $page_transfer_parameter; ?>">
                                                                                    <?php echo $i; ?>
                                                                                </a>
                                                                            </li>
                                                                        <?php endfor; ?>
                                                                        <li>...</li>
                                                                        <li class="waves-effect">
                                                                            <a href="<?php echo $base_dir . "account/" . $pageCount . $page_withdraw_parameter . $page_transfer_parameter; ?>">
                                                                                <?php echo $pageCount; ?>
                                                                            </a>
                                                                        </li>
                                                                    <?php else: ?>
                                                                        <li class="waves-effect"><a
                                                                                    href="<?php echo $base_dir . "account/1" . $page_withdraw_parameter . $page_transfer_parameter; ?>">1</a>
                                                                        </li>
                                                                        <li>...</li>
                                                                        <?php for ($i = $pageCount - 5; $i <= $pageCount; $i++): ?>
                                                                            <li class="<?php
                                                                            if ($i == $page)
                                                                                echo "active";
                                                                            else
                                                                                echo "waves-effect";

                                                                            ?>">
                                                                                <a href="<?php echo $base_dir . "account/" . $i . $page_withdraw_parameter . $page_transfer_parameter; ?>">
                                                                                    <?php echo $i; ?>
                                                                                </a>
                                                                            </li>
                                                                        <?php endfor; ?>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                                <!-- Right pagination-->
                                                                <li class="<?php

                                                                if ($page < $pageCount)
                                                                    echo "waves-effect";
                                                                else
                                                                    echo "disabled";


                                                                ?>"><a href="<?php

                                                                    if ($page_deposit_parameter == 0)
                                                                        echo $base_dir . "account/2" . $page_withdraw_parameter . $page_transfer_parameter;
                                                                    else if ($page < $pageCount)
                                                                        echo $base_dir . "account/" . ($page_deposit_parameter + 1) . $page_withdraw_parameter . $page_transfer_parameter;
                                                                    else
                                                                        echo "#!";

                                                                    ?>"><i class="material-icons">chevron_right</i></a>
                                                                </li>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="collapsible-header <?php

                                    if (!empty($withdraw_error_message) || ($page_withdraw_parameter > 0)) {
                                        echo "active";
                                    }

                                    ?>"><i class="material-icons">file_download</i>Withdraw
                                    </div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col l10 offset-l1 m10 offset-m1 s12">
                                                <form method="post"
                                                      action="<?php echo $base_dir; ?>actions/withdraw">
                                                    <blockquote class="blockquote-green w900">
                                                        Transfer bitcoin to your personal wallet. Amount must be an
                                                        integer
                                                        number greater than 100 bits. A 100 bits
                                                        mining fee will be added to the transaction.
                                                    </blockquote>
                                                    <?php if (!empty($withdraw_error_message)) : ?>
                                                        <div class="col s12">
                                                            <blockquote class="blockquote-error w900">
                                                                <?php echo $withdraw_error_message; ?>
                                                            </blockquote>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="input-field col l8 m7 s6">
                                                        <i class="material-icons prefix">account_balance_wallet</i>
                                                        <input type="text" id="withdraw_address" name="withdraw_address"
                                                               value="<?php echo $withdraw_address_input; ?>">
                                                        <label for="withdraw_address" id="withdraw_address_label">Wallet
                                                            Address</label>
                                                    </div>
                                                    <div class="input-field col l4 m5 s6">
                                                        <i class="material-icons prefix">bubble_chart</i>
                                                        <input type="number" id="withdraw_amount" name="withdraw_amount"
                                                               value="<?php echo $withdraw_amount_input; ?>">
                                                        <label for="withdraw_amount" id="withdraw_amount_label">Amount
                                                            (bits)</label>
                                                    </div>
                                                    <div class="row">

                                                    </div>
                                                    <div class="row">
                                                        <button id="withdraw_button" disabled
                                                                class="amber darken-3 waves-effect waves-light btn right disabled g-recaptcha">
                                                            Withdraw
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col l10 offset-l1 m10 offset-m1 s12">
                                                <h4>Withdrawals history</h4>
                                            </div>
                                            <div class="col l8 offset-l2 m10 offset-m1 s12">
                                                <table class="">
                                                    <thead>
                                                    <tr>
                                                        <th>Amount</th>
                                                        <th>Link</th>
                                                        <th>Request date</th>
                                                        <th>Completed on</th>
                                                        <th></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php if ($withdrawRowCount > 0): ?>
                                                        <?php foreach ($rowTableWithdraws as $i): ?>
                                                            <tr>
                                                                <td><?php echo $i['amount'] / 100; ?> bits</td>
                                                                <td><a href="<?php echo $i['hash']; ?>">Click here</a>
                                                                </td>
                                                                <td><?php echo $i['request_date']; ?></td>
                                                                <td><?php
                                                                    if (empty($i['completed_on']))
                                                                        echo "<span class='warning-text'>Unconfirmed</span>";
                                                                    else
                                                                        echo "<span class='win-text'>" . $i['completed_on'] . "</span>";

                                                                    ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="4">No withdrawals yet.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="row centerWrap">
                                            <div class="centeredDiv">
                                                <?php if ($pageWithdrawCount > 1): ?>
                                                    <ul class="pagination">
                                                        <!-- Left pagination -->
                                                        <li class="<?php

                                                        if ($pageWithdraw > 1)
                                                            echo "waves-effect";
                                                        else
                                                            echo "disabled";


                                                        ?>"><a href="<?php

                                                            if ($pageWithdraw > 1)
                                                                echo $base_dir . "account/" . $page_deposit_parameter . ($page_withdraw_parameter - 1) . $page_transfer_parameter;
                                                            else
                                                                echo "#!";

                                                            ?>"><i class="material-icons">chevron_left</i></a>
                                                        </li>
                                                        <!-- Numbers pagination -->
                                                        <?php if ($pageWithdrawCount <= 7): ?>
                                                            <?php for ($i = 1; $i <= $pageWithdrawCount; $i++) : ?>
                                                                <li class="<?php
                                                                if ($i == $pageWithdraw)
                                                                    echo "active";
                                                                else
                                                                    echo "waves-effect";

                                                                ?>">
                                                                    <a href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $i . $page_transfer_parameter ?>">
                                                                        <?php echo $i; ?>
                                                                    </a>
                                                                </li>
                                                            <?php endfor; ?>
                                                        <?php else: ?>
                                                            <?php if ($pageWithdraw <= 3): ?>
                                                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                                                    <li class="<?php
                                                                    if ($i == $pageWithdraw)
                                                                        echo "active";
                                                                    else
                                                                        echo "waves-effect";

                                                                    ?>">
                                                                        <a href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $i . $page_transfer_parameter ?>">
                                                                            <?php echo $i; ?>
                                                                        </a>
                                                                    </li>
                                                                <?php endfor; ?>
                                                                <li class="">...</li>
                                                                <li class="waves-effect">
                                                                    <a href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $pageWithdrawCount . $page_transfer_parameter ?>">
                                                                        <?php echo $pageWithdrawCount; ?>
                                                                    </a>
                                                                </li>
                                                            <?php elseif ($pageWithdraw > 3 && $pageWithdraw < ($pageWithdrawCount - 3)): ?>
                                                                <li class="waves-effect"><a
                                                                            href="<?php echo $base_dir . "account/" . $page_deposit_parameter . "1" . $page_transfer_parameter; ?>">1</a>
                                                                </li>
                                                                <li>...</li>
                                                                <?php for ($i = $pageWithdraw - 2; $i <= $pageWithdraw + 2; $i++): ?>
                                                                    <li class="<?php
                                                                    if ($i == $pageWithdraw)
                                                                        echo "active";
                                                                    else
                                                                        echo "waves-effect";

                                                                    ?>">
                                                                        <a href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $i . $page_transfer_parameter; ?>">
                                                                            <?php echo $i; ?>
                                                                        </a>
                                                                    </li>
                                                                <?php endfor; ?>
                                                                <li>...</li>
                                                                <li class="waves-effect">
                                                                    <a href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $pageWithdrawCount . $page_transfer_parameter; ?>">
                                                                        <?php echo $pageWithdrawCount; ?>
                                                                    </a>
                                                                </li>
                                                            <?php else: ?>
                                                                <li class="waves-effect"><a
                                                                            href="<?php echo $base_dir . "account/" . $page_deposit_parameter . "1" . $page_transfer_parameter; ?>">1</a>
                                                                </li>
                                                                <li>...</li>
                                                                <?php for ($i = $pageWithdrawCount - 5; $i <= $pageWithdrawCount; $i++): ?>
                                                                    <li class="<?php
                                                                    if ($i == $pageWithdraw)
                                                                        echo "active";
                                                                    else
                                                                        echo "waves-effect";

                                                                    ?>">
                                                                        <a href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $i . $page_transfer_parameter; ?>">
                                                                            <?php echo $i; ?>
                                                                        </a>
                                                                    </li>
                                                                <?php endfor; ?>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                        <!-- Right pagination-->
                                                        <li class="<?php

                                                        if ($pageWithdraw < $pageWithdrawCount)
                                                            echo "waves-effect";
                                                        else
                                                            echo "disabled";


                                                        ?>"><a href="<?php

                                                            if ($page_withdraw_parameter == 0)
                                                                echo $base_dir . "account/" . $page_deposit_parameter . "2" . $page_transfer_parameter;
                                                            elseif ($pageWithdraw < $pageWithdrawCount)
                                                                echo $base_dir . "account/" . $page_deposit_parameter . ($page_withdraw_parameter + 1) . $page_transfer_parameter;
                                                            else
                                                                echo "#!";

                                                            ?>"><i class="material-icons">chevron_right</i></a>
                                                        </li>
                                                    </ul>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="collapsible-header <?php

                                    if (!empty($_SESSION['transfer_user_error']) || !empty($_SESSION['transfer_amount_error']) || ($page_transfer_parameter > 0))
                                        echo "active";

                                    ?>"><i class="material-icons">swap_horiz</i>Transfer
                                    </div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col l10 offset-l1 m10 offset-m1 s12">
                                                <div class="row">
                                                    <form id="transfer_form" method="post"
                                                          action="<?php echo $base_dir; ?>actions/transfer">
                                                        <blockquote class="blockquote-green w900">
                                                            Transfer bitcoin to another user. Amount must be an integer
                                                            number greater than 100 bits. A 100 bits
                                                            mining fee will be added to the transaction.
                                                        </blockquote>
                                                        <div class="input-field col l8 m7 s6">
                                                            <i class="material-icons prefix">account_circle</i>
                                                            <input type="text" id="transfer_user" name="transfer_user"
                                                                   class="<?php

                                                                   if (!empty($_SESSION['transfer_user_error']))
                                                                       echo "invalid";

                                                                   ?>" value="<?php
                                                            if (!empty($_SESSION['transfer_user_input']))
                                                                echo $_SESSION['transfer_user_input'];

                                                            ?>"><label id="transfer_user_label" for="transfer_user"
                                                                       data-error="<?php

                                                                       if (!empty($_SESSION['transfer_user_error'])) {
                                                                           switch ($_SESSION['transfer_user_error']) {
                                                                               case 1:
                                                                                   echo "Empty field";
                                                                                   break;
                                                                               case 2:
                                                                                   echo "User does not exist";
                                                                                   break;
                                                                               case 3:
                                                                                   echo "User cannot be yourself";
                                                                                   break;
                                                                           }
                                                                       }

                                                                       ?>">
                                                                User</label>
                                                        </div>
                                                        <div class="input-field col l4 m5 s6">
                                                            <i class="material-icons prefix">bubble_chart</i>
                                                            <input type="number" id="transfer_amount"
                                                                   name="transfer_amount"
                                                                   class="<?php

                                                                   if (!empty($_SESSION['transfer_amount_error']))
                                                                       echo "invalid";

                                                                   ?>" value="<?php
                                                            if (!empty($_SESSION['transfer_amount_input']))
                                                                echo $_SESSION['transfer_amount_input'];

                                                            ?>">
                                                            <label id="transfer_amount_label" for="transfer_amount"
                                                                   data-error="<?php

                                                                   if (!empty($_SESSION['transfer_amount_error'])) {
                                                                       switch ($_SESSION['transfer_amount_error']) {
                                                                           case 1:
                                                                               echo "Empty field";
                                                                               break;
                                                                           case 2:
                                                                               echo "Amount must be an integer number";
                                                                               break;
                                                                           case 3:
                                                                               echo "Amount must be greater than 100";
                                                                               break;
                                                                           case 4:
                                                                               echo "Not enough bits";
                                                                               break;
                                                                       }
                                                                   }

                                                                   ?>">Amount(bits)</label>
                                                        </div>
                                                        <div class="row"></div>
                                                        <div class="row">
                                                            <button id="transfer_button" disabled
                                                                    class="amber darken-3 waves-effect waves-light btn right disabled g-recatpcha">
                                                                Transfer
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="row">
                                                    <h4>Transfer history</h4>
                                                    <div class="col l10 offset-l1 m12 s12">
                                                        <table>
                                                            <thead>
                                                            <tr>
                                                                <th>User</th>
                                                                <th>Amount</th>
                                                                <th>Link</th>
                                                                <th>Request date</th>
                                                                <th>Completed on</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php if ($transferRowCount > 0) :
                                                                foreach ($rowTableTransfers as $i): ?>

                                                                    <tr>
                                                                        <td><?php echo $i['username']; ?></td>
                                                                        <td><?php echo $i['amount'] / 100; ?> bits</td>
                                                                        <td><a href="<?php echo $i['hash']; ?>">Click
                                                                                here</a>
                                                                        </td>
                                                                        <td><?php echo $i['request_date']; ?></td>
                                                                        <td><?php
                                                                            if (empty($i['completed_on']))
                                                                                echo "<span class='warning-text'>Unconfirmed</span>";
                                                                            else
                                                                                echo "<span class='win-text'>" . $i['completed_on'] . "</span>";

                                                                            ?></td>
                                                                    </tr>

                                                                <?php endforeach;

                                                            else:?>
                                                                <tr>
                                                                    <td colspan="5">No transfers yet.</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="row centerWrap">
                                                    <div class="centeredDiv">
                                                        <?php if ($pageTransferCount > 1) : ?>
                                                            <ul class="pagination">
                                                                <!-- Left pagination -->
                                                                <li class="<?php

                                                                if ($pageTransfer > 1)
                                                                    echo "waves-effect";
                                                                else
                                                                    echo "disabled";


                                                                ?>"><a href="<?php

                                                                    if ($pageTransfer > 1)
                                                                        echo $base_dir . "account/" . $page_deposit_parameter . $page_withdraw_parameter . ($page_transfer_parameter - 1);
                                                                    else
                                                                        echo "#!";

                                                                    ?>"><i class="material-icons">chevron_left</i></a>
                                                                </li>
                                                                <!-- Numbers pagination -->
                                                                <?php if ($pageTransferCount <= 7): ?>
                                                                    <?php for ($i = 1; $i <= $pageTransferCount; $i++) : ?>
                                                                        <li class="<?php
                                                                        if ($i == $pageTransfer)
                                                                            echo "active";
                                                                        else
                                                                            echo "waves-effect";

                                                                        ?>">
                                                                            <a href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $page_withdraw_parameter . $i ?>">
                                                                                <?php echo $i; ?>
                                                                            </a>
                                                                        </li>
                                                                    <?php endfor; ?>
                                                                <?php else: ?>
                                                                    <?php if ($pageTransfer <= 3): ?>
                                                                        <?php for ($i = 1; $i <= 6; $i++): ?>
                                                                            <li class="<?php
                                                                            if ($i == $pageTransfer)
                                                                                echo "active";
                                                                            else
                                                                                echo "waves-effect";

                                                                            ?>">
                                                                                <a href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $page_withdraw_parameter . $i ?>">
                                                                                    <?php echo $i; ?>
                                                                                </a>
                                                                            </li>
                                                                        <?php endfor; ?>
                                                                        <li class="">...</li>
                                                                        <li class="waves-effect">
                                                                            <a href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $page_withdraw_parameter . $pageTransferCount ?>">
                                                                                <?php echo $pageTransferCount; ?>
                                                                            </a>
                                                                        </li>
                                                                    <?php elseif ($pageTransfer > 3 && $pageTransfer < ($pageTransferCount - 3)): ?>
                                                                        <li class="waves-effect"><a
                                                                                    href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $page_withdraw_parameter . "1"; ?>">1</a>
                                                                        </li>
                                                                        <li>...</li>
                                                                        <?php for ($i = $pageTransfer - 2; $i <= $pageTransfer + 2; $i++): ?>
                                                                            <li class="<?php
                                                                            if ($i == $pageTransfer)
                                                                                echo "active";
                                                                            else
                                                                                echo "waves-effect";

                                                                            ?>">
                                                                                <a href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $page_withdraw_parameter . $i ?>">
                                                                                    <?php echo $i; ?>
                                                                                </a>
                                                                            </li>
                                                                        <?php endfor; ?>
                                                                        <li>...</li>
                                                                        <li class="waves-effect">
                                                                            <a href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $page_withdraw_parameter . $pageTransferCount ?>">
                                                                                <?php echo $pageTransferCount; ?>
                                                                            </a>
                                                                        </li>
                                                                    <?php else: ?>
                                                                        <li class="waves-effect"><a
                                                                                    href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $page_withdraw_parameter . "1"; ?>">1</a>
                                                                        </li>
                                                                        <li>...</li>
                                                                        <?php for ($i = $pageTransferCount - 5; $i <= $pageTransferCount; $i++): ?>
                                                                            <li class="<?php
                                                                            if ($i == $pageTransfer)
                                                                                echo "active";
                                                                            else
                                                                                echo "waves-effect";

                                                                            ?>">
                                                                                <a href="<?php echo $base_dir . "account/" . $page_deposit_parameter . $page_withdraw_parameter . $i ?>">
                                                                                    <?php echo $i; ?>
                                                                                </a>
                                                                            </li>
                                                                        <?php endfor; ?>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                                <!-- Right pagination-->
                                                                <li class="<?php

                                                                if ($pageTransfer < $pageTransferCount)
                                                                    echo "waves-effect";
                                                                else
                                                                    echo "disabled";


                                                                ?>"><a href="<?php

                                                                    if ($page_transfer_parameter == 0)
                                                                        echo $base_dir . "account/" . $page_deposit_parameter . $page_withdraw_parameter . "2";
                                                                    elseif ($pageTransfer < $pageTransferCount)
                                                                        echo $base_dir . "account/" . $page_deposit_parameter . $page_withdraw_parameter . ($page_transfer_parameter + 1);
                                                                    else
                                                                        echo "#!";

                                                                    ?>"><i class="material-icons">chevron_right</i></a>
                                                                </li>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <?php if ($ticket_content_error != 0): ?>
                                        <div class="collapsible-header active"><i class="material-icons">live_help</i>Support
                                        </div>
                                    <?php else: ?>
                                        <div class="collapsible-header"><i class="material-icons">live_help</i>Support
                                        </div>
                                    <?php endif; ?>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col l8 offset-l2 m10 offset-m1 s12">
                                                <form method="post"
                                                      action="<?php echo $base_dir; ?>actions/send_ticket"
                                                      id="ticket_form">
                                                    <blockquote class="blockquote-green w900">
                                                        Do you have a question or concern? Send a ticket, and we will be
                                                        happy
                                                        to help you.
                                                    </blockquote>
                                                    <br>
                                                    <div class="input-field col s12">
                                                        <i class="material-icons prefix">short_text</i>
                                                        <input type="text" id="support_subject"
                                                               class="<?php echo $is_ticket_subject_invalid; ?>"
                                                               name="support_subject"
                                                               placeholder="Subject (optional)" data-length="80"
                                                               value="<?php
                                                               if (!empty($_SESSION['ticket_input_subject']))
                                                                   echo $_SESSION['ticket_input_subject']; ?>">
                                                        <label for="support_subject" id="support_subject_label"
                                                               data-error="<?php echo $ticket_subject_data_error; ?>">Subject</label>
                                                    </div>
                                                    <div class="input-field col s12">
                                                        <i class="material-icons prefix">textsms</i>
                                                        <?php //If there is an error
                                                        //Add invalid class
                                                        if ($ticket_content_error != 0): ?>
                                                            <textarea id="support_content" name="support_content"
                                                                      class="materialize-textarea invalid"
                                                                      data-length="2000"><?php
                                                                if (!empty($_SESSION['ticket_input_content']))
                                                                    echo $_SESSION['ticket_input_content']; ?></textarea>
                                                        <?php else: ?>
                                                            <textarea id="support_content" name="support_content"
                                                                      class="materialize-textarea"
                                                                      data-length="2000"><?php
                                                                if (!empty($_SESSION['ticket_input_content']))
                                                                    echo $_SESSION['ticket_input_content']; ?></textarea>
                                                        <?php endif; ?>
                                                        <?php if ($ticket_content_error == 2): ?>
                                                            <label for="support_content" id="support_content_label"
                                                                   data-error="Message must have at least 50 characters">
                                                                Message</label>
                                                        <?php else: ?>
                                                            <label for="support_content" id="support_content_label"
                                                                   data-error="Message is too long">Message</label>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="row"></div>
                                                    <div class="row"></div>
                                                    <div class="row">
                                                        <button id="ticket_button" disabled
                                                                class="waves-effect waves-light btn right disabled g-recaptcha">
                                                            Submit
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        <?php else: ?>
                            <div class="card">
                                <div class="card-content">
                                    <span class="card-title"><b>Email Confirmation</b></span>
                                    <p>A link was sent to your email account. Click on it to confirm your account.
                                        You might need to check your junk folder.</p>
                                </div>
                                <div class="card-action">
                                    <a href="actions/send_confirmation_email">Resend email</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="centerWrap">
                    <div class="centeredDiv">
                        <span class="h5Span"><i class="material-icons left">error</i>You must be logged in to access this page.</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>

    <!-- Custom scripts -->
    <script src="<?php echo $base_dir; ?>js/account-script.js"></script>

    <!-- Recaptcha-->
    <script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit' async defer></script>

    <!-- Form submits -->
    <script type="text/javascript">
        var onloadCallback = function () {
            $(".g-recaptcha").each(function () {
                var object = $(this);
                grecaptcha.render(object.attr("id"), {
                    "sitekey": "6Lf1d0EUAAAAAHlf_-pGuqjxWwBfy-UVkdJt-xLf",
                    "callback": function (token) {
                        object.parents('form').find(".g-recaptcha-response").val(token);
                        object.parents('form').submit();
                    }
                });
            });
        }

    </script>
<?php include "inc/footer.php";

unset($_SESSION['transfer_user_error']);
unset($_SESSION['transfer_amount_error']);
unset($_SESSION['transfer_amount_input']);
unset($_SESSION['transfer_user_input']);
unset($_SESSION['account_management_success']);
unset($_SESSION['input_code']);

/*Ticket stuff*/
unset($_SESSION['ticket_input_subject']);
unset($_SESSION['ticket_input_content']);


?>
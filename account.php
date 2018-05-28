<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/24/17
 * Time: 11:12 PM
 */
session_start();

require_once '/home/luckiestguyever/PhpstormProjects/bitcoinLottery/vendor/autoload.php';

include "function.php";
include "globals.php";
include "inc/login_checker.php";

$rowPerPage = 7;

if ($logged_in) {

    $driver = new \Nbobtc\Http\Driver\CurlDriver();
    $driver
        ->addCurlOption(CURLOPT_VERBOSE, true)
        ->addCurlOption(CURLOPT_STDERR, '/var/logs/curl.err');

    $client = new \Nbobtc\Http\Client('http://puppetmaster:vz6qGFsHBv5auSSDhTPWPktVu@localhost:18332');
    $client->withDriver($driver);

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

            //Transfers pageCount
            $stmt = $conn->prepare('SELECT COUNT(transfer_id) AS the_count FROM transfer WHERE user_id = :user_id1
            OR to_user = :user_id2');
            $stmt->execute(array('user_id1' => $user_id, 'user_id2' => $user_id));
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

            //Withdraws pageCount
            $stmt = $conn->prepare('SELECT COUNT(withdrawal_id) AS the_count FROM withdrawal WHERE user_id = :user_id');
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

            //Selecting deposits

            $command = new \Nbobtc\Command\Command('getreceivedbyaddress', $bit_address);

            /** @var \Nbobtc\Http\Message\Response */
            $response = $client->sendCommand($command);

            /** @var string */
            $output = json_decode($response->getBody()->getContents());

            $total_deposits = $output->result * 1000000;

//            $array_of_deposits = array();
//            foreach ($output->result as $transaction) {
//
//                if ($transaction->category == "receive") {
//                    array_push($array_of_deposits, $transaction);
//                }
//            }

            //Deposits pageCount
//            $depositRowCount = count($array_of_deposits);
//            $pageCount = ceil($depositRowCount / $rowPerPage);
//
//            if (!empty($_GET['p'])) {
//                $page = htmlspecialchars($_GET['p']);
//                filterOnlyNumber($page, 1, $pageCount, 1);
//                $page_deposit_parameter = $page;
//            } else {
//                $page = 1;
//                $page_deposit_parameter = 0;
//            }
//
//            $slice_from = ($page - 1) * $rowPerPage;
//
//            array_slice($array_of_deposits, $slice_from, $rowPerPage);

            //Selecting withdrawals

            $stmt = $conn->prepare('SELECT user_id, txid, inserted_on FROM withdrawal WHERE user_id = :user_id
                                      ORDER BY inserted_on DESC LIMIT :rows OFFSET :the_offset');
            $stmt->execute(array('user_id' => $user_id, 'rows' => $rowPerPage, 'the_offset' => (($pageWithdraw - 1) * $rowPerPage)));
            $rowTableWithdraws = $stmt->fetchAll(PDO::FETCH_ASSOC);


            $array_of_withdrawals = array();
            foreach ($rowTableWithdraws as $item) {

                $command = new \Nbobtc\Command\Command('gettransaction', $item['txid']);

                /** @var \Nbobtc\Http\Message\Response */
                $response = $client->sendCommand($command);

                /** @var string */
                $output = json_decode($response->getBody()->getContents());

                $resultRPC = $output->result;
                $resultDetails = $resultRPC->details;

                $transaction = new stdClass();
                $transaction->txid = $item['txid'];
                $transaction->confirmations = $resultRPC->confirmations;
                $transaction->timereceived = $resultRPC->timereceived;

                foreach ($resultDetails as $resultDetail) {
                    if ($resultDetail->category == "send") {
                        $transaction->amount = $resultDetail->amount;
                        $transaction->fee = $resultDetail->fee;
                    }
                }

                array_push($array_of_withdrawals, $transaction);

            }

            //Selecting transfers
            $stmt = $conn->prepare('SELECT
  (SELECT username FROM user WHERE  user_id = t.user_id) from_u,
  (SELECT username FROM user WHERE  user_id = t.to_user) to_u,
  amount,
  transfer_time
  FROM transfer t
WHERE t.user_id = :user_id1
OR t.to_user = :user_id2
                                      ORDER BY transfer_time DESC LIMIT :rows OFFSET :the_offset');
            $stmt->execute(array('user_id1' => $user_id, 'user_id2' => $user_id, 'rows' => $rowPerPage, 'the_offset' => (($pageTransfer - 1) * $rowPerPage)));
            $rowTableTransfers = $stmt->fetchAll(PDO::FETCH_ASSOC);


//Hiding part of email for security purposes
            $email = hide_mail($email);

            $error_update_email = !empty($_SESSION['expand_email']) ? $_SESSION['expand_email'] : false;
            $expand_email_collapsible = $email_update_requests || $error_update_email;

            unset($_SESSION['expand_email']);

            $new_email_input = !empty($_SESSION['new-email']) ? $_SESSION['new-email'] : "";
            $confirm_email_input = !empty($_SESSION['confirm-email']) ? $_SESSION['confirm-email'] : "";

            if (!empty($_SESSION['captcha_failed_email']) && $_SESSION['captcha_failed_email'])
                $email_error_message = "reCAPTCHA validation field.";
            elseif (!empty($_SESSION['email_empty_fields']) && $_SESSION['email_empty_fields'])
                $email_error_message = "All fields are required.";
            else
                $email_error_message = "";

            unset($_SESSION['captcha_failed_email']);
            unset($_SESSION['email_empty_fields']);
            unset($_SESSION['new-email']);
            unset($_SESSION['confirm-email']);

            /* PASSWORD STUFF */
            $expand_password_collapsible = !empty($_SESSION['expand_password']) ? $_SESSION['expand_password'] : false;

            if (!empty($_SESSION['captcha_failed_password']) && $_SESSION['captcha_failed_password'])
                $password_error_message = "reCAPTCHA validation failed.";
            elseif (!empty($_SESSION['password_empty_fields']) && $_SESSION['password_empty_fields'])
                $password_error_message = "All fields are required.";
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


            /* WITHDRAW STUFF */

            $withdraw_address_input = !empty($_SESSION['withdraw_address_input']) ? $_SESSION['withdraw_address_input'] : "";
            $withdraw_amount_input = !empty($_SESSION['withdraw_amount_input']) ? $_SESSION['withdraw_amount_input'] : "";

            unset($_SESSION['withdraw_address_input']);
            unset($_SESSION['withdraw_amount_input']);

            if (!empty($_SESSION['captcha_failed_withdraw']) && $_SESSION['captcha_failed_withdraw'])
                $withdraw_error_message = "reCAPTCHA validation failed.";
            elseif (!empty($_SESSION['withdraw_empty_fields']) && $_SESSION['withdraw_empty_fields'])
                $withdraw_error_message = "All fields are required.";
            elseif (!empty($_SESSION['withdraw_invalid_address']) && $_SESSION['withdraw_invalid_address'])
                $withdraw_error_message = "Invalid Bitcoin address.";
            elseif (!empty($_SESSION['withdraw_insufficient']) && $_SESSION['withdraw_insufficient'])
                $withdraw_error_message = "You do not have enough bits to do this transaction.";
            elseif (!empty($_SESSION['withdraw_blockchain_error']) && $_SESSION['withdraw_blockchain_error'])
                $withdraw_error_message = $_SESSION['withdraw_blockchain_error'];
            else
                $withdraw_error_message = "";

            $expand_withdraw_collapsible = !empty($_SESSION['expand_withdraw']) ? $_SESSION['expand_withdraw'] : false;

            unset($_SESSION['withdraw_blockchain_error']);
            unset($_SESSION['captcha_failed_withdraw']);
            unset($_SESSION['withdraw_invalid_address']);
            unset($_SESSION['withdraw_insufficient']);
            unset($_SESSION['withdraw_empty_fields']);
            unset($_SESSION['expand_withdraw']);

            /* TRANSFER STUFF */

            $transfer_user_input = !empty($_SESSION['transfer_user_input']) ? $_SESSION['transfer_user_input'] : "";
            $transfer_amount_input = !empty($_SESSION['transfer_amount_input']) ? $_SESSION['transfer_amount_input'] : "";

            if (!empty($_SESSION['captcha_failed_transfer']) && $_SESSION['captcha_failed_transfer'])
                $transfer_error_message = "reCAPTCHA validation failed.";
            elseif (!empty($_SESSION['transfer_empty_fields']) && $_SESSION['transfer_empty_fields'])
                $transfer_error_message = "All fields are required.";
            elseif (!empty($_SESSION['transfer_not_enough_balance']) && $_SESSION['transfer_not_enough_balance'])
                $transfer_error_message = "You do not have enough bits to do this transaction.";
            else
                $transfer_error_message = "";

            $expand_transfer_collapsible = !empty($_SESSION['expand_transfer']) ? $_SESSION['expand_transfer'] : false;

            unset($_SESSION['transfer_user_input']);
            unset($_SESSION['transfer_amount_input']);
            unset($_SESSION['captcha_failed_transfer']);
            unset($_SESSION['transfer_empty_fields']);
            unset($_SESSION['transfer_not_enough_balance']);
            unset($_SESSION['expand_transfer']);

            /* Ticket form*/

            $ticket_input_subject = !empty($_SESSION['ticket_input_subject']) ? $_SESSION['ticket_input_subject'] : "";
            $ticket_input_content = !empty($_SESSION['ticket_input_content']) ? $_SESSION['ticket_input_content'] : "";


            if (!empty($_SESSION['captcha_failed_ticket']) && $_SESSION['captcha_failed_ticket'])
                $ticket_error_message = "reCAPTCHA validation failed.";
            elseif (!empty($_SESSION['ticket_empty_content']) && $_SESSION['ticket_empty_content'])
                $ticket_error_message = "Message cannot be empty.";
            else
                $ticket_error_message = "";

            $expand_ticket_collapsible = !empty($_SESSION['expand_ticket']) ? $_SESSION['expand_ticket'] : false;

            unset($_SESSION['ticket_input_subject']);
            unset($_SESSION['ticket_input_content']);
            unset($_SESSION['captcha_failed_ticket']);
            unset($_SESSION['ticket_empty_content']);
            unset($_SESSION['expand_ticket']);

        } else {

            $successfully_resent = !empty($_SESSION['confirm_email_sent_again_success']) ? $_SESSION['confirm_email_sent_again_success'] : false;
            $failed_resent = !empty($_SESSION['too_soon_to_send_confirm_email_again']) ? $_SESSION['too_soon_to_send_confirm_email_again'] : false;

            unset($_SESSION['confirm_email_sent_again_success']);
            unset($_SESSION['too_soon_to_send_confirm_email_again']);
        }


    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

}
$title = "Account - BitcoinPVP";

include "inc/header.php"; ?>
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
                                                    echo "Transfer completed successfully.";
                                                    break;
                                                case 4:
                                                    echo "Your transaction is being confirmed by the blockchain.";
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
                                <li class="<?php if ($expand_email_collapsible) echo "active"; ?>">
                                    <div class="collapsible-header">
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
                                                <?php if (!empty($email_error_message)): ?>
                                                    <div class="col s12">
                                                        <blockquote class="blockquote-error w900">
                                                            <?php echo $email_error_message; ?>
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
                                                            <label for="new-email">New Email</label>
                                                            <span id="new_email_helper" class="helper-text"
                                                                  data-error="" data-success="Email is available">e.g myemail@email.com</span>
                                                        </div>
                                                        <div class="input-field col s12">
                                                            <i class="material-icons prefix">mail</i>
                                                            <input name="confirm-email" id="confirm-email"
                                                                   type="email"
                                                                   class="" value="<?php echo $confirm_email_input; ?>">
                                                            <label for="confirm-email">Confirm New Email</label>
                                                            <span id="confirm_email_helper" class="helper-text"
                                                                  data-error="Emails do not match"></span>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <button id="updateEmailButton"
                                                                class="g-recaptcha waves-effect waves-light btn right disabled amber darken-3"
                                                                disabled>
                                                            Update Email
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="<?php if ($expand_password_collapsible) echo "active"; ?>">
                                    <div class="collapsible-header">
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
                                                            <label for="current_password">Current Password</label>
                                                            <span class="helper-text">The password you use to log in</span>
                                                        </div>
                                                        <div class="input-field col s12">
                                                            <i class="material-icons prefix">lock</i>
                                                            <input name="new_password" id="new_password" type="password"
                                                                   value="<?php echo $new_password_input; ?>">
                                                            <label for="new_password">New Password</label>
                                                            <span class="helper-text"
                                                                  data-error="New password must be at least 8 characters long">At least 8 characters long</span>
                                                        </div>
                                                        <div class="input-field col s12">
                                                            <i class="material-icons prefix">enhanced_encryption</i>
                                                            <input name="confirm_new_password" id="confirm_new_password"
                                                                   type="password"
                                                                   value="<?php echo $confirm_new_password_input; ?>">
                                                            <label for="confirm_new_password">Confirm New
                                                                Password</label>
                                                            <span class="helper-text"
                                                                  data-error="Passwords do not match"></span>
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
                                <li class="<?php if ($page_deposit_parameter > 0) echo "active"; ?>">
                                    <div class="collapsible-header"><i class="material-icons">account_balance</i>Deposit
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
                                                    <h6><b>Total Deposits: </b><?php echo $total_deposits; ?> bits</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="<?php
                                if (!empty($withdraw_error_message) || ($page_withdraw_parameter > 0) ||
                                    $expand_withdraw_collapsible) {
                                    echo "active";
                                } ?>">
                                    <div class="collapsible-header"><i class="material-icons">file_download</i>Withdraw
                                    </div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col l10 offset-l1 m10 offset-m1 s12">
                                                <form method="post"
                                                      action="<?php echo $base_dir; ?>actions/withdraw">
                                                    <blockquote class="blockquote-green w900">
                                                        Transfer Bitcoin to your personal wallet. Amount must be an
                                                        integer
                                                        number greater than 200 bits. We include a fee of 20
                                                        satoshis/byte.
                                                        For the median transaction size of 225 bytes, this results in a
                                                        fee of 4,500 satoshis.
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
                                                        <label for="withdraw_address">Wallet Address</label>
                                                        <span class="helper-text" id="withdraw_address_helper   ">The Bitcoin address to which you will send your bits</span>

                                                    </div>
                                                    <div class="input-field col l4 m5 s6">
                                                        <i class="material-icons prefix">bubble_chart</i>
                                                        <input type="number" id="withdraw_amount" name="withdraw_amount"
                                                               value="<?php echo $withdraw_amount_input; ?>">
                                                        <label for="withdraw_amount">Amount (bits)</label>
                                                        <span id="withdraw_amount_helper" class="helper-text">Only integers allowed.</span>
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
                                                        <th>Fee</th>
                                                        <th>Transaction</th>
                                                        <th>Confirmations</th>
                                                        <th>Transaction Time</th>
                                                        <th></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php if ($withdrawRowCount > 0): ?>
                                                        <?php foreach ($array_of_withdrawals as $i): ?>
                                                            <tr>
                                                                <td><?php echo $i->amount * 1000000 * -1; ?> bits</td>
                                                                <td><?php echo $i->fee * 1000000 * -1; ?> bits</td>
                                                                <td>
                                                                    <a href="https://live.blockcypher.com/btc-testnet/tx/<?php echo $i->txid; ?>"
                                                                       target="_blank">See transaction</a>
                                                                </td>
                                                                <td><?php
                                                                    if ($i->confirmations > 0)
                                                                        echo "<span class='win-text'>" . $i->confirmations . "</span>";
                                                                    else
                                                                        echo "<span class='lose-text'>" . $i->confirmations . "</span>";
                                                                    ?></td>
                                                                <td class="withdraw-time"><?php echo $i->timereceived; ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="5">No withdrawals yet.</td>
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
                                <li class="<?php if ($expand_transfer_collapsible || ($page_transfer_parameter > 0))
                                    echo "active"; ?>">
                                    <div class="collapsible-header"><i class="material-icons">swap_horiz</i>Transfer
                                    </div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col l10 offset-l1 m10 offset-m1 s12">
                                                <div class="row">
                                                    <form id="transfer_form" method="post"
                                                          action="<?php echo $base_dir; ?>actions/transfer">
                                                        <blockquote class="blockquote-green w900">
                                                            Transfer bitcoin instantly to another user without any fee.
                                                        </blockquote>
                                                        <?php if (!empty($transfer_error_message)) : ?>
                                                            <div class="col s12">
                                                                <blockquote class="blockquote-error w900">
                                                                    <?php echo $transfer_error_message; ?>
                                                                </blockquote>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="input-field col l8 m7 s6">
                                                            <i class="material-icons prefix">account_circle</i>
                                                            <input type="text" id="transfer_user" name="transfer_user"
                                                                   value="<?php echo $transfer_user_input; ?>">
                                                            <label for="transfer_user">User</label>
                                                            <span id="transfer_user_helper" class="helper-text">To whom are you sending bits?</span>
                                                        </div>
                                                        <div class="input-field col l4 m5 s6">
                                                            <i class="material-icons prefix">bubble_chart</i>
                                                            <input type="number" id="transfer_amount"
                                                                   name="transfer_amount"
                                                                   value="<?php echo $transfer_amount_input; ?>">
                                                            <label for="transfer_amount">Amount(bits)</label>
                                                            <span id="transfer_amount_helper" class="helper-text">Only integers allowed</span>
                                                        </div>
                                                        <div class="row"></div>
                                                        <div class="row">
                                                            <button id="transfer_button"
                                                                    class="amber darken-3 waves-effect waves-light btn right disabled g-recaptcha"
                                                                    disabled>
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
                                                                <th>From</th>
                                                                <th>To</th>
                                                                <th>Amount</th>
                                                                <th>Transfer Date</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php if ($transferRowCount > 0) :
                                                                foreach ($rowTableTransfers as $i): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <a href="<?php echo $base_dir . "user-stats/" .
                                                                                $i['from_u']; ?>"
                                                                               target="_blank"><?php echo $i['from_u']; ?></a>
                                                                        </td>
                                                                        <td>
                                                                            <a href="<?php echo $base_dir . "user-stats/" .
                                                                                $i['to_u']; ?>"
                                                                               target="_blank"><?php echo $i['to_u']; ?></a>
                                                                        </td>
                                                                        <td><?php echo $i['amount'];
                                                                            if ($i['amount'] > 1)
                                                                                echo " bits";
                                                                            else
                                                                                echo "bit"; ?> </td>
                                                                        <td class="transfer_time"><?php echo $i['transfer_time']; ?></td>
                                                                    </tr>

                                                                <?php endforeach;

                                                            else:?>
                                                                <tr>
                                                                    <td colspan="4">No transfers yet.</td>
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
                                <li class="<?php if ($expand_ticket_collapsible) echo "active"; ?>">
                                    <div class="collapsible-header">
                                        <i class="material-icons">live_help</i>Support
                                    </div>
                                    <div class="collapsible-body">
                                        <div class="row">
                                            <div class="col l8 offset-l2 m10 offset-m1 s12">
                                                <form method="post"
                                                      action="<?php echo $base_dir; ?>actions/send-ticket"
                                                      id="ticket_form">
                                                    <blockquote class="blockquote-green w900">
                                                        Do you have a question or concern? Send a ticket, and we will be
                                                        happy
                                                        to help you.
                                                    </blockquote>
                                                    <br>
                                                    <?php if (!empty($ticket_error_message)) : ?>
                                                        <div class="col s12">
                                                            <blockquote class="blockquote-error w900">
                                                                <?php echo $ticket_error_message; ?>
                                                            </blockquote>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="input-field col s12">
                                                        <i class="material-icons prefix">short_text</i>
                                                        <input type="text" id="support_subject"
                                                               name="support_subject"
                                                               placeholder="(Optional)" data-length="78"
                                                               value="<?php echo $ticket_input_subject; ?>">
                                                        <label for="support_subject">Subject</label>
                                                        <span id="support_subject_helper" class="helper-text"></span>
                                                    </div>
                                                    <div class="input-field col s12">
                                                        <i class="material-icons prefix">textsms</i>
                                                        <textarea id="support_content" name="support_content"
                                                                  class="materialize-textarea"
                                                                  data-length="2000"><?php echo $ticket_input_content; ?></textarea>
                                                        <label for="support_content"> Message</label>
                                                        <span id="support_content_helper" class="helper-text"></span>
                                                    </div>
                                                    <div class="row">
                                                        <button id="ticket_button" disabled
                                                                class="waves-effect waves-light btn right disabled g-recaptcha amber darken-3">
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
                            <?php if ($successfully_resent): ?>
                                <blockquote class="blockquote-green w900">
                                    Email successfully sent.
                                </blockquote>
                            <?php endif; ?>
                            <?php if ($failed_resent): ?>
                                <blockquote class="blockquote-error w900">
                                    Wait at least 10 minutes to resend the link.
                                </blockquote>
                            <?php endif; ?>
                            <div class="card">
                                <div class="card-content">
                                    <span class="card-title"><b>Email Confirmation</b></span>
                                    <p>A link was sent to your email account. Click on it to confirm your account.
                                        You might need to check your junk folder.</p>
                                </div>
                                <div class="card-action">
                                    <a href="<?php echo $base_dir; ?>actions/resend-confirmation-email">Resend email</a>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
    <script src="<?php echo $base_dir; ?>js/jquery-dateformat.min.js"></script>

    <!-- Custom scripts -->
    <script src="<?php echo $base_dir; ?>js/account-script.js"></script>

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
                object.prop('disabled', true);
            });
        }

    </script>

    <!-- Recaptcha-->
    <script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit' async defer></script>


<?php include "inc/footer.php";


unset($_SESSION['account_management_success']);

/*Ticket stuff*/
unset($_SESSION['ticket_input_subject']);
unset($_SESSION['ticket_input_content']);


?>
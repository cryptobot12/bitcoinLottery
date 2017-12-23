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

$user_id = $_SESSION['user_id'];
$rowPerPage = 1;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    //Getting email
    $stmt = $conn->prepare('SELECT bit_address, email FROM user WHERE user_id = :user_id');
    $stmt->execute(array('user_id' => $user_id));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $email = $result['email'];
    $bit_address = $result['bit_address'];

    //Selecting code
    $stmt = $conn->prepare('SELECT code, code_expires FROM user WHERE user_id = :user_id');
    $stmt->execute(array('user_id' => $user_id));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $code_expires = $result['code_expires'];

    //Deposits pageCount
    $stmt = $conn->prepare('SELECT COUNT(hash) AS the_count FROM deposit WHERE user_id = :user_id');
    $stmt->execute(array('user_id' => $user_id));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $depositRowCount = $result['the_count'];
    $pageCount = ceil($depositRowCount / $rowPerPage);

    if (!empty($_GET['p'])) {
        $page = htmlspecialchars($_GET['p']);
        filterOnlyNumber($page, 1, $pageCount, 1);
    } else {
        $page = 1;
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
    } else {
        $pageWithdraw = 1;
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
    } else {
        $pageTransfer = 1;
    }

    //Selecting deposits
    $stmt = $conn->prepare('SELECT hash, amount, DATE_FORMAT(deposit_date, "%M %D, %Y") AS deposit_date FROM deposit WHERE user_id = :user_id
                                      ORDER BY deposit_date DESC LIMIT :rows OFFSET :the_offset');
    $stmt->execute(array('user_id' => $user_id, 'rows' => $rowPerPage, 'the_offset' => (($page - 1) * $rowPerPage)));
    $rowTableDeposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Selecting withdrawals
    $stmt = $conn->prepare('SELECT hash, amount, DATE_FORMAT(request_date, "%M %D, %Y") AS request_date,
 DATE_FORMAT(completed_on, "%M %D, %Y") AS completed_on FROM withdrawal WHERE user_id = :user_id
                                      ORDER BY request_date DESC LIMIT :rows OFFSET :the_offset');
    $stmt->execute(array('user_id' => $user_id, 'rows' => $rowPerPage, 'the_offset' => (($pageWithdraw - 1) * $rowPerPage)));
    $rowTableWithdraws = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Selecting transfers
    $stmt = $conn->prepare('SELECT u.username, hash, amount, DATE_FORMAT(request_date, "%M %D, %Y") AS request_date,
 DATE_FORMAT(completed_on, "%M %D, %Y") AS completed_on FROM transfer AS t 
  INNER JOIN user AS u
  ON t.to_user = u.user_id
  WHERE t.user_id = :user_id
                                      ORDER BY request_date DESC LIMIT :rows OFFSET :the_offset');
    $stmt->execute(array('user_id' => $user_id, 'rows' => $rowPerPage, 'the_offset' => (($pageTransfer - 1) * $rowPerPage)));
    $rowTableTransfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <?php include "inc/header.php" ?>
</header>
<main class="<?php
if (!(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])))
    echo 'valign-wrapper'; ?>">
    <div class="container">
        <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
            <div class="row top-buffer-30">
                <div class="col l10 offset-l1 m10 offset-m1 s12">
                    <?php if (!empty($_SESSION['account_management_success'])) : ?>
                        <div class="row centerWrap">
                            <div class="centeredDiv">
                                <blockquote class="w900">
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
                                    }

                                    ?>
                                </blockquote>
                            </div>
                        </div>
                    <?php endif; ?>
                    <ul class="collapsible popout" data-collapsible="expandable">
                        <li>
                            <div class="collapsible-header <?php if (!empty($_SESSION['upd_email'])) {
                                echo "active";
                                unset($_SESSION['upd_email']);
                            } ?>"><i class="material-icons">email</i>Update email
                            </div>
                            <div class="collapsible-body">
                                <div class="row">
                                    <div class="col l8 offset-l2 m10 offset-m1 s12">
                                        <?php if ((empty($result['code'])) || (time() > strtotime($code_expires))): ?>
                                            <blockquote class="w900">
                                                We use this email account to recover your password and to keep you
                                                updated about changes in your account.
                                            </blockquote>
                                            <form class=""
                                                  action="php_actions/update_email_code.php"
                                                  method="post">
                                                <div class="row">
                                                    <div class="input-field col s12">
                                                        <input disabled name="old-email" id="old-email" type="email"
                                                               value="<?php echo $email; ?>">
                                                        <label id="oldEmailLabel" for="old-email"
                                                               data-error="Invalid email">Current
                                                            Email</label>
                                                    </div>
                                                    <div class="input-field col s9">
                                                        <input name="new-email" id="new-email" type="email" class="<?php
                                                        if (!empty($_SESSION['invalid_email']) || !empty($_SESSION['email_taken'])) {
                                                            echo 'invalid';
                                                        }
                                                        ?>" value="<?php
                                                        if (!empty($_SESSION['new-email'])) {
                                                            echo $_SESSION['new-email'];
                                                            unset($_SESSION['new-email']);
                                                        }
                                                        ?>">
                                                        <label id="newEmailLabel" for="new-email"
                                                               data-error="<?php
                                                               if (!empty($_SESSION['email_taken'])) {
                                                                   echo "Email is already taken";
                                                                   unset($_SESSION['email_taken']);
                                                               }

                                                               if (!empty($_SESSION['invalid_email'])) {
                                                                   echo "Invalid email";
                                                                   unset($_SESSION['invalid_email']);
                                                               }

                                                               ?>" data-success="Email is available">New
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
                                                    <div class="input-field col s3 little-top-buffer no-text-overflow">
                                                        <a id="checkAvailability" class=""
                                                           href="#!">Check&nbsp;availability</a>
                                                    </div>
                                                    <div class="input-field col s12">
                                                        <input name="confirm-email" id="confirm-email" type="email"
                                                               class="<?php
                                                               if (!empty($_SESSION['unmatch'])) {
                                                                   echo 'invalid';
                                                                   unset($_SESSION['unmatch']);
                                                               }
                                                               ?>" value="<?php
                                                        if (!empty($_SESSION['confirm-email'])) {
                                                            echo $_SESSION['confirm-email'];
                                                            unset($_SESSION['confirm-email']);
                                                        }

                                                        ?>">
                                                        <label id="confirmEmailLabel" for="confirm-email"
                                                               data-error="Emails do not match">Confirm New Email
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
                                                            class="waves-effect waves-light btn right disabled">Update
                                                        Email
                                                    </button>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <form class="col l8 offset-l2 m10 offset-m1 s12"
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
                                                        $_SESSION == true): ?>
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
                            </div>
                        </li>
                        <li>
                            <div class="collapsible-header <?php


                            if (!empty($_SESSION['incorrect_cp']) || !empty($_SESSION['incorrect_length']) ||
                                !empty($_SESSION['unmatch_p']) || !empty($_SESSION['diff_pass'])) {
                                echo "active";
                            }

                            ?>"><i class="material-icons">lock</i>Update password
                            </div>
                            <div class="collapsible-body">
                                <div class="row">
                                    <div class="col l8 offset-l2 m10 offset-m1 s12">
                                        <form class=""
                                              action="php_actions/update_password.php"
                                              method="post">
                                            <div class="row">
                                                <blockquote class="w900">
                                                    Your new password must be at least 8 characters long. We encourage
                                                    you
                                                    to use a combination of symbols, numbers and letters for your new
                                                    password in order to protect your account.
                                                </blockquote>
                                                <div class="input-field col s12">
                                                    <input name="current_password" id="current_password" type="password"
                                                           class="<?php

                                                           if (!empty($_SESSION['incorrect_cp'])) {
                                                               echo "invalid";
                                                               unset($_SESSION['incorrect_cp']);
                                                           }

                                                           ?>"
                                                           value="<?php

                                                           if (!empty($_SESSION['current_password'])) {
                                                               echo $_SESSION['current_password'];
                                                               unset($_SESSION['current_password']);
                                                           }


                                                           ?>">
                                                    <label id="current_password-label" for="current_password"
                                                           data-error="Incorrect password">Current
                                                        password&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                </div>
                                                <div class="input-field col s12">
                                                    <input name="new_password" id="new_password" type="password"
                                                           class="<?php

                                                           if (!empty($_SESSION['incorrect_length']) || !empty($_SESSION['diff_pass'])) {
                                                               echo "invalid";
                                                           }

                                                           ?>"
                                                           value="<?php

                                                           if (!empty($_SESSION['new_password'])) {
                                                               echo $_SESSION['new_password'];
                                                               unset($_SESSION['new_password']);
                                                           }


                                                           ?>">
                                                    <label id="new_password-label" for="new_password"
                                                           data-error="<?php

                                                           if (!empty($_SESSION['incorrect_length']))
                                                               echo "Password must be at least 8 characters long";
                                                           elseif (!empty($_SESSION['diff_pass']))
                                                               echo "New password must be different from current password";

                                                           unset($_SESSION['incorrect_length']);
                                                           unset($_SESSION['diff_pass']);

                                                           ?>">New
                                                        password
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                </div>
                                                <div class="input-field col s12">
                                                    <input name="confirm_new_password" id="confirm_new_password"
                                                           type="password" class="<?php

                                                    if (!empty($_SESSION['unmatch_p'])) {
                                                        echo "invalid";
                                                        unset($_SESSION['unmatch_p']);
                                                    }

                                                    ?>"
                                                           value="<?php

                                                           if (!empty($_SESSION['confirm_new_password'])) {
                                                               echo $_SESSION['confirm_new_password'];
                                                               unset($_SESSION['confirm_new_password']);
                                                           }


                                                           ?>">
                                                    <label id="confirm_new_password-label" for="confirm_new_password"
                                                           data-error="Passwords do not match">Confirm
                                                        new password&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <button type="submit" id="update_password_button"
                                                        class="waves-effect waves-light btn right disabled">Update
                                                    Password
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="collapsible-header <?php

                            if (!empty($_GET['p']))
                                echo "active";

                            ?>"><i class="material-icons">monetization_on</i>Deposit
                            </div>
                            <div class="collapsible-body">
                                <div class="row">
                                    <div class="col l8 offset-l2 m10 offset-m1 s12">
                                        <div class="row">
                                            <blockquote class="w900">
                                                This is your bitPVP wallet. Transfer bitcoin to this wallet
                                                to fund your account.
                                            </blockquote>
                                        </div>
                                        <div class="row">
                                            <div class="input-field">
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
                                                                echo "account.php?p=" . ($page - 1);
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
                                                                    <a href="<?php echo "account.php?p=" . $i ?>">
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
                                                                        <a href="<?php echo "account.php?p=" . $i ?>">
                                                                            <?php echo $i; ?>
                                                                        </a>
                                                                    </li>
                                                                <?php endfor; ?>
                                                                <li class="">...</li>
                                                                <li class="waves-effect">
                                                                    <a href="<?php echo "account.php?p=" . $pageCount ?>">
                                                                        <?php echo $pageCount; ?>
                                                                    </a>
                                                                </li>
                                                            <?php elseif ($page > 3 && $page < ($pageCount - 3)): ?>
                                                                <li class="waves-effect"><a href="account.php?p=1">1</a>
                                                                </li>
                                                                <li>...</li>
                                                                <?php for ($i = $page - 2; $i <= $page + 2; $i++): ?>
                                                                    <li class="<?php
                                                                    if ($i == $page)
                                                                        echo "active";
                                                                    else
                                                                        echo "waves-effect";

                                                                    ?>">
                                                                        <a href="<?php echo "account.php?p=" . $i ?>">
                                                                            <?php echo $i; ?>
                                                                        </a>
                                                                    </li>
                                                                <?php endfor; ?>
                                                                <li>...</li>
                                                                <li class="waves-effect">
                                                                    <a href="<?php echo "account.php?p=" . $pageCount ?>">
                                                                        <?php echo $pageCount; ?>
                                                                    </a>
                                                                </li>
                                                            <?php else: ?>
                                                                <li class="waves-effect"><a href="account.php?p=1">1</a>
                                                                </li>
                                                                <li>...</li>
                                                                <?php for ($i = $pageCount - 5; $i <= $pageCount; $i++): ?>
                                                                    <li class="<?php
                                                                    if ($i == $page)
                                                                        echo "active";
                                                                    else
                                                                        echo "waves-effect";

                                                                    ?>">
                                                                        <a href="<?php echo "account.php?p=" . $i ?>">
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

                                                            if ($page < $pageCount)
                                                                echo "account.php?p=" . ($page + 1);
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

                            if (!empty($_SESSION['withdraw_address_error']) || !(empty($_SESSION['withdraw_amount_error'])) ||
                                !empty($_SESSION['withdraw_insufficient']) || !empty($_GET['pw'])) {
                                echo "active";
                            }

                            ?>"><i class="material-icons">local_atm</i>Withdraw
                            </div>
                            <div class="collapsible-body">
                                <div class="row">
                                    <div class="col l10 offset-l1 m10 offset-m1 s12">
                                        <form method="post" action="php_actions/withdraw.php">
                                            <blockquote class="w900">
                                                Transfer bitcoin to your personal wallet. Amount must be an integer
                                                number greater than 100 bits. A 100 bits
                                                mining fee will be added to the transaction.
                                            </blockquote>
                                            <?php if (!empty($_SESSION['withdraw_insufficient'])) :
                                                unset($_SESSION['withdraw_insufficient']); ?>
                                                <div class="row">
                                                    <p class="input-alert font-15">* You do not have enough bits to do
                                                        this transaction.</p>
                                                </div>
                                            <?php endif; ?>
                                            <div class="input-field col l8 m7 s6">
                                                <input class="<?php

                                                if (!empty($_SESSION['withdraw_address_error'])) {
                                                    echo "invalid";
                                                }

                                                ?>" type="text" id="withdraw_address" name="withdraw_address"
                                                       value="<?php

                                                       if (!empty($_SESSION['withdraw_address_input'])) {
                                                           echo $_SESSION['withdraw_address_input'];
                                                           unset($_SESSION['withdraw_address_input']);
                                                       }

                                                       ?>">
                                                <label for="withdraw_address" data-error="<?php

                                                if (!empty($_SESSION['withdraw_address_error'])) {
                                                    echo $_SESSION['withdraw_address_error'];
                                                    unset($_SESSION['withdraw_address_error']);
                                                }

                                                ?>">Address&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;</label>
                                            </div>
                                            <div class="input-field col l4 m5 s6">
                                                <input type="number" id="withdraw_amount" name="withdraw_amount"
                                                       class="<?php

                                                       if (!empty($_SESSION['withdraw_amount_error'])) {
                                                           echo "invalid";
                                                       }

                                                       ?>" value="<?php

                                                if (!empty($_SESSION['withdraw_amount_input'])) {
                                                    echo $_SESSION['withdraw_amount_input'];
                                                    unset($_SESSION['withdraw_amount_input']);
                                                }

                                                ?>">
                                                <label for="withdraw_amount" data-error="<?php

                                                if (!empty($_SESSION['withdraw_amount_error'])) {
                                                    echo $_SESSION['withdraw_amount_error'];
                                                    unset($_SESSION['withdraw_amount_error']);
                                                }

                                                ?>">Amount (bits)&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                            </div>
                                            <div class="row">

                                            </div>
                                            <div class="row">
                                                <button type="submit" class="waves-effect waves-light btn right">
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
                                                        <td><a href="<?php echo $i['hash']; ?>">Click here</a></td>
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
                                                        echo "account.php?pw=" . ($pageWithdraw - 1);
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
                                                            <a href="<?php echo "account.php?pw=" . $i ?>">
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
                                                                <a href="<?php echo "account.php?pw=" . $i ?>">
                                                                    <?php echo $i; ?>
                                                                </a>
                                                            </li>
                                                        <?php endfor; ?>
                                                        <li class="">...</li>
                                                        <li class="waves-effect">
                                                            <a href="<?php echo "account.php?pw=" . $pageWithdrawCount ?>">
                                                                <?php echo $pageWithdrawCount; ?>
                                                            </a>
                                                        </li>
                                                    <?php elseif ($pageWithdraw > 3 && $pageWithdraw < ($pageWithdrawCount - 3)): ?>
                                                        <li class="waves-effect"><a href="account.php?pw=1">1</a>
                                                        </li>
                                                        <li>...</li>
                                                        <?php for ($i = $pageWithdraw - 2; $i <= $pageWithdraw + 2; $i++): ?>
                                                            <li class="<?php
                                                            if ($i == $pageWithdraw)
                                                                echo "active";
                                                            else
                                                                echo "waves-effect";

                                                            ?>">
                                                                <a href="<?php echo "account.php?pw=" . $i ?>">
                                                                    <?php echo $i; ?>
                                                                </a>
                                                            </li>
                                                        <?php endfor; ?>
                                                        <li>...</li>
                                                        <li class="waves-effect">
                                                            <a href="<?php echo "account.php?pw=" . $pageWithdrawCount ?>">
                                                                <?php echo $pageWithdrawCount; ?>
                                                            </a>
                                                        </li>
                                                    <?php else: ?>
                                                        <li class="waves-effect"><a href="account.php?pw=1">1</a>
                                                        </li>
                                                        <li>...</li>
                                                        <?php for ($i = $pageWithdrawCount - 5; $i <= $pageWithdrawCount; $i++): ?>
                                                            <li class="<?php
                                                            if ($i == $pageWithdraw)
                                                                echo "active";
                                                            else
                                                                echo "waves-effect";

                                                            ?>">
                                                                <a href="<?php echo "account.php?pw=" . $i ?>">
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

                                                    if ($pageWithdraw < $pageWithdrawCount)
                                                        echo "account.php?pw=" . ($pageWithdraw + 1);
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

                            if (!empty($_SESSION['transfer_user_error']) || !empty($_SESSION['transfer_amount_error']))
                                echo "active";

                            ?>"><i class="material-icons">swap_horiz</i>Transfer
                            </div>
                            <div class="collapsible-body">
                                <div class="row">
                                    <div class="col l10 offset-l1 m10 offset-m1 s12">
                                        <div class="row">
                                            <form id="transfer_form" method="post" action="php_actions/transfer.php">
                                                <blockquote class="w900">
                                                    Transfer bitcoin to another user. Amount must be an integer
                                                    number greater than 100 bits. A 100 bits
                                                    mining fee will be added to the transaction.
                                                </blockquote>
                                                <div class="input-field col l8 m7 s6">
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
                                                        User&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;</label>
                                                </div>
                                                <div class="input-field col l4 m5 s6">
                                                    <input type="number" id="transfer_amount" name="transfer_amount"
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

                                                           ?>">Amount(bits)&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                </div>
                                                <div class="row"></div>
                                                <div class="row">
                                                    <button type="submit" id="transfer_button"
                                                            class="waves-effect waves-light btn right disabled">Transfer
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
                                                                echo "account.php?pt=" . ($pageTransfer - 1);
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
                                                                    <a href="<?php echo "account.php?pt=" . $i ?>">
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
                                                                        <a href="<?php echo "account.php?pt=" . $i ?>">
                                                                            <?php echo $i; ?>
                                                                        </a>
                                                                    </li>
                                                                <?php endfor; ?>
                                                                <li class="">...</li>
                                                                <li class="waves-effect">
                                                                    <a href="<?php echo "account.php?pt=" . $pageTransferCount ?>">
                                                                        <?php echo $pageTransferCount; ?>
                                                                    </a>
                                                                </li>
                                                            <?php elseif ($pageTransfer > 3 && $pageTransfer < ($pageTransferCount - 3)): ?>
                                                                <li class="waves-effect"><a
                                                                            href="account.php?pt=1">1</a>
                                                                </li>
                                                                <li>...</li>
                                                                <?php for ($i = $pageTransfer - 2; $i <= $pageTransfer + 2; $i++): ?>
                                                                    <li class="<?php
                                                                    if ($i == $pageTransfer)
                                                                        echo "active";
                                                                    else
                                                                        echo "waves-effect";

                                                                    ?>">
                                                                        <a href="<?php echo "account.php?pt=" . $i ?>">
                                                                            <?php echo $i; ?>
                                                                        </a>
                                                                    </li>
                                                                <?php endfor; ?>
                                                                <li>...</li>
                                                                <li class="waves-effect">
                                                                    <a href="<?php echo "account.php?pt=" . $pageTransferCount ?>">
                                                                        <?php echo $pageTransferCount; ?>
                                                                    </a>
                                                                </li>
                                                            <?php else: ?>
                                                                <li class="waves-effect"><a
                                                                            href="account.php?pt=1">1</a>
                                                                </li>
                                                                <li>...</li>
                                                                <?php for ($i = $pageTransferCount - 5; $i <= $pageTransferCount; $i++): ?>
                                                                    <li class="<?php
                                                                    if ($i == $pageTransfer)
                                                                        echo "active";
                                                                    else
                                                                        echo "waves-effect";

                                                                    ?>">
                                                                        <a href="<?php echo "account.php?pt=" . $i ?>">
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

                                                            if ($pageTransfer < $pageTransferCount)
                                                                echo "account.php?pt=" . ($pageTransfer + 1);
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
                            <div class="collapsible-header"><i class="material-icons">live_help</i>Support</div>
                            <div class="collapsible-body">
                                <div class="row">
                                    <div class="col l8 offset-l2 m10 offset-m1 s12">
                                        <form method="post" action="php_actions/send_ticket.php">
                                            <div class="input-field col s12">
                                                <input type="text" id="support_subject" name="support_subject"
                                                       placeholder="Subject (optional)">
                                                <label for="support_subject" id="support_subject_label">Subject</label>
                                            </div>
                                            <div class="input-field col s12">
                                                <textarea id="support_content" name="support_content"
                                                          class="materialize-textarea"></textarea>
                                                <label for="support_content" id="support_content_label">Message</label>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <h3 class="center-align"><i class="medium material-icons vmid">error</i> You must be logged in.</h3>
        <?php endif; ?>
    </div>
</main>
<?php include "inc/footer.php"; ?>
</body>
<?php

if (!empty($_SESSION['transfer_user_error']))
    unset($_SESSION['transfer_user_error']);

if (!empty($_SESSION['transfer_amount_error']))
    unset($_SESSION['transfer_amount_error']);

if (!empty($_SESSION['transfer_amount_input']))
    unset($_SESSION['transfer_amount_input']);

if (!empty($_SESSION['transfer_user_input']))
    unset($_SESSION['transfer_user_input']);

if (!empty($_SESSION['account_management_success']))
    unset($_SESSION['account_management_success']);

?>
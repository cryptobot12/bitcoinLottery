<?php
session_start();
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/17/2017
 * Time: 1:51 PM
 *
 *
 */

require __DIR__ . '/vendor/autoload.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bitcoin</title>
    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
    <script src="js/autobahn.js"></script>
    <script>
        var numbersGlobal = <?php echo json_encode($_SESSION['numbers_list']); ?>;
    </script>
    <script src="js/nostylescripts.js"></script>
    <script src="js/stylescript.js"></script>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<header>
    <!-- Profile Structure -->
    <?php if (isset($_SESSION['username']) && !empty($_SESSION['username'])) : ?>
        <ul id="profileDropdown" class="dropdown-content">
            <li><a href="#!"><i class="material-icons left">person</i>Profile</a></li>
            <li><a href="#!"><i class="material-icons left">exit_to_app</i>Logout</a></li>
        </ul>
    <?php endif; ?>
    <!-- Stats structure   -->
    <ul id="statsDropdown" class="dropdown-content">
        <li><a href="game_info.php"><i class="material-icons left">assignment</i>Game Info</a></li>
        <li><a href="stats.php"><i class="material-icons left">assessment</i>Server Stats</a></li>
    </ul>
    <!-- Navbar goes here -->
    <nav>
        <div class="nav-wrapper green">
            <a href="#" class="brand-logo left">BitPVP</a>
            <ul id="nav-mobile" class="right .hide-on-small-only nav-letters">
                <li><a href="rank.php"><i class="material-icons left">assistant_photo</i><b>Ranking</b></a></li>
                <li><a class="dropdown-button" href="#" data-activates="statsDropdown">
                        <i class="material-icons left">trending_up</i><b>Stats</b><i
                                class="material-icons right">arrow_drop_down</i></a></a></li>
                <li class="no-link-nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>
                <?php if (isset($_SESSION['username']) && !empty($_SESSION['username'])): ?>
                    <li class="no-link-nav"><i class="material-icons left">account_balance_wallet</i>
                        <b>Balance: <span id="balanceNumber"><?php include 'updateBalance.php'; ?></span> bits</b></li>
                    <li><a class="dropdown-button" href="#" data-activates="profileDropdown">
                            <b><?php echo $_SESSION['username']; ?></b><i
                                    class="material-icons right">arrow_drop_down</i></a>
                    </li>
                <?php else: ?>
                    <li><a href="registration.php"><b>Register</b></a></li>
                    <li><a href="login.php"><b>Login</b></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>
<!--    Beginning of first row   -->
<main>
    <div class="row">
        <div class="col s4">
            <!--            Jackpot card   -->
            <div class="card z-depth-5">
                <div class="card-content">
                    <h3 class="center-align" id="jackpot">Jackpot: <span
                                id="jackpotNumber"><?php include 'show_jackpot.php' ?></span> bits</h3>
                    <p class="center-align" style="font-weight: lighter; font-size: 16px;"
                       id="time"><?php include 'timer.php' ?></p>
                </div>
            </div>
            <!--            Play card    -->
            <div class="card z-depth-5">
                <div class="card-content">
                    <?php if (isset($_SESSION['username']) && !empty($_SESSION['username'])): ?>
                        <div class="row">
                            <ul class="tabs tabs-fixed-width">
                                <li class="tab col s4 green-text"><a href="#fieldDiv">Text</a></li>
                                <li class="tab col s4"><a href="#randomDiv">Random selection</a></li>
                                <li class="tab col s4"><a href="#sequenceDiv">Sequence</a></li>
                            </ul>
                            <!--       Text Area Input          -->
                            <div id="fieldDiv" class="col s12">
                                <div class="input-field col s12">
                                    <label for="field"></label>
                                    <textarea id="numbersArea" class="materialize-textarea"
                                              placeholder="Type your numbers separated by spaces."></textarea>
                                    <label for="numbersArea"></label>
                                </div>
                                <p class="center-align">
                                    <a class="waves-effect waves-light btn disabled modal-trigger" id="checkButtonField"
                                       href="#modal1">
                                        <i class="material-icons left">attach_money</i>Bet<i
                                                class="material-icons right">attach_money</i></a>
                                </p>
                                <div class="subText smaller top-buffer-15">Only 200 numbers allowed. Numbers must be
                                    between 1 and 50000.
                                </div>
                                <br>
                                <p class="center-align price-text">
                                    Each number costs 50 bits.
                                </p>
                            </div>
                            <!--   Random Input    -->
                            <div id="randomDiv" class="col s12">
                                <div class="row top-buffer-15">
                                    <div class="input-field col s4">
                                        <input placeholder="Start" id="start" type="number" class="" value="1">
                                        <label for="start">Start range</label>
                                    </div>
                                    <div class="input-field col s4">
                                        <input placeholder="End" id="end" type="number" class="" value="200">
                                        <label for="end">End range</label>
                                    </div>
                                    <div class="input-field col s4">
                                        <input placeholder="How many numbers?" id="numberOfNumbers" type="number"
                                               class="" value="20">
                                        <label for="numberOfNumbers">How many numbers?</label>
                                    </div>
                                </div>
                                <p class="center-align">
                                    <a class="waves-effect waves-light btn" id="checkButtonRandom">
                                        <i class="material-icons left">attach_money</i>Bet<i
                                                class="material-icons right">attach_money</i></a>
                                </p>
                                <div class="subText smaller top-buffer-15">Only 200 numbers allowed. Numbers must be
                                    between 1 and 50000.<br>
                                    Duplicates will be removed.
                                </div>
                                <br>
                                <p class="center-align price-text">
                                    Each number costs 50 bits.
                                </p>
                            </div>
                            <!--     Sequence Input                       -->
                            <div id="sequenceDiv" class="col s12">
                                <div class="row top-buffer-15">
                                    <div class="input-field col s4">
                                        <input placeholder="Start number" id="startSequence" type="number" class=""
                                               value="1">
                                        <label for="startSequence">Start range</label>
                                    </div>
                                    <div class="input-field col s4">
                                        <input placeholder="End number" id="endSequence" type="number" class=""
                                               value="200">
                                        <label for="endSequence">End range</label>
                                    </div>
                                </div>
                                <p class="center-align">
                                    <a class="waves-effect waves-light btn" id="checkButtonSequence">
                                        <i class="material-icons left">attach_money</i>Bet<i
                                                class="material-icons right">attach_money</i></a>
                                </p>
                                <div class="subText smaller top-buffer-15">Only 200 numbers allowed. Numbers must be
                                    between 1 and 50000.
                                </div>
                                <br>
                                <p class="center-align price-text">
                                    Each number costs 50 bits.
                                </p>
                            </div>
                            <!-- Modal Structure -->
                            <div id="modal1" class="modal">
                                <div class="modal-content">
                                    <h4>Check your numbers<span class="subText" id="countConfirm">&nbsp;&nbsp;&nbsp;&nbsp;0 numbers (100 bits)</span><br>
                                        <span class="balance-alert hidden" id="insufficientText">&nbsp;&nbsp;Insufficient balance</span>
                                    </h4>
                                    <div id="confirmationNumbers"></div>
                                </div>
                                <div class="modal-footer">
                                    <a id="playButton"
                                       class="modal-action modal-close waves-effect waves-green btn-flat">Confirm</a>
                                    <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Cancel</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="center-align"><a class="waves-effect waves-light btn" href="login.php">Login to
                                play</a><br>
                            <a href="registration.php">or register</a></p>
                    <?php endif; ?>
                </div>
            </div>
            <!--            Numbers card  -->
            <ul class="collapsible z-depth-5" data-collapsible="accordion" id="expand">
                <li>
                    <div id="numbersCollapsible" class="collapsible-header">
                        <div class="valign-wrapper"><i class="material-icons" id="expand-icon">expand_less</i></div>
                        <h4>Your numbers<span class="subText"
                                              id="count">&nbsp;&nbsp;&nbsp;&nbsp;<?php include 'count_numbers.php' ?></span>
                        </h4>

                    </div>
                    <div class="collapsible-body">
                        <div id="numbersList">
                            <?php include 'numbers_list.php' ?>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <!--        Last game card   -->
        <div class="col s4">
            <div class="card z-depth-4">
                <div class="card-content">
                    <h4>Last game</h4>
                    <div id="lastGame">
                        <?php include 'last_game.php'; ?>
                    </div>
                </div>
            </div>
        </div>
        <!--        Game history card -->
        <div class="col s4">
            <div class="card z-depth-4">
                <div class="card-content">
                    <h4>Game history</h4>
                    <table id="gameHistoryTable" class="bordered responsive-table">
                        <thead>
                        <tr>
                            <th>Game #</th>
                            <th>Jackpot</th>
                            <th>Number</th>
                            <th>Time</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php include 'games_history.php'; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>
<!-- End of first row  -->
<footer class="page-footer green">
    <div class="container">
        <div class="row">
            <div class="col l6 s12">
                <h5 class="white-text">License</h5>
                <p class="grey-text text-lighten-4">Peruvian license N48D1489A-ADS4</p>
            </div>
            <div class="col l4 offset-l2 s12">
                <h5 class="white-text">Useful Links</h5>
                <ul>
                    <li><a class="grey-text text-lighten-3" href="rank.php">Ranking</a></li>
                    <li><a class="grey-text text-lighten-3" href="stats.php">Server Stats</a></li>
                    <li><a class="grey-text text-lighten-3" href="game_info.php">Game Info</a></li>
                    <li><a class="grey-text text-lighten-3" href="#!">Guide</a></li>
                    <li><a class="grey-text text-lighten-3" href="#!">Games history</a></li>
                    <li><a class="grey-text text-lighten-3" href="registration.php">Registration</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-copyright">
        <div class="container">
            © <?php echo date('Y'); ?> Copyright BitPVP
        </div>
    </div>
</footer>
</body>
</html>

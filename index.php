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
    <script src="js/autobahn.js"></script>
    <script src="js/nostylescripts.js"></script>
    <script src="js/stylescript.js"></script>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
    <!-- Navbar goes here -->
    <!-- Profile Structure -->
    <?php
        if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
            echo '<ul id="profileDropdown" class="dropdown-content">
                    <li><a href="#!"><i class="material-icons left">person</i>Profile</a></li>
                    <li><a href="#!"><i class="material-icons left">exit_to_app</i>Logout</a></li>
                  </ul>';
        }
    ?>
    <nav>
        <div class="nav-wrapper green">
            <a href="#" class="brand-logo left">BitPVP</a>
            <ul id="nav-mobile" class="right .hide-on-small-only nav-letters">
                <li><a href="rank.php"><i class="material-icons left">assistant_photo</i><b>Ranking</b></a></li>
                <li><a href="stats.php"><i class="material-icons left">assessment</i><b>Stats</b></a></li>
                <?php
                    if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
                        echo '<li class="no-link-nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>';
                        echo '<li class="no-link-nav"><b>Balance: <span id="balanceNumber">';
                        include 'updateBalance.php';
                        echo '</span> bits</b></li>
                            <li><a class="dropdown-button" href="#" data-activates="profileDropdown"><b>' .
                            $_SESSION['username'] . '</b><i class="material-icons right">arrow_drop_down</i></a></li>';
                    }
                    else {
                        echo '<li><a href="registration.php"><b>Register</b></a></li>
                              <li><a href="login.php"><b>Login</b></a></li>';
                    }
                 ?>
            </ul>
        </div>
    </nav>
    <div class="row">
        <div class="col s4">
            <div class="card z-depth-5">
                <div class="card-content">
                    <h3 class="center-align" id="jackpot">Jackpot: <span id="jackpotNumber"><?php include 'show_jackpot.php' ?></span> bits</h3>
                    <p class="center-align" style="font-weight: lighter; font-size: 16px;" id="time"><?php include 'timer.php' ?></p>
                </div>
            </div>
            <div class="card z-depth-5">
                <div class="card-content">


                        <div class="row">
                            <input class="with-gap" name="inputType" type="radio" id="field" checked value="1"/>
                            <label for="field">Text</label>
                            <div class="row">
                                <div class="input-field col s11 offset-s1">
                                    <label for="field"></label>
                                    <textarea id="numbersArea" class="materialize-textarea" placeholder="Type your numbers separated by spaces."></textarea>
                                    <label for="numbersArea"></label>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <input class="with-gap" name="inputType" type="radio" id="random" value="2">
                            <label for="random">Random selection</label>
                            <div class="row top-buffer-15">
                                <div class="input-field col s3 offset-s1">
                                    <input disabled placeholder="Start" id="start" type="text" class="validate">
                                    <label for="start">Start range</label>
                                </div>
                                <div class="input-field col s4">
                                    <input disabled placeholder="End" id="end" type="text" class="validate">
                                    <label for="end">End range</label>
                                </div>
                                <div class="input-field col s4">
                                    <input disabled placeholder="How many numbers?" id="numberOfNumbers" type="text" class="validate">
                                    <label for="numberOfNumbers">How many numbers?</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <input class="with-gap" name="inputType" type="radio" id="sequence" value="3">
                            <label for="sequence">Sequence</label>
                            <div class="row top-buffer-15">
                                <div class="input-field col s4 offset-s1">
                                    <input disabled placeholder="Start" id="startSequence" type="text" class="validate">
                                    <label for="startSequence">Start range</label>
                                </div>
                                <div class="input-field col s4">
                                    <input disabled placeholder="End" id="endSequence" type="text" class="validate">
                                    <label for="endSequence">End range</label>
                                </div>
                            </div>
                        </div>
                    <p class="center-align">
                        <a class="waves-effect waves-light btn" id="playButton">
                            <i class="material-icons left">attach_money</i>Play<i class="material-icons right">attach_money</i></a>
                    </p>


                </div>
            </div>
            <div class="card z-depth-5">
                <div class="card-content">
                    <h4>Your numbers</h4>
                    <div id="numbersList">
                        <?php include 'numbers_list.php' ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s4">
            <div class="card z-depth-4">
                <div class="card-content">
                    <h4>Last game</h4>
                    <div id="lastGame">
                        <?php include 'last_game.php';?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s4">
            <div class="card z-depth-4">
                <div class="card-content">
                    <h4>Game history</h4>
                    <table id="gameHistoryTable" class="bordered">
                        <thead>
                        <tr>
                            <th>Game #</th>
                            <th>Number</th>
                            <th>Jackpot</th>
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
</body>
</html>

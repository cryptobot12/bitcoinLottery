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

include "connect.php";
include "inc/login_checker.php";

$_SESSION['last_url'] = 'index.php';

//Counting numbers
if ($logged_in) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


        //Selecting current game
        $stmt = $conn->prepare('SELECT game_id FROM game ORDER BY game_id DESC, timedate DESC LIMIT 1');
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $current_game = $row['game_id'];

        //Selecting numbers list
        $stmt = $conn->prepare('SELECT COUNT(number_id) AS numbersCount FROM numberxuser WHERE user_id = (SELECT user_id
        FROM user WHERE username = :username) AND game_id = :game_id');
        $stmt->execute(array('username' => $username, 'game_id' => $current_game));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $numbers_count = $row['numbersCount'];

        if ($numbers_count > 1)
            $numbers_title = "My " . $numbers_count . " numbers";
        else if ($row['numbersCount'] == 1)
            $numbers_title = "My number";

        if ($numbers_count > 0)
            $scale_status = "scale-in";
        else
            $scale_status = "scale-out";

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bitcoin</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
    <script src="js/autobahn.js"></script>
    <script>
        var numbersGlobal = <?php echo json_encode($_SESSION['numbers_list']); ?>;
    </script>
    <script src="js/index_script.js"></script>
    <script src="js/game_script.js"></script>

    <link href="css/style.css" rel="stylesheet">

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<!--    Beginning of first row   -->
<header>
    <?php include 'inc/header.php' ?>
</header>
<main>
    <div id="main-row" class="row">
        <div class="col l4 m6 s12">
            <!--            Jackpot card   -->
            <div id="jackpot_card" class="card z-depth-1 amber lighten-1">
                <div class="card-content">
                    <span class="card-title"><b>Jackpot</b></span>
                    <h3 class="center-align" id="jackpot"><span
                                id="jackpotNumber"><?php include 'inc/show_jackpot_part.php'; ?></span> bits
                    </h3>
                    <p class="center-align" style="font-weight: lighter; font-size: 16px;"
                       id="time"><?php include 'timer.php'; ?></p>
                </div>
            </div>
            <!--            Play card    -->
            <div class="row hide-on-med-and-up">
                <div class="card z-depth-1">
                    <div class="card-content">
                        <?php if ($logged_in): ?>
                            <div class="row">
                                <ul class="tabs">
                                    <li class="tab col s4 green-text"><a href="#field_div_small">Text</a></li>
                                    <li class="tab col s4"><a href="#random_div_small">Random</a></li>
                                    <li class="tab col s4"><a href="#sequence_div_small">Sequence</a></li>
                                </ul>
                                <!--       Text Area Input          -->
                                <div id="field_div_small" class="col s12">
                                    <blockquote class="blockquote-green w900">
                                        Each number costs 50 bits.
                                        Only 200 numbers allowed. Numbers
                                        must be
                                        between 1 and 50000.
                                    </blockquote>
                                    <div class="input-field col s12">
                                        <label for="field"></label>
                                        <textarea id="numbersArea" class="materialize-textarea"
                                                  placeholder="Type your numbers separated by spaces."></textarea>
                                        <label for="numbersArea"></label>
                                    </div>
                                    <p class="center-align">
                                        <a class="waves-effect waves-light btn disabled modal-trigger"
                                           id="checkButtonField"
                                           href="#modal1">Bet</a>
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
                                            <input placeholder="Start" id="start" type="number" class="" value="1">
                                            <label for="start">Start range</label>
                                        </div>
                                        <div class="input-field col s4">
                                            <input placeholder="End" id="end" type="number" class="" value="200">
                                            <label for="end">End range</label>
                                        </div>
                                        <div class="input-field col s4">
                                            <input placeholder="How many numbers?" id="numberOfNumbers"
                                                   type="number"
                                                   class="" value="20">
                                            <label for="numberOfNumbers">How many numbers?</label>
                                        </div>
                                    </div>
                                    <p class="center-align">
                                        <a class="waves-effect waves-light btn" id="checkButtonRandom">Bet</a>
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
                                            <input placeholder="Start number" id="startSequence" type="number"
                                                   class=""
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
                                        <a class="waves-effect waves-light btn" id="checkButtonSequence">Bet</a>
                                    </p>
                                </div>
                                <!-- Modal Structure -->
                                <div id="modal1_small" class="modal">
                                    <div class="modal-content">
                                        <h4>Check your numbers<span class="subText" id="countConfirm">&nbsp;&nbsp;&nbsp;&nbsp;0 numbers (100 bits)</span><br>
                                            <span class="balance-alert hidden" id="insufficientText">&nbsp;&nbsp;Insufficient balance</span>
                                        </h4>
                                        <div id="confirmationNumbers"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <a id="playButton"
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
                <div class="row hide-on-med-and-up">
                    <!--            Numbers card  -->
                    <div id="numbers_card" class="row scale-transition <?php echo $scale_status; ?>">
                        <div class="card z-depth-1">
                            <div class="card-content">
                                <span id="count" class="card-title"><b><?php echo $numbers_title; ?></b></span>
                                <div id="numbersList"><?php include 'inc/numbers_list_part.php'; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row">
                <div id="chat-space">
                    <ul id="chat-messages">
                        <li><b>Frank:</b> Welcome!</li>
                    </ul>
                </div>
                <div id="chat-input-line" class="row">
                    <input placeholder="Enter your message here" id="input-chat" class="input-chat" type="text"><a
                            class="btn" id="chat-send"><i class="material-icons">send</i></a>
                </div>
            </div>
            <div class="row hide-on-med-and-up">
                <div class="card z-depth-1">
                    <div class="card-content">
                        <span class="card-title"><b>Last Game</b></span>
                        <div id="lastGame">
                            <?php include 'inc/last_game_part.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row hide-on-large-only">
                <!--        Game history card -->
                <div class="card z-depth-1">
                    <div class="card-content">
                        <span class="card-title"><b>Game history</b></span>
                        <table id="gameHistoryTable" class="bordered">
                            <thead>
                            <tr>
                                <th>Game #</th>
                                <th>Jackpot</th>
                                <th>Number</th>
                                <th>Time</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php include 'inc/games_history_part.php'; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col l4 m6 hide-on-small-only">
            <!--            Play card    -->
            <div class="row">
                <div class="card z-depth-1">
                    <div class="card-content">
                        <?php if ($logged_in): ?>
                            <div class="row">
                                <ul class="tabs">
                                    <li class="tab col s4 green-text"><a href="#fieldDiv">Text</a></li>
                                    <li class="tab col s4"><a href="#randomDiv">Random</a></li>
                                    <li class="tab col s4"><a href="#sequenceDiv">Sequence</a></li>
                                </ul>
                                <!--       Text Area Input          -->
                                <div id="fieldDiv" class="col s12">
                                    <blockquote class="blockquote-green w900">
                                        Each number costs 50 bits.
                                        Only 200 numbers allowed. Numbers
                                        must be
                                        between 1 and 50000.
                                    </blockquote>
                                    <div class="input-field col s12">
                                        <label for="field"></label>
                                        <textarea id="numbersArea" class="materialize-textarea"
                                                  placeholder="Type your numbers separated by spaces."></textarea>
                                        <label for="numbersArea"></label>
                                    </div>
                                    <p class="center-align">
                                        <a class="waves-effect waves-light btn disabled modal-trigger"
                                           id="checkButtonField"
                                           href="#modal1">Bet</a>
                                    </p>
                                </div>
                                <!--   Random Input    -->
                                <div id="randomDiv" class="col s12">
                                    <blockquote class="blockquote-green w900">
                                        Each number costs 50 bits.
                                        Only 200 numbers allowed. Numbers
                                        must be
                                        between 1 and 50000.
                                    </blockquote>
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
                                            <input placeholder="How many numbers?" id="numberOfNumbers"
                                                   type="number"
                                                   class="" value="20">
                                            <label for="numberOfNumbers">How many numbers?</label>
                                        </div>
                                    </div>
                                    <p class="center-align">
                                        <a class="waves-effect waves-light btn" id="checkButtonRandom">Bet</a>
                                    </p>
                                </div>
                                <!--     Sequence Input                       -->
                                <div id="sequenceDiv" class="col s12">
                                    <blockquote class="blockquote-green w900">
                                        Each number costs 50 bits.
                                        Only 200 numbers allowed. Numbers
                                        must be
                                        between 1 and 50000.
                                    </blockquote>
                                    <div class="row top-buffer-15">
                                        <div class="input-field col s4">
                                            <input placeholder="Start number" id="startSequence" type="number"
                                                   class=""
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
                                        <a class="waves-effect waves-light btn" id="checkButtonSequence">Bet</a>
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
                <div class="row">
                    <!--            Numbers card  -->
                    <div id="numbers_card" class="row scale-transition <?php echo $scale_status; ?>">
                        <div class="card z-depth-1">
                            <div class="card-content">
                                <span id="count" class="card-title"><b><?php echo $numbers_title; ?></b></span>
                                <div id="numbersList"><?php include 'inc/numbers_list_part.php'; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <!--        Last game card   -->
            <div class="row hide-on-large-only">
                <div class="card z-depth-1">
                    <div class="card-content">
                        <span class="card-title"><b>Last Game</b></span>
                        <div id="lastGame">
                            <?php include 'inc/last_game_part.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col l4 hide-on-med-and-down">
            <!--        Last game card   -->
            <div class="card z-depth-1">
                <div class="card-content">
                    <span class="card-title"><b>Last Game</b></span>
                    <div id="lastGame">
                        <?php include 'inc/last_game_part.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row hide-on-med-and-down">
        <div class="container">
            <!--        Game history card -->
            <div class="col l12">
                <div class="card z-depth-1">
                    <div class="card-content">
                        <span class="card-title"><b>Game history</b></span>
                        <table id="gameHistoryTable" class="bordered">
                            <thead>
                            <tr>
                                <th>Game #</th>
                                <th>Jackpot</th>
                                <th>Number</th>
                                <th>Time</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php include 'inc/games_history_part.php'; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>
<?php include "inc/footer.php"; ?>
</body>
</html>

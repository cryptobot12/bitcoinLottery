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
        $stmt = $conn->prepare('SELECT game_id FROM game ORDER BY game_id DESC, game_date DESC LIMIT 1');
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $current_game = $row['game_id'];


        //Selecting numbers list
        $arrayOfNumbers = array();
        $stmt = $conn->prepare('SELECT number_id FROM numberxuser WHERE user_id = :user_id
 AND game_id = :game_id');
        $stmt->execute(array('user_id' => $user_id, 'game_id' => $current_game));
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $item) {
            array_push($arrayOfNumbers, $item['number_id']);
        }


        //Counting numbers
        $stmt = $conn->prepare('SELECT COUNT(number_id) AS numbersCount FROM numberxuser WHERE user_id = :user_id
 AND game_id = :game_id');
        $stmt->execute(array('user_id' => $user_id, 'game_id' => $current_game));
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

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Selecting game history
    $stmt = $conn->prepare('SELECT game_id, date_format(game_date, \'%h:%i %p\') AS time, winner_number, amount, number_of_players FROM game
                                      ORDER BY game_id DESC, game_date DESC LIMIT 20');
    $stmt->execute();
    $game_history_table = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Getting last game information
    $stmt = $conn->prepare('SELECT game_id, amount, winner_number, number_of_players FROM game ORDER BY game_id DESC LIMIT 1, 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $last_game = $row['game_id'];
    $last_jackpot = $row['amount'] / 100;
    $last_winner_number = $row['winner_number'];
    $last_number_of_players = $row['number_of_players'];


    //Getting profit for winners
    $stmt = $conn->prepare('SELECT COUNT(win) AS number_of_w FROM gamexuser 
                                     WHERE game_id = :game_id
                                     AND win = 1');
    $stmt->execute(array('game_id' => $last_game));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $profit_winners = $jackpot * 100 / $row['number_of_w'];

    //Selecting winners
    $stmt = $conn->prepare('SELECT u.username, gu.win, gu.bet, gu.profit 
     FROM user AS u 
     INNER JOIN gamexuser AS gu
     ON u.user_id = gu.user_id
     WHERE gu.game_id = :game_id
     AND gu.win = 1');

    $stmt->execute(array('game_id' => $last_game));

    $winners_row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $n_of_winners = count($winners_row);

//    echo '<p><a id="gameLink" href="game_info.php?game_id=' . $last_game . '" target="_blank">Game #<span id="gameNumberLast">' . $last_game . '</span></a></p>';
//    echo '<div><b>Winner number: </b><div class="chip"><span id="winnerNumberLast">' . $winner_number . '</span></div></div>';
//    echo '<p><b>Jackpot: </b><span id="jackpotLast">' . $jackpot . '</span> bits</p>';
//    echo '<table id="lastGameTable" class="bordered">
//            <thead>
//            <tr>
//              <th>User</th>
//              <th>Bet</th>
//              <th>Profit</th>
//            </tr>
//            </thead>
//            <tbody>';
//
//    foreach ($row as $item){
//
//        echo '<tr class="win"><td>' .
//            $item['username'] . '</td><td>' .
//            ($item['bet'] / 100) . ' bits</td><td>';
//
//        if ($item['profit'] > 0)
//            echo '<span class="win-text">+';
//        elseif ($item['profit'] == 0)
//            echo '<span class="neutral-text">';
//        else
//            echo '<span class="lose-text">';
//        echo    ($item['profit'] / 100) . ' bits</span></td></tr>';
//    }

    //Selecting losers
    $stmt = $conn->prepare('SELECT u.username, COUNT(number_id) * 50 AS profit
                                    FROM user AS u
                                    INNER JOIN gamexuser AS gu
                                    ON u.user_id = gu.user_id
                                    INNER JOIN numberxuser AS nu
                                    ON u.user_id = nu.user_id
                                    AND nu.user_id = gu.user_id
                                    AND nu.game_id = gu.game_id
                                    WHERE gu.win = 0
                                    AND gu.game_id = :game_id
                                    ORDER BY profit DESC
                                    LIMIT :the_limit');

    $stmt->execute(array('game_id' => $last_game, 'the_limit' => (15 - $n_of_winners)));
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($row)) {

        foreach ($row as $item) {

            if ($item['username'] <> null)
                echo '<tr class="lose"><td>' .
                    $item['username'] . '</td><td>' .
                    $item['profit'] . ' bits</td><td><span class="lose-text">-' .
                    ($item['profit']) .
                    ' bits</span></td></tr>';
        }
    }

    echo '</tbody></table>';

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
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
        var numbersGlobal = <?php echo json_encode($arrayOfNumbers); ?>;
    </script>
    <script src="js/index_script.js"></script>

    <link href="css/style.css" rel="stylesheet">

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
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
                                            <input placeholder="Start" id="start_random_small" type="number" class=""
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
                                            <input placeholder="Start number" id="start_sequence_small" type="number"
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
                                        <h4>Check your numbers<span class="subText" id="">&nbsp;&nbsp;&nbsp;&nbsp;0 numbers (100 bits)</span><br>
                                            <span class="balance-alert hidden" id="">&nbsp;&nbsp;Insufficient balance</span>
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
                <div id="numbers_card" class="row scale-transition <?php echo $scale_status; ?> hide-on-med-and-up">
                    <div class="card z-depth-1">
                        <div class="card-content">
                            <span id="count" class="card-title"><b><?php echo $numbers_title; ?></b></span>
                            <div id="numbersList"><?php include 'inc/numbers_list_part.php'; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Chat -->
                <div id="chat-space">
                    <ul id="chat-messages">
                        <li><b>Frank:</b> Welcome!</li>
                    </ul>
                </div>
                <div id="chat-input-line" class="row">
                    <input placeholder="Enter your message here" id="input-chat" class="input-chat" type="text"
                           maxlength="180">
                    <button
                            class="btn" id="chat-send"><i class="material-icons">send</i></button>
                </div>
            </div>

            <!-- Game history card (medium and down only) -->
            <div class="card z-depth-1 hide-on-large-only">
                <div class="card-content">
                    <span class="card-title"><b>Game history</b></span>
                    <table id="game_history_table_large" class="bordered">
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

        </div>


        <!-- Medium and large only -->
        <div class="col l4 m6 hide-on-small">
            <!--            Play card    -->
            <div class="row">
                <div class="card z-depth-1">
                    <div class="card-content">
                        <?php if ($logged_in): ?>
                            <div class="row">
                                <ul class="tabs">
                                    <li class="tab col s4 green-text"><a href="#textarea_div_med">Text</a></li>
                                    <li class="tab col s4"><a href="#random_div_med">Random</a></li>
                                    <li class="tab col s4"><a href="#sequence_div_med">Sequence</a></li>
                                </ul>

                                <!--       Text Area Input          -->
                                <div id="textarea_div_med" class="col s12">
                                    <blockquote class="blockquote-green w900">
                                        Each number costs 50 bits.
                                        Only 200 numbers allowed. Numbers
                                        must be
                                        between 1 and 50000.
                                    </blockquote>
                                    <div class="input-field col s12">
                                        <label for="field"></label>
                                        <textarea id="numbers_textarea_med" class="materialize-textarea"
                                                  placeholder="Type your numbers separated by spaces."></textarea>
                                        <label for="numbers_textarea_med"></label>
                                    </div>
                                    <p class="center-align">
                                        <a class="waves-effect waves-light btn disabled modal-trigger"
                                           id="textarea_button_med"
                                           href="#confirm_numbers_modal_med">Bet</a>
                                    </p>
                                </div>

                                <!--   Random Input    -->
                                <div id="random_div_med" class="col s12">
                                    <blockquote class="blockquote-green w900">
                                        Each number costs 50 bits.
                                        Only 200 numbers allowed. Numbers
                                        must be
                                        between 1 and 50000.
                                    </blockquote>
                                    <div class="row top-buffer-15">
                                        <div class="input-field col s4">
                                            <input placeholder="Start" id="start_random_med" type="number" class=""
                                                   value="1">
                                            <label for="start_random_med">Start range</label>
                                        </div>
                                        <div class="input-field col s4">
                                            <input placeholder="End" id="end_random_med" type="number" class=""
                                                   value="200">
                                            <label for="end_random_med">End range</label>
                                        </div>
                                        <div class="input-field col s4">
                                            <input placeholder="How many numbers?" id="how_many_numbers_med"
                                                   type="number"
                                                   class="" value="20">
                                            <label for="how_many_numbers_med">How many numbers?</label>
                                        </div>
                                    </div>
                                    <p class="center-align">
                                        <a class="waves-effect waves-light btn" id="random_button_med">Bet</a>
                                    </p>
                                </div>

                                <!--     Sequence Input                       -->
                                <div id="sequence_div_med" class="col s12">
                                    <blockquote class="blockquote-green w900">
                                        Each number costs 50 bits.
                                        Only 200 numbers allowed. Numbers
                                        must be
                                        between 1 and 50000.
                                    </blockquote>
                                    <div class="row top-buffer-15">
                                        <div class="input-field col s4">
                                            <input placeholder="Start number" id="start_sequence_med" type="number"
                                                   class=""
                                                   value="1">
                                            <label for="start_sequence_med">Start range</label>
                                        </div>
                                        <div class="input-field col s4">
                                            <input placeholder="End number" id="end_sequence_med" type="number"
                                                   class=""
                                                   value="200">
                                            <label for="end_sequence_med">End range</label>
                                        </div>
                                    </div>
                                    <p class="center-align">
                                        <a class="waves-effect waves-light btn" id="sequence_button_med">Bet</a>
                                    </p>
                                </div>

                                <!-- Modal Structure -->
                                <div id="confirm_numbers_modal_med" class="modal">
                                    <div class="modal-content">
                                        <h4>Check your numbers<span class="subText" id="count_numbers_confirm_med">&nbsp;&nbsp;&nbsp;&nbsp;0 numbers (100 bits)</span><br>
                                            <span class="balance-alert hidden" id="insufficient_balance_med">&nbsp;&nbsp;Insufficient balance</span>
                                        </h4>
                                        <div id="confirmation_numbers_med"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <a id="play_button_med"
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
                <div id="numbers_card" class="row scale-transition <?php echo $scale_status; ?>">
                    <div class="card z-depth-1">
                        <div class="card-content">
                            <span id="count" class="card-title"><b><?php echo $numbers_title; ?></b></span>
                            <div id="numbersList"><?php include 'inc/numbers_list_part.php'; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!--        Last game card   -->
        <div class="col l4 m6 s12">
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

    <!-- Large only-->
    <div class="row hide-on-med-and-down">
        <!-- Game history card-->
        <div class="container">
            <div class="card z-depth-1">
                <div class="card-content">
                    <span class="card-title"><b>Game history</b></span>
                    <table id="game_history_table_large" class="bordered">
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
        </div>
    </div>
</main>
<?php include "inc/footer.php"; ?>
</body>
</html>

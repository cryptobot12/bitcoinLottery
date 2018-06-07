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

include "globals.php";
include "inc/login_checker.php";

$_SESSION['last_url'] = 'index';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    //Getting current game
    $stmt = $conn->prepare('SELECT game_id, amount FROM game ORDER BY game_id DESC LIMIT 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_game = $row['game_id'];

    //Getting jackpot
    $stmt = $conn->prepare('SELECT balance FROM balances WHERE username = :username');
    $stmt->execute(array('username' => 'jackpot'));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $jackpot = $row['balance'] / 100;

    $stmt = $conn->prepare('SELECT chat.message, u.username_display AS username, chat.sentat FROM chat INNER JOIN user u ON chat.user_id = u.user_id LIMIT 60');
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Counting numbers
    if ($logged_in) {

        //Selecting numbers list
        $arrayOfNumbers = array();
        $stmt = $conn->prepare('SELECT number_id FROM numberxuser WHERE user_id = :user_id
 AND game_id = :game_id');
        $stmt->execute(array('user_id' => $user_id, 'game_id' => $current_game));
        $numbers_list_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($numbers_list_result as $item) {
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
        else
            $numbers_title = "No numbers yet";
    }

    // Selecting game history
    $stmt = $conn->prepare('SELECT game_id, date_format(game_date, \'%h:%i %p\') AS time, winner_number, amount, number_of_players FROM game
                                      WHERE number_of_players <> 0
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

    //Selecting players from last game
    $stmt = $conn->prepare('SELECT u.username_display AS username, gu.win AS win, gu.bet AS bet, gu.profit AS profit
     FROM user AS u 
     INNER JOIN gamexuser AS gu
     ON u.user_id = gu.user_id
     WHERE gu.game_id = :game_id
     ORDER BY win DESC, profit DESC, bet DESC, username ASC');

    $stmt->execute(array('game_id' => $last_game));
    $players_row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Select number of winners
    $stmt = $conn->prepare('SELECT COUNT(win) AS n_o_w
    FROM gamexuser
    WHERE game_id = :game_id');
    $stmt->execute(array('game_id' => $last_game));
    $n_of_winners = $stmt->fetch(PDO::FETCH_ASSOC)['n_o_w'];

} catch (PDOException $e) {
    echo "Connection failed1: " . $e->getMessage();
}

$title = "BitcoinPVP";

include 'inc/header.php' ?>
<main>
    <div class="container">
        <div class="row">
            <div class="col s12"></div>
        </div>
        <div class="hide-on-large-only">
            <div class="row">
                <div class="col m12 s12">
                    <div class="card-panel amber lighten-1 hoverable">
                        <h5 class="center-align" id="jackpot"><b>Jackpot: </b><span
                                    id="jackpot_number_med"><?php echo $jackpot; ?></span> bits
                            <span id="timer_span_small" class="right win-text"><i class="material-icons left">timer</i> <span
                                        id="timer_small">--</span></span>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col l4 m6 s12">
                <!--            Jackpot card   -->
                <div id="jackpot_card" class="card z-depth-1 amber lighten-1 hide-on-med-and-down hoverable">
                    <div class="card-content">
                        <span class="card-title"><b>Jackpot</b>
                            <span id="timer_span_large" class="right win-text"><i class="material-icons left">timer</i> <span
                                        id="timer_large">--</span></span>
                        </span>

                        <p class="center-align h3Size" id="jackpot"><span
                                    id="jackpot_number_large"><?php echo $jackpot; ?></span> bits
                        </p>
                    </div>
                </div>

                <!--        Game INFO CARD  -->
                <div class="card z-depth-1 hide-on-small-only" id="tables">
                    <div class="card-tabs" id="table-tabs">
                        <ul class="tabs tabs-fixed-width">
                            <li class="tab"><a class="active" href="#last_game_med">Last Game</a></li>
                            <li class="tab"><a href="#game_history_med">Game History</a></li>
                        </ul>
                    </div>
                    <div class="card-content" id="game_info_card_content">
                        <div id="last_game_med">
                            <div id="last_game_header">
                                <p>
                                    <a id="game_link_med"
                                       href="<?php echo $base_dir; ?>game-info/<?php echo $last_game; ?>"
                                       target="_blank">Game #<span
                                                id="last_game_number_med"><?php echo $last_game; ?></span></a>
                                </p>
                                <div><b>Winner number: </b>
                                    <div class="chip yellow">
                                        <span id="last_winner_number_med"><b><?php echo $last_winner_number; ?></b></span>
                                    </div>
                                </div>
                                <p>
                                    <b>Jackpot: </b><span id="last_jackpot_med">
                            <?php echo $last_jackpot; ?>
                            </span> bits
                                </p>
                            </div>
                            <div id="last_game_med_table_container" class="overflowable top-buffer-15">
                                <table id="last_game_table_med" class="bordered">
                                    <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Bet</th>
                                        <th>Profit</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($players_row as $item) :?>
                                        <?php if ($item["win"] == 1): ?>
                                            <tr class="win">
                                                <td><?php echo $item['username']; ?></td>
                                                <td><?php echo $item['bet'] / 100; ?> bits</td>
                                                <td>
                                                    <?php if ($item['profit'] > 0) : ?>
                                                        <span class="win-text">+<?php echo $item['profit'] / 100; ?>
                                                            bits</span>
                                                    <?php elseif ($item['profit'] == 0): ?>
                                                        <span class="neutral-text"><?php echo $item['profit'] / 100; ?>
                                                            bits</span>
                                                    <?php else: ?>
                                                        <span class="lose-text"><?php echo $item['profit'] / 100; ?>
                                                            bits</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <tr class="lose">
                                                <td><?php echo $item['username']; ?></td>
                                                <td><?php echo $item['bet'] / 100; ?> bits</td>
                                                <td>
                                                    <?php if ($item['profit'] > 0) : ?>
                                                        <span class="win-text">+<?php echo $item['profit'] / 100; ?>
                                                            bits</span>
                                                    <?php elseif ($item['profit'] == 0): ?>
                                                        <span class="neutral-text"><?php echo $item['profit'] / 100; ?>
                                                            bits</span>
                                                    <?php else: ?>
                                                        <span class="lose-text"><?php echo $item['profit'] / 100; ?>
                                                            bits</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="game_history_med" class="overflowable">
                            <table id="game_history_table_large" class="highlight">
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
                                            <a href="<?php echo $base_dir; ?>game-info/<?php echo $item["game_id"] ?>"
                                               target="_blank"><?php echo $item["game_id"] ?></a>
                                        </td>
                                        <td><?php echo $item['amount'] / 100; ?> bits</td>
                                        <td>
                                            <div class='chip yellow'><?php echo $item['winner_number']; ?></div>
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


            <!-- Medium and large only -->
            <div class="col l8 m6 s12">
                <?php if ($logged_in): ?>
                    <!--            Play card    -->
                    <div class="col l6 m12" id="play_med_col">
                        <div class="card z-depth-1" id="play_med_card">
                            <div class="card-tabs">
                                <ul id="play_tabs" class="tabs tabs-fixed-width">
                                    <li class="tab"><a id="textarea_selector" class="active" href="#textarea_div_med">Text</a>
                                    </li>
                                    <li class="tab"><a id="random_selector" href="#random_div_med">Random</a></li>
                                    <li class="tab"><a id="sequence_selector" href="#sequence_div_med">Sequence</a></li>
                                    <li class="tab hide-on-large-only"><a href="#numbers_div_med">Numbers</a></li>
                                </ul>
                            </div>

                            <div class="card-content top-pad-play">
                                <!--       Text Area Input          -->
                                <div id="textarea_div_med">
                                    <blockquote class="blockquote-orange w900">
                                        Each number costs 25 bits.
                                        Only 100 numbers per play allowed. Numbers
                                        must be
                                        between 1 and 50000.
                                    </blockquote>
                                    <div class="input-field col s12">
                                        <textarea id="numbers_textarea_med" class="materialize-textarea"></textarea>
                                        <label for="numbers_textarea_med"></label>
                                        <span class="helper-text" data-error="Invalid numbers">Type your numbers separated by spaces.</span>
                                    </div>
                                    <p class="center-align">
                                        <a class="waves-effect waves-light btn disabled amber darken-3"
                                           id="textarea_button_med">Bet</a>
                                    </p>
                                </div>

                                <!--   Random Input    -->
                                <div id="random_div_med">
                                    <blockquote class="blockquote-orange w900">
                                        Each number costs 25 bits.
                                        Only 100 numbers per play allowed. Numbers
                                        must be
                                        between 1 and 50000.
                                    </blockquote>
                                    <div class="input-field col s4">
                                        <input placeholder="Start" id="start_random_med" type="number"
                                               class="valid"
                                               value="1">
                                        <label for="start_random_med">Start range</label>
                                    </div>
                                    <div class="input-field col s4">
                                        <input placeholder="End" id="end_random_med" type="number"
                                               class="valid"
                                               value="200">
                                        <label for="end_random_med">End range</label>
                                    </div>
                                    <div class="input-field col s4">
                                        <input placeholder="How many numbers?" id="how_many_numbers_med"
                                               type="number"
                                               class="valid" value="25">
                                        <label for="how_many_numbers_med">Quantity</label>
                                    </div>
                                    <p class="center-align">
                                        <a class="waves-effect waves-light btn amber darken-3" id="random_button_med">Bet</a>
                                    </p>
                                </div>

                                <!--     Sequence Input                       -->
                                <div id="sequence_div_med">
                                    <blockquote class="blockquote-orange w900">
                                        Each number costs 25 bits.
                                        Only 100 numbers per play allowed. Numbers
                                        must be
                                        between 1 and 50000.
                                    </blockquote>
                                    <div class="input-field col m5 s6 offset-m1">
                                        <input placeholder="Start number" id="start_sequence_med"
                                               type="number"
                                               class="valid"
                                               value="1">
                                        <label for="start_sequence_med">Start Range</label>
                                    </div>
                                    <div class="input-field col m5 s6">
                                        <input placeholder="End number" id="end_sequence_med" type="number"
                                               class="valid"
                                               value="25">
                                        <label for="end_sequence_med">End Range</label>
                                    </div>
                                    <p class="center-align">
                                        <a class="waves-effect waves-light btn amber darken-3" id="sequence_button_med">Bet</a>
                                    </p>
                                </div>

                                <!--Numbers Tab-->
                                <div id="numbers_div_med">
                                    <span id="count_numbers_small"
                                          class="card-title"><b><?php echo $numbers_title; ?></b></span>
                                    <div id="numbers_list_small" class="overflowable
                                     <?php if (empty($numbers_list_result))
                                        echo "valign-wrapper"; ?>">
                                        <?php
                                        if (!empty($numbers_list_result)) {
                                            foreach ($numbers_list_result as $item) {
                                                echo '<div class="chip small-chip yellow"><b>' . $item['number_id'] . '</b></div>';
                                            }
                                        } else {
                                            echo "<div class='centerWrap' style='width: 100%;'>
<div class='centeredDiv'><span class='h7Span'><i class='material-icons small left'>mood_bad</i> Maybe you should get some numbers</span></div>
</div>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Structure -->
                            <div id="confirm_numbers_modal_med" class="modal modal-fixed-footer">
                                <div class="modal-content">
                                    <h4>Check your numbers</h4>
                                    <p><span class="subText"
                                             id="count_numbers_confirm_med">0 numbers (0 bits)</span><br>
                                        <span class="balance-alert hidden" id="insufficient_balance_med">Insufficient balance</span>
                                    </p>
                                    <div id="confirmation_numbers_med"></div>
                                </div>
                                <div class="modal-footer">
                                    <a id="play_button_med"
                                       class="modal-action modal-close waves-effect waves-orange btn-flat">Confirm</a>
                                    <a href="#!"
                                       class="modal-action modal-close waves-effect waves-orange btn-flat">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col l6 hide-on-med-and-down">
                        <!--            Numbers card  -->
                        <div id="numbers_card_med" class="card z-depth-1">
                            <div class="card-content" id="card-numbers-content">
                                <span id="count_numbers_med"
                                      class="card-title"><b><?php echo $numbers_title; ?></b></span>
                                <div id="numbers_list_med" class="overflowable
                                     <?php if (empty($numbers_list_result))
                                    echo "valign-wrapper"; ?>">
                                    <?php
                                    if (!empty($numbers_list_result)) {
                                        foreach ($numbers_list_result as $item) {
                                            echo '<div class="chip small-chip yellow"><b>' . $item['number_id'] . '</b></div>';
                                        }
                                    } else {
                                        echo "<div class='centerWrap' style='width: 100%;'>
<div class='centeredDiv'><span class='h7Span'><i class='material-icons small left'>mood_bad</i> Maybe you should get some numbers</span></div>
</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div id="not-logged-play" class="col s12">
                        <div class="row"></div>
                        <div class="row">
                            <div class="col s12">
                                <p class="center-align"><a class="waves-effect waves-light btn amber darken-3"
                                                           href="<?php echo $base_dir; ?>login">Login to play</a><br>
                                    <a href="<?php echo $base_dir; ?>registration">or register</a></p>

                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="col s12" id="chat-col">
                    <div class="card" id="chat-card">
                        <div class="card-content" id="chat-card-content">
                            <!-- Chat -->
                            <div id="chat-space">
                                <ul id="chat-messages">

                                    <li><b>Frank:</b> Welcome!</li>
                                </ul>
                            </div>
                            <?php if ($logged_in): ?>
                                <div id="chat-input-line" class="row">
                                    <input placeholder="Enter your message here" id="input-chat" class="input-chat"
                                           type="text"
                                           maxlength="180">
                                    <button
                                            class="btn amber darken-3 btn-no-marg" id="chat-send"><i
                                                class="material-icons">send</i>
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="row"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row hide-on-med-and-up">
            <div class="col s12">
                <div class="col s12">
                    <div class="card z-depth-1">
                        <div class="card-tabs">
                            <ul class="tabs tabs-fixed-width">
                                <li class="tab"><a class="active" href="#last_game_small">Last Game</a></li>
                                <li class="tab"><a href="#game_history_small">Game History</a></li>
                            </ul>
                        </div>
                        <div class="card-content" id="game_info_card_content">
                            <div id="last_game_small">
                                <div id="last_game_header">
                                    <p>
                                        <a id="game_link_small"
                                           href="<?php echo $base_dir; ?>game-info/<?php echo $last_game; ?>"
                                           target="_blank">Game #<span
                                                    id="last_game_number_small"><?php echo $last_game; ?></span></a>
                                    </p>
                                    <div><b>Winner number: </b>
                                        <div class="chip yellow">
                                            <span id="last_winner_number_small"><b><?php echo $last_winner_number; ?></b></span>
                                        </div>
                                    </div>
                                    <p>
                                        <b>Jackpot: </b><span id="last_jackpot_small">
                            <?php echo $last_jackpot; ?>
                            </span> bits
                                    </p>
                                </div>
                                <div id="last_game_small_table_container" class="overflowable top-buffer-15">
                                    <table id="last_game_table_small" class="bordered">
                                        <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Bet</th>
                                            <th>Profit</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($players_row as $item) :?>
                                            <?php if ($item["win"] == 1): ?>
                                                <tr class="win">
                                                    <td><?php echo $item['username']; ?></td>
                                                    <td><?php echo $item['bet'] / 100; ?> bits</td>
                                                    <td>
                                                        <?php if ($item['profit'] > 0) : ?>
                                                            <span class="win-text">+<?php echo $item['profit'] / 100; ?>
                                                                bits</span>
                                                        <?php elseif ($item['profit'] == 0): ?>
                                                            <span class="neutral-text"><?php echo $item['profit'] / 100; ?>
                                                                bits</span>
                                                        <?php else: ?>
                                                            <span class="lose-text"><?php echo $item['profit'] / 100; ?>
                                                                bits</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <tr class="lose">
                                                    <td><?php echo $item['username']; ?></td>
                                                    <td><?php echo $item['bet'] / 100; ?> bits</td>
                                                    <td>
                                                        <?php if ($item['profit'] > 0) : ?>
                                                            <span class="win-text">+<?php echo $item['profit'] / 100; ?>
                                                                bits</span>
                                                        <?php elseif ($item['profit'] == 0): ?>
                                                            <span class="neutral-text"><?php echo $item['profit'] / 100; ?>
                                                                bits</span>
                                                        <?php else: ?>
                                                            <span class="lose-text"><?php echo $item['profit'] / 100; ?>
                                                                bits</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div id="game_history_small" class="overflowable">
                                <table id="game_history_table_small" class="highlight">
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
                                                <a href="<?php echo $base_dir; ?>game-info/<?php echo $item["game_id"] ?>"
                                                   target="_blank"><?php echo $item["game_id"] ?></a>
                                            </td>
                                            <td><?php echo $item['amount'] / 100; ?> bits</td>
                                            <td>
                                                <div class='chip yellow'><?php echo $item['winner_number']; ?></div>
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
            </div>
        </div>
    </div>
</main>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
<script src="js/autobahn.js"></script>
<script>
    var server_off_minutes = 360;
    var offset = new Date().getTimezoneOffset();
    var to = server_off_minutes - offset;
    var times_array = [];
    var users_array = [];
    var messages_array = [];
    <?php
    $chat_append = "";
    foreach ($messages as $item) {
        echo "times_array.push(new Date('" . $item['sentat'] . "')); ";
        echo "users_array.push('" . $item['username'] . "');";
        echo "messages_array.push('" . $item['message'] . "');";
    }

    ?>
    var chat_append = "";
    for (var i = 0; i < times_array.length; i++) {
        times_array[i].setMinutes(times_array[i].getMinutes() + to);
        var minute = times_array[i].getMinutes();
        if (minute < 10) {
            minute = "0" + minute;
        }
        chat_append += "<li><b>" + users_array[i] + " (" + times_array[i].getHours() + ":" +
            minute + "): </b>" + messages_array[i] + "</li>";
    }

    var chat_messages = $("#chat-messages");
    chat_messages.prepend(chat_append);
    var numbersGlobal = <?php
        if ($logged_in) {
            echo json_encode($arrayOfNumbers);
        } else {
            echo "\"\"";
        }?>;
</script>
<script src="js/index-script.js"></script>
<?php include "inc/footer.php"; ?>



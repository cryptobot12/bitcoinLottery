<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/22/2017
 * Time: 10:32 PM
 */
session_start();

try {
    $servername = "localhost";
    $conn = new PDO("mysql:host=$servername;dbname=lottery", "root", "5720297Ff");
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    //Getting last game information
    $stmt = $conn->prepare('SELECT game_id, amount, winner_number FROM game ORDER BY game_id DESC LIMIT 1, 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $last_game = $row['game_id'];
    $jackpot = $row['amount'] / 100;
    $winner_number = $row['winner_number'];

    //Getting profit for winners
    $stmt = $conn->prepare('SELECT COUNT(win) AS number_of_w FROM gamexuser 
                                     WHERE game_id = :game_id
                                     AND win = 1');
    $stmt->execute(array('game_id' => $last_game));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $profit_winners = $jackpot * 100 / $row['number_of_w'];

    //Selecting winners
    $stmt = $conn->prepare('SELECT u.username, COUNT(nu.number_id) * 5000 AS bet,
                                        (:profit_winners - COUNT(nu.number_id) * 5000 ) AS profit
                                        FROM user as u
                                        INNER JOIN gamexuser AS gu
                                        ON u.user_id = gu.user_id
                                        INNER JOIN numberxuser AS nu
                                        ON u.user_id = nu.user_id
                                        AND nu.user_id = gu.user_id
                                        AND nu.game_id = gu.game_id
                                        WHERE gu.win = 1
                                        AND gu.game_id = :game_id
                                        GROUP BY u.username
                                        ORDER BY bet DESC
                                        LIMIT 10');

    $stmt->execute(array('game_id' => $last_game, 'profit_winners' => $profit_winners));

    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<p><a id="gameLink" href="game_info.php?game_id=' . $last_game . '" target="_blank">Game #<span id="gameNumberLast">' . $last_game . '</span></a></p>';
    echo '<div><b>Winner number: </b><div class="chip"><span id="winnerNumberLast">' . $winner_number . '</span></div></div>';
    echo '<p><b>Jackpot: </b><span id="jackpotLast">' . $jackpot . '</span> bits</p>';
    echo '<table id="lastGameTable" class="bordered responsive-table">
            <thead>
            <tr>
              <th>User</th>
              <th>Bet</th>
              <th>Profit</th>
            </tr>
            </thead>
            <tbody>';

    foreach ($row as $item){

        echo '<tr class="win"><td>' .
            $item['username'] . '</td><td>' .
            ($item['bet'] / 100) . ' bits</td><td>';

        if ($item['profit'] > 0)
            echo '<span class="win-text">+';
        elseif ($item['profit'] == 0)
            echo '<span class="neutral-text">';
        else
            echo '<span class="lose-text">';
        echo    ($item['profit'] / 100) . ' bits</span></td></tr>';
    }

    //Selecting losers
    $stmt = $conn->prepare('SELECT u.username, COUNT(number_id) * 50 AS profit
                                    FROM user as u
                                    INNER JOIN gamexuser AS gu
                                    ON u.user_id = gu.user_id
                                    INNER JOIN numberxuser AS nu
                                    ON u.user_id = nu.user_id
                                    AND nu.user_id = gu.user_id
                                    AND nu.game_id = gu.game_id
                                    WHERE gu.win = 0
                                    AND gu.game_id = :game_id
                                    ORDER BY profit DESC
                                    LIMIT 10');

    $stmt->execute(array('game_id' => $last_game));
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
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}
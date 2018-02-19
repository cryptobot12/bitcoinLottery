<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/22/2017
 * Time: 12:34 PM
 */
session_start();

include "../connect.php";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    //Selecting current game
    $stmt = $conn->prepare('SELECT game_id, amount FROM game ORDER BY game_id DESC, game_date DESC LIMIT 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_game = $row['game_id'];
    $bonus = $row['amount'];
    echo "Current game: " . $current_game . "<br>";
    echo "Bonus: " . $bonus . "<br><br>";


    //Counting players
    $stmt = $conn->prepare('SELECT COUNT(DISTINCT user_id) AS number_of_players FROM numberxuser WHERE game_id = :game_id');
    $stmt->execute(array('game_id' => $current_game));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $players_in_current_game = $row['number_of_players'];
    echo "Number of players in this game: " . $players_in_current_game . "<br>";

    if ($players_in_current_game > 1) {

        //New game
        echo "Creating new game...<br>";
        $stmt = $conn->prepare('INSERT INTO game(game_date, winner_number, amount) VALUES 
                                  (current_timestamp, 0, 0)');
        $stmt->execute();

        //Increase number of games (history stats)
        echo "Increasing number of games...<br>";
        $stmt = $conn->prepare('UPDATE stats SET games_played = games_played + 1');
        $stmt->execute();

        //Update number of players
        echo "Increasing number of players...<br>";
        $stmt = $conn->prepare('UPDATE game SET number_of_players = :number_of_players
                                          WHERE game_id = :game_id');
        $stmt->execute(array('number_of_players' => $players_in_current_game, 'game_id' => $current_game));

        //Getting winner number
        $stmt = $conn->prepare('SELECT nxf.number_id
                                        FROM (SELECT number_id, COUNT(number_id) AS frequency FROM numberxuser
                                        WHERE game_id = :game_id1
                                        GROUP BY number_id
                                        HAVING frequency <= 30) AS nxf
                                        INNER JOIN
                                        (SELECT frequency, COUNT(frequency) AS fxf FROM(
                                        SELECT number_id, COUNT(number_id) AS frequency FROM numberxuser
                                        WHERE game_id = :game_id2
                                        GROUP BY number_id
                                        HAVING frequency <= 30) AS sometable
                                        GROUP BY frequency) AS fxft
                                        ON fxft.frequency = nxf.frequency
                                        ORDER BY fxft.fxf ASC, nxf.frequency ASC, nxf.number_id ASC
                                        LIMIT 1');
        $stmt->execute(array('game_id1' => $current_game, 'game_id2' => $current_game));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $winner_number = $row['number_id'];
        echo "Winner number: " . $winner_number . "<br>";

        //Number of winners
        $stmt = $conn->prepare('SELECT COUNT(user_id) AS n_of_w FROM numberxuser
                                          WHERE number_id = :winner_number
                                          AND game_id = :game_id');
        $stmt->execute(array('winner_number' => $winner_number, 'game_id' => $current_game));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $number_of_winners = $row['n_of_w'];
        echo "Number of winners: " . $number_of_winners . "<br>";

        //Calculating jackpot and how much each receives
        $stmt = $conn->prepare('SELECT COUNT(*) AS jackpot FROM numberxuser WHERE game_id = :game_id');
        $stmt->execute(array('game_id' => $current_game));
        $jackpot = $stmt->fetchColumn() * 9500 + $bonus;
        echo "Jackpot: " . $jackpot . "<br>";

        $each_receives = floor($jackpot / $number_of_winners);
        echo "Each receives: " . $each_receives . "<br>";
        $bonus = $jackpot - ($each_receives * $number_of_winners); //Bonus is added to next game
        echo "Bonus: " . $bonus . "<br>";

        //Updating new game bonus
        $stmt = $conn->prepare('SELECT game_id FROM game ORDER BY game_id DESC, game_date DESC LIMIT 1');
        $stmt->execute();
        echo "Selecting new game...<br>";
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $the_new_game = $row['game_id'];
        echo "New game id: " . $the_new_game . "<br>";
        $stmt = $conn->prepare('UPDATE game SET amount = :bonus WHERE game_id = :game_id');
        $stmt->execute(array('bonus' => $bonus, 'game_id' => $the_new_game));
        echo "Updating new game bonus...<br>";

        /****************** Here you should add what to do with the 5% of the money not taken by the users *********/


        /************************************************************************************************************/

        //Updating max_jackpot and gross profit (history)
        $stmt = $conn->prepare('SELECT max_jackpot FROM stats');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $max_jackpot = $result['max_jackpot'];
        echo "Max jackpot: " . $max_jackpot . "<br>";

        if ($jackpot > $max_jackpot) {
            $stmt = $conn->prepare('UPDATE stats SET max_jackpot = :jackpot');
            $stmt->execute(array('jackpot' => $jackpot));
            echo "Updating max jackpot...<br>";
        }

        $stmt = $conn->prepare('UPDATE stats SET gross_profit = gross_profit + :jackpot');
        $stmt->execute(array('jackpot' => $jackpot));
        echo "Increasing gross profit...<br>";

        //Saving game history
        $stmt = $conn->prepare('UPDATE game SET game_date = current_timestamp, winner_number = :winner_number,
            amount = :amount WHERE game_id = :game_id');
        $stmt->execute(array('winner_number' => $winner_number, 'amount' => $jackpot, 'game_id' => $current_game));
        echo "Saving game history...<br>";

        //Giving profit to winners, updating net profit...
        $stmt = $conn->prepare('UPDATE user AS u
              INNER JOIN gamexuser AS gu ON u.user_id = gu.user_id
              INNER JOIN numberxuser AS nu ON u.user_id = nu.user_id
            SET u.balance = u.balance + :profit, u.net_profit = u.net_profit + :net_profit,
              gu.profit = :profit2, gu.win = 1
            WHERE nu.user_id = :winner_number
            AND gu.game_id = :game_id');
        $stmt->execute(array('profit' => $each_receives, 'net_profit' => $each_receives, 'profit2' => $each_receives,
            'winner_number' => $winner_number, 'game_id' => $current_game));
        echo "Giving profit to winners...(user)<br>";

        $jackpot_last = $jackpot / 100;
        //After new game

        echo "Broadcasting...<br>";
        //Selecting actually current game
        $stmt = $conn->prepare('SELECT game_id, amount FROM game ORDER BY game_id DESC, game_date DESC LIMIT 1');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $actually_current_game = $result['game_id'];
        $bonus = $result['amount'];

        //Selecting jackpot from new game
        $stmt = $conn->prepare('SELECT COUNT(*) AS jackpot FROM numberxuser WHERE game_id = :game_id');
        $stmt->execute(array('game_id' => $actually_current_game));
        $jackpot = ($stmt->fetchColumn() * 9500 + $bonus) / 100;

        //Selecting games history
        $stmt = $conn->prepare('SELECT game_id, date_format(game_date, \'%h:%i %p\') AS time, winner_number, amount FROM game
                                      WHERE amount > 0
                                      ORDER BY game_id DESC, game_date DESC LIMIT 20');
        $stmt->execute();

        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arrayOfGames = array();

        foreach ($row as $item) {
            $rowArray = array('game_id' => $item['game_id'], 'timedate' => $item['time'],
                'winner_number' => $item['winner_number'], 'amount' => ($item['amount'] / 100));
            array_push($arrayOfGames, $rowArray);
        }

        //Selecting winners
        $stmt = $conn->prepare('SELECT u.username, gu.win, gu.bet, gu.profit 
     FROM user AS u 
     INNER JOIN gamexuser AS gu
     ON u.user_id = gu.user_id
     WHERE gu.game_id = :game_id
     AND gu.win = 1');

        $stmt->execute(array('game_id' => $current_game));
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $n_of_wrow = count($row);

        $arrayOfWinners = array();
        foreach ($row as $item) {
            $rowArray = array('username' => $item['username'], 'bet' => ($item['bet'] / 100),
                'profit' => ($item['profit'] / 100));
            array_push($arrayOfWinners, $rowArray);
        }

        //Selecting losers
        $stmt = $conn->prepare('SELECT u.username, gu.win, gu.bet, gu.profit 
     FROM user AS u 
     INNER JOIN gamexuser AS gu
     ON u.user_id = gu.user_id
     WHERE gu.game_id = :game_id
     AND gu.win = 0');


        $stmt->execute(array('game_id' => $current_game));
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arrayOfLosers = array();
        if (!empty($row)) {

            foreach ($row as $item) {

                if ($item['username'] <> null) {
                    $rowArray = array('username' => $item['username'], 'profit' => $item['profit']);
                    array_push($arrayOfLosers, $rowArray);
                }
            }
        }

        //Broadcasting
        $entryData = array('category' => 'all', 'option' => 2, 'jackpot' => $jackpot, 'games' => $arrayOfGames,
            'last_game_number' => $current_game, 'last_winner_number' => $winner_number, 'last_jackpot' => $jackpot_last,
            'winners' => $arrayOfWinners, 'losers' => $arrayOfLosers);

        var_dump($entryData);

        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:5555");

        $socket->send(json_encode($entryData));

    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} catch (ZMQSocketException $e) {

    echo $e->getMessage();

}



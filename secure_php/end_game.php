<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/22/2017
 * Time: 12:34 PM
 */
session_start();

$servername = "localhost";

try {
    $conn = new PDO("mysql:host=$servername;dbname=lottery", "root", "5720297Ff");
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT game_id FROM game ORDER BY game_id DESC, timedate DESC LIMIT 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_game = $row['game_id'];

    $stmt = $conn->prepare('SELECT COUNT(*) FROM numberxuser WHERE game_id = :game_id');
    $stmt->execute(array('game_id' => $current_game));
    $columns = $stmt->fetchColumn();

    if ($columns > 0) {

        //Increase number of games (history stats)
        $stmt = $conn->prepare('UPDATE stats SET games_played = games_played + 1');
        $stmt->execute();

        //Getting winner number and number of winners
        $stmt = $conn->prepare('SELECT COUNT(user_id) AS reps, number_id 
                                    FROM numberxuser
                                    WHERE game_id = :game_id
                                    GROUP BY number_id ORDER BY reps, number_id');
        $stmt->execute(array('game_id' => $current_game));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $winner_number = $result['number_id'];
        $number_of_winners = $result['reps'];

        //Calculating jackpot and how much each receives
        $stmt = $conn->prepare('SELECT COUNT(*) AS jackpot FROM numberxuser WHERE game_id = :game_id');
        $stmt->execute(array('game_id' => $current_game));
        $jackpot = $stmt->fetchColumn() * 3000;

        $each_receives = floor($jackpot / $number_of_winners);

        //Updating max_jackpot and gross profit
        $stmt = $conn->prepare('SELECT max_jackpot FROM stats');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $max_jackpot = $result['max_jackpot'];

        if ($jackpot > $max_jackpot)
        {
            $stmt = $conn->prepare('UPDATE stats SET max_jackpot = :jackpot');
            $stmt->execute(array('jackpot' => $jackpot));
        }

        $stmt = $conn->prepare('UPDATE stats SET gross_profit = gross_profit + :jackpot');
        $stmt->execute(array('jackpot' => $jackpot));

        //Giving profit to winners, updating net profit, and games played
        $stmt = $conn->prepare('UPDATE user SET balance = balance + :profit, net_profit = net_profit + :net_profit,
                              games_played = games_played + 1
                              WHERE user_id IN (SELECT user_id FROM numberxuser WHERE number_id = :winner_number
                              AND game_id = :game_id)');
        $stmt->execute(array('profit' => $each_receives, 'net_profit' => $each_receives, 'winner_number' => $winner_number, 'game_id' => $current_game));

        //Saving game history
        $stmt = $conn->prepare('UPDATE game SET timedate = current_timestamp, winner_number = :winner_number,
            amount = :amount WHERE game_id = :game_id');
        $stmt->execute(array('winner_number' => $winner_number, 'amount' => $jackpot, 'game_id' => $current_game));

        //Selecting losers
        $stmt = $conn->prepare('SELECT DISTINCT user_id FROM numberxuser 
                                          WHERE number_id <> :winner_number
                                          AND game_id = :game_id');
        $stmt->execute(array('winner_number' => $winner_number, 'game_id' => $current_game));
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //Saving losers to history
        foreach ($row as $item) {
            $stmt = $conn->prepare('INSERT INTO gamexuser(game_id, user_id, win) VALUES (:game_id , :user_id, 0)');
            $stmt->execute(array('game_id' => $current_game, 'user_id' => $item['user_id']));
        }

        //Selecting winners
        $stmt = $conn->prepare('SELECT DISTINCT user_id FROM numberxuser 
                                          WHERE number_id = :winner_number
                                          AND game_id = :game_id');
        $stmt->execute(array('winner_number' => $winner_number, 'game_id' => $current_game));
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //Saving winners to history
        foreach ($row as $item) {
            //Deleting winners from list of users
            $stmt = $conn->prepare('DELETE FROM gamexuser WHERE user_id = :user_id AND game_id = :game_id');
            $stmt->execute(array('game_id' => $current_game, 'user_id' => $item['user_id']));

            //Inserting winners
            $stmt = $conn->prepare('INSERT INTO gamexuser(game_id, user_id, win) VALUES (:game_id , :user_id, 1)');
            $stmt->execute(array('game_id' => $current_game, 'user_id' => $item['user_id']));
        }

        //Selecting losers to update net profit and games played
        $stmt = $conn->prepare('SELECT gu.user_id, COUNT(nu.number_id) * -3000
                      AS profit
                    FROM gamexuser AS gu
                    INNER JOIN numberxuser AS nu
                    ON gu.user_id = nu.user_id
                    AND nu.user_id = gu.user_id
                    AND nu.game_id = gu.game_id
                    WHERE gu.game_id = :game_id
                    AND win = 0
                    GROUP BY gu.user_id
                    ORDER BY profit DESC');
        $stmt->execute(array('game_id' => $current_game));
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //Incrementing number of games played and net profit
        foreach ($row as $item) {
            $user_id = $item['user_id'];
            $profit = $item['profit'];

            $stmt = $conn->prepare('UPDATE user SET games_played = (games_played + 1),
                                                net_profit = (net_profit + :profit)
                                              WHERE user_id = :user_id');
            $stmt->execute(array('user_id' => $user_id, 'profit' => $profit));
        }

        //Selecting number of plays
        $stmt = $conn->prepare('SELECT COUNT(game_id) AS row_count FROM gamexuser WHERE game_id = :game_id');
        $stmt->execute(array('game_id' => $current_game));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $rowCount = $result['row_count'];

        //Updating number of plays
        $stmt = $conn->prepare('UPDATE stats SET total_plays = total_plays + :new_plays');
        $stmt->execute(array('new_plays' => $rowCount));

        //New game
        $stmt = $conn->prepare('INSERT INTO game(timedate, winner_number, amount) VALUES 
                                  (current_timestamp, 0, 0)');
        $stmt->execute();

        $jackpot_last = $jackpot / 100;
        //After new game

        //Selecting actually current game
        $stmt = $conn->prepare('SELECT game_id FROM game ORDER BY game_id DESC, timedate DESC LIMIT 1');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $actually_current_game = $result['game_id'];

        //Update jackpot
        $stmt = $conn->prepare('SELECT COUNT(*) AS jackpot FROM numberxuser WHERE game_id = :game_id');
        $stmt->execute(array('game_id' => $actually_current_game));
        $jackpot = $stmt->fetchColumn() * 30;

        //Selecting games history
        $stmt = $conn->prepare('SELECT game_id, date_format(timedate, \'%h:%i %p\') AS time, winner_number, amount FROM game
                                      WHERE amount > 0
                                      ORDER BY game_id DESC, timedate DESC LIMIT 20');
        $stmt->execute();

        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arrayOfGames = array();
        foreach ($row as $item){
            $rowArray = array('game_id' => $item['game_id'], 'timedate' => $item['time'],
                'winner_number' => $item['winner_number'], 'amount' => ($item['amount'] / 100));
            array_push($arrayOfGames, $rowArray);
        }

        //Selecting winners
        $stmt = $conn->prepare('SELECT u.username, COUNT(nu.number_id) * 3000 AS bet,
                                        (:profit_winners - COUNT(nu.number_id) * 3000 ) AS profit
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
                                        ORDER BY bet DESC');

        $stmt->execute(array('game_id' => $current_game, 'profit_winners' => $each_receives));
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arrayOfWinners = array();
        foreach ($row as $item){
            $rowArray = array('username' => $item['username'], 'bet' => ($item['bet'] / 100),
                'profit' => ($item['profit'] / 100));
            array_push($arrayOfWinners, $rowArray);
        }

        //Selecting losers
        $stmt = $conn->prepare('SELECT u.username, COUNT(number_id) * 30 AS profit
                                    FROM user as u
                                    INNER JOIN gamexuser AS gu
                                    ON u.user_id = gu.user_id
                                    INNER JOIN numberxuser AS nu
                                    ON u.user_id = nu.user_id
                                    AND nu.user_id = gu.user_id
                                    AND nu.game_id = gu.game_id
                                    WHERE gu.win = 0
                                    AND gu.game_id = :game_id
                                    ORDER BY profit DESC');

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

        $entryData = array('category' => 'all','reload' => 1, 'jackpot' => $jackpot, 'games' => $arrayOfGames,
            'last_game_number' => $current_game, 'last_winner_number' => $winner_number, 'last_jackpot' => $jackpot_last,
            'winners' => $arrayOfWinners, 'losers' =>$arrayOfLosers);

        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:5555");

        $socket->send(json_encode($entryData));

    }

}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}


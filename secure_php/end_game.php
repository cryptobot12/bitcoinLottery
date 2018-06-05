<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/22/2017
 * Time: 12:34 PM
 */

require_once '/var/www/html/bitcoinLottery/vendor/autoload.php';
include "../globals.php";

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

        $driver = new \Nbobtc\Http\Driver\CurlDriver();
        $driver
            ->addCurlOption(CURLOPT_VERBOSE, true)
            ->addCurlOption(CURLOPT_STDERR, '/var/logs/curl.err');

        $client = new \Nbobtc\Http\Client('http://puppetmaster:vz6qGFsHBv5auSSDhTPWPktVu@localhost:18332');
        $client->withDriver($driver);


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
        echo "Updating number of players in current game...<br>";
        $stmt = $conn->prepare('UPDATE game SET number_of_players = :number_of_players
                                          WHERE game_id = :game_id');
        $stmt->execute(array('number_of_players' => $players_in_current_game, 'game_id' => $current_game));

        //Getting winner number
        $stmt = $conn->prepare('SELECT nxf.number_id
                                        FROM (SELECT number_id, COUNT(number_id) AS frequency FROM numberxuser
                                        WHERE game_id = :game_id1
                                        GROUP BY number_id) AS nxf
                                        INNER JOIN
                                        (SELECT frequency, COUNT(frequency) AS fxf FROM(
                                        SELECT number_id, COUNT(number_id) AS frequency FROM numberxuser
                                        WHERE game_id = :game_id2
                                        GROUP BY number_id) AS sometable
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
        $stmt = $conn->prepare('SELECT balance FROM balances WHERE username = :username');
        $stmt->execute(array('username' => 'jackpot'));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $jackpot_in_satoshis = $row['balance'];
        $jackpot_in_bits = $jackpot_in_satoshis / 100;

        echo "Jackpot: " . ($jackpot_in_bits) . " bits<br>";

        $each_receives = floor($jackpot_in_satoshis / $number_of_winners);
        echo "Each receives: " . $each_receives / 100 . " bits<br>";

        //Updating max_jackpot and gross profit (history)
        $stmt = $conn->prepare('SELECT max_jackpot FROM stats');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $max_jackpot = $result['max_jackpot'];
        echo "Max jackpot: " . $max_jackpot . "<br>";

        if ($jackpot_in_satoshis > $max_jackpot) {
            $stmt = $conn->prepare('UPDATE stats SET max_jackpot = :jackpot');
            $stmt->execute(array('jackpot' => $jackpot_in_satoshis));
            echo "Updating max jackpot...<br>";
        }

        $stmt = $conn->prepare('UPDATE stats SET gross_profit = gross_profit + :jackpot');
        $stmt->execute(array('jackpot' => $jackpot_in_satoshis));
        echo "Increasing gross profit...<br>";

        //Saving game history
        $stmt = $conn->prepare('UPDATE game SET game_date = current_timestamp, winner_number = :winner_number,
            amount = :amount WHERE game_id = :game_id');
        $stmt->execute(array('winner_number' => $winner_number, 'amount' => $jackpot_in_satoshis, 'game_id' => $current_game));
        echo "Saving game history...<br>";

        //Giving profit to winners, updating net profit...
        $stmt = $conn->prepare('UPDATE user AS u
              INNER JOIN gamexuser AS gu ON u.user_id = gu.user_id
              INNER JOIN numberxuser AS nu ON u.user_id = nu.user_id
              AND gu.game_id = nu.game_id
            SET u.net_profit = u.net_profit + :net_profit,
              gu.profit = gu.profit + :profit2, gu.win = 1
            WHERE nu.number_id = :winner_number
            AND gu.game_id = :game_id');
        $stmt->execute(array('net_profit' => $each_receives, 'profit2' => $each_receives,
            'winner_number' => $winner_number, 'game_id' => $current_game));
        echo "Giving profit to winners...(user)<br>";

        /*****BITCOIN TRANSACTION *********/

        $stmt = $conn->prepare('SELECT username
FROM user
  INNER JOIN gamexuser
    ON user.user_id = gamexuser.user_id
WHERE gamexuser.win = 1 AND game_id = :game_id');
        $stmt->execute(array('game_id' => $current_game));
        $winners_usernames = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $stmt = $conn->prepare('UPDATE balances SET balance = balance + :add WHERE username = :username');

        foreach ($winners_usernames as $winner) {
            echo $winner['username'] . "<br>";
            $command = new \Nbobtc\Command\Command('move', array("jackpot", $winner['username'], $each_receives / 100000000));

            /** @var \Nbobtc\Http\Message\Response */
            $response = $client->sendCommand($command);
            $stmt->execute(array('add' => $each_receives, 'username' => $winner['username']));

            echo "<br>";
        }

        $stmt = $conn->prepare('UPDATE balances SET balance = :balance WHERE username = :username');
        $stmt->execute(array('balance' => 0, 'username' => 'jackpot'));
        /*********************************/

        $jackpot_last = $jackpot_in_bits;
        //After new game

        echo "Broadcasting...<br>";

        /****TRANSFERRING BITCOIN FROM NEXT JACKPOT TO JACKPOT ****/

        $stmt = $conn->prepare('SELECT balance FROM balances WHERE username = :username');
        $stmt->execute(array('username' => 'next_jackpot'));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_jackpot_balance = $row['balance'];

        $next_jackpot_balance_in_bitcoin = $next_jackpot_balance / 100000000;


        $command = new \Nbobtc\Command\Command('move', array("nextjackpot", "jackpot", $next_jackpot_balance_in_bitcoin));
        /** @var \Nbobtc\Http\Message\Response */
        $response = $client->sendCommand($command);

        /*TO JACKPOT*/
        $stmt = $conn->prepare('UPDATE balances SET balance = :balance WHERE username = :username');
        $stmt->execute(array('balance' => $next_jackpot_balance, 'username' => 'jackpot'));

        $stmt = $conn->prepare('UPDATE balances SET balance = :balance WHERE username = :username');
        $stmt->execute(array('balance' => 0, 'username' => 'next_jackpot'));



        /**********************************************************/

        $stmt = $conn->prepare('SELECT balance FROM balances WHERE username = :username');
        $stmt->execute(array('username' => 'jackpot'));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $new_jackpot = $row['balance'] / 100;

        //Selecting games history
        $stmt = $conn->prepare('SELECT game_id, date_format(game_date, \'%h:%i %p\') AS time, winner_number, amount FROM game
                                      WHERE amount > 0
                                      ORDER BY game_id DESC, game_date DESC LIMIT 1');
        $stmt->execute();

        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arrayOfGames = array();

        foreach ($row as $item) {
            $rowArray = array('game_id' => $item['game_id'], 'timedate' => $item['time'],
                'winner_number' => $item['winner_number'], 'amount' => ($item['amount'] / 100));
            array_push($arrayOfGames, $rowArray);
        }

        //Selecting players
        $stmt = $conn->prepare('SELECT u.username_display AS username, gu.win AS win, gu.bet AS bet, gu.profit AS profit
     FROM user AS u 
     INNER JOIN gamexuser AS gu
     ON u.user_id = gu.user_id
     WHERE gu.game_id = :game_id
     ORDER BY win DESC, profit DESC, bet DESC, username ASC 
     LIMIT 30');

        $stmt->execute(array('game_id' => $current_game));
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arrayOfPlayers = array();

        foreach ($row as $item) {

                $rowArray = array('username' => $item['username'], 'win' => $item['win'], 'bet' => ($item['bet'] / 100),
                    'profit' => ($item['profit']) / 100);
                array_push($arrayOfPlayers, $rowArray);

        }

        //Broadcasting
        $entryData = array('category' => 'all', 'option' => 2, 'jackpot' => $new_jackpot, 'games' => $arrayOfGames,
            'last_game_number' => $current_game, 'last_winner_number' => $winner_number, 'last_jackpot' => $jackpot_last,
            'players' => $arrayOfPlayers);


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



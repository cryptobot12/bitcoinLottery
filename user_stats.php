<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/24/2017
 * Time: 10:16 AM
 */

$username = $_GET['user'];

try {
    $servername = "localhost";
    $conn = new PDO("mysql:host=$servername;dbname=lottery", "root", "5720297Ff");
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //echo "Connected successfully";

    $stmt = $conn->prepare('SELECT u.net_profit, u.games_played
                                      FROM user AS u
                                      WHERE u.username = :username');

    $stmt->execute(array('username' => $username));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $net_profit = $result['net_profit'];
    $games_played = $result['games_played'];

    echo 'User: ' . $username . "<br>" .
        'Net Profit: ' . $net_profit . "<br>" .
        'Games_played: ' . $games_played . "<br>" .
        "<table>
            <tr>
                <th>Game #</th>
                <th>Win</th>
                <th>Bet</th>
                <th>Profit</th>
                <th>Amount</th>
            </tr>";


    $stmt = $conn->prepare('SELECT gu.game_id, gu.win,
                                      COUNT(nu.number_id) * 3000 AS bet,
                                      CASE
                                      WHEN win = 1 THEN (ga.amount - COUNT(nu.number_id) * 3000)
                                      WHEN win = 0 THEN COUNT(nu.number_id) * -3000
                                      END AS profit, ga.amount
                                    FROM user AS u
                                    INNER JOIN gamexuser AS gu
                                    ON u.user_id = gu.user_id
                                    INNER JOIN numberxuser AS nu
                                    ON u.user_id = nu.user_id
                                    AND nu.user_id = gu.user_id
                                    AND nu.game_id = gu.game_id
                                    INNER JOIN game AS ga
                                    ON gu.game_id = ga.game_id
                                    WHERE u.username = :username
                                    GROUP BY gu.game_id
                                    ORDER BY game_id DESC
                                    LIMIT 50');

    $stmt->execute(array('username' => $username));
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($row as $item){

        echo "<tr>
                <td>" . $item['game_id'] ."</td>
                <td>" . $item['win'] . "</td>
                <td>" . $item['bet'] . "</td>
                <td>" . $item['profit'] . "</td>
                <td>" . $item['amount'] . "</td>
            </tr>";

    }

    echo "</table>";
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}
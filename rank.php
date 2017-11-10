<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/25/2017
 * Time: 6:16 PM
 */

try {
    $servername = "localhost";
    $conn = new PDO("mysql:host=$servername;dbname=lottery", "root", "5720297Ff");
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //echo "Connected successfully";
    $stmt = $conn->prepare('SELECT @curRank := @curRank + 1 AS rank, username, net_profit, games_played 
                        FROM user,(SELECT @curRank := 0) r 
                        ORDER BY net_profit DESC LIMIT 50');
    $stmt->execute();
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $rank = 1;

    echo "<table>
            <tr>
              <th>Rank</th>
              <th>User</th>
              <th>Net Profit</th>
              <th>Games Played</th>
            </tr>";

    foreach ($row as $item){
        echo "<tr>
            <td>" . $item['rank'] . "</td>
                <td>" . $item['username'] . "</td>" .
            "<td>" . $item['net_profit'] . "</td>" .
            "<td>" . $item['games_played'] . "</td>" .
            "</tr>";
        $rank++;
            }

    echo "</table>";
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}


<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/23/2017
 * Time: 3:00 PM
 */


try {
    $servername = "localhost";
    $conn = new PDO("mysql:host=$servername;dbname=lottery", "root", "5720297Ff");
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    // Selecting game history
    $stmt = $conn->prepare('SELECT game_id, date_format(timedate, \'%h:%i %p\') AS time, winner_number, amount FROM game
                                      WHERE amount > 0
                                      ORDER BY game_id DESC, timedate DESC LIMIT 20');

    $stmt->execute();

    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($row as $item){
        echo "<tr>
                <td>" . $item['game_id'] . "</td>" .
                "<td>" . ($item['amount'] / 100) . " bits</td>" .
                "<td><div class='chip'>" . $item['winner_number'] . "</div></td>" .
                "<td>" . $item['time'] . "</td>" .
             "</tr>";
    }
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}
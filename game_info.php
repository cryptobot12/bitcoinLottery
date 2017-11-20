<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/19/17
 * Time: 4:57 PM
 */

$servername = "localhost";
$game_id = $_GET['game_id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=lottery", "root", "5720297Ff");
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT amount, winner_number, date_format(timedate, \'%h:%i %p\') AS time FROM game 
                                     WHERE game_id = :game_id');
    $stmt->execute(array('game_id' => $game_id));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $amount = $row['amount'];
    $winner_number = $row['winner_number'];
    $timedate = $row['time'];

    echo "Game #" . $game_id . "<br>" .
         "Jackpot: " . ($amount / 100) . " bits<br>" .
         "Winner number: " . $winner_number . "<br>" .
         $timedate;

    $stmt = $conn->prepare('SELECT number_id, frequency, COUNT(frequency) AS fxf FROM(
                                        SELECT number_id, COUNT(number_id) AS frequency FROM numberxuser 
                                        WHERE game_id = :game_id
                                        GROUP BY number_id) AS data1
                                        WHERE frequency <= 30
                                        GROUP BY frequency
                                        ORDER BY fxf ASC
                                        LIMIT 10');
    $stmt->execute(array('game_id' => $game_id));
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<table>
            <tr>
                <th>Frequency</th>
                <th>Frequency of frequency</th>
                <th>Lowest number</th>
            </tr>';

    foreach ($row as $item) {
        echo '<tr>
                <td>' . $item['frequency'] . '</td>' .
               '<td>' . $item['fxf'] . '</td>' .
               '<td>' . $item['number_id'] . '</td>
              </tr>';
    }

    echo '</table>';

}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}

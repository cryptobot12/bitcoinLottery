<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/22/17
 * Time: 4:56 PM
 */

include "../globals.php";
$game = htmlspecialchars($_POST['game']);
$number = htmlspecialchars($_POST['number']);

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT user.username
                                    FROM user
                                    INNER JOIN
                                    numberxuser
                                    ON user.user_id = numberxuser.user_id
                                    WHERE numberxuser.number_id = :number
                                    AND numberxuser.game_id = :game');
    $stmt->execute(array('number' => $number, 'game' => $game));
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $arrayOfPlayers = array();
    foreach ($row as $item) {
        array_push($arrayOfPlayers, $item['username']);
    }

    $returnAjax = json_encode($arrayOfPlayers);
    echo $returnAjax;

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}


<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/24/2017
 * Time: 10:16 AM
 */
session_start();

$username = htmlspecialchars($_GET['user']);

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

    $stmt = $conn->prepare('SELECT
                                      gu.game_id,
                                      gu.win,
                                      COUNT(nu.number_id) * 5000 AS bet,
                                      CASE
                                      WHEN win = 1
                                        THEN (rec.received - COUNT(nu.number_id) * 5000)
                                      WHEN win = 0
                                        THEN COUNT(nu.number_id) * -5000
                                      END                        AS profit,
                                      ga.amount
                                    FROM user AS u
                                      INNER JOIN gamexuser AS gu
                                        ON u.user_id = gu.user_id
                                      INNER JOIN numberxuser AS nu
                                        ON u.user_id = nu.user_id
                                           AND nu.user_id = gu.user_id
                                           AND nu.game_id = gu.game_id
                                      INNER JOIN game AS ga
                                        ON gu.game_id = ga.game_id
                                      INNER JOIN
                                      (SELECT
                                         ceil(amount / COUNT(win)) AS received,
                                         game.game_id              AS game_id
                                       FROM gamexuser
                                         INNER JOIN game
                                           ON game.game_id = gamexuser.game_id
                                       WHERE win = 1
                                      GROUP BY game.game_id) AS rec
                                        ON rec.game_id = gu.game_id
                                    WHERE u.username = :username
                                    GROUP BY gu.game_id
                                    ORDER BY game_id DESC
                                    LIMIT 50');

    $stmt->execute(array('username' => $username));
    $rowTable = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Bitcoin</title>
        <!--    Jquery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

        <!-- Compiled and minified CSS -->
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <!-- Compiled and minified JavaScript -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
        <script src="js/autobahn.js"></script>

        <link href="css/style.css" rel="stylesheet">

        <!--Let browser know website is optimized for mobile-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    </head>
<body>
    <header>
        <?php include 'header.php'; ?>
        <div class="row top-buffer-15">
            <div class="col s4 offset-s4">
                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">search</i>
                        <input id="search_game" class="validate" type="number" ">
                        <label for="search_game">Username</label>
                    </div>
                </div>
            </div>
        </div>
    </header>
<main>
    <div class="container">
    <div class="row">
        <h3><b><?php echo $username; ?></b></h3>
        <h5><b>Net profit: </b><?php echo($net_profit / 100); ?> bits</h5>
        <h5><b>Games played: </b><?php echo $games_played ?></h5>
    </div>
    <table class="highlight">
    <thead>
    <tr>
        <th>Game #</th>
        <th>Bet</th>
        <th>Profit</th>
        <th>Jackpot</th>
    </tr>
    </thead>
    <tbody>
<?php
foreach ($rowTable as $item) : ?>

    <tr class="<?php
    if ($item['win'] == 1)
        echo 'win';
    else
        echo 'lose';
    ?>">

        <td><a href="<?php echo "game_info.php?game=" .$item['game_id'];?>"><?php echo $item['game_id']; ?></a></td>
        <td><?php echo $item['bet'] / 100; ?> bits</td>
        <td><?php

            if ($item['profit'] > 0)
                echo '<span class="win-text">+';
            elseif ($item['profit'] == 0)
                echo '<span class="neutral-text">';
            else
                echo '<span class="lose-text">';

            echo($item['profit'] / 100); ?> bits</span></td>
        <td><?php echo $item['amount'] / 100; ?> bits</td>
    </tr>


    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    </main>
    <?php include 'footer.php'; ?>
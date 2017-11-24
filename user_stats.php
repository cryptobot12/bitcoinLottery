<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/24/2017
 * Time: 10:16 AM
 */
session_start();

include "function.php";

$username = htmlspecialchars($_GET['user']);

function userStatsLink($user, $page = 1, $gaAsc = 2, $beAsc = 2, $prAsc = 2, $jaAsc = 2, $arrayOrd, $first)
{
    $pos = array_search($first, $arrayOrd);
    array_splice($arrayOrd, $pos, 1);
    array_unshift($arrayOrd, $first);
    echo "user_stats.php?user=$user&p=$page&ga=$gaAsc&be=$beAsc&pr=$prAsc&ja=$jaAsc&ord[]=$arrayOrd[0]&
ord[]=$arrayOrd[1]&ord[]=$arrayOrd[2]&ord[]=$arrayOrd[3]";
}

if (isset($_GET['p']) && !empty($_GET['p'])) {
    $page = htmlspecialchars($_GET['p']);
    filterOnlyNumber($page, 1, 1, 1);
} else
    $page = 1;

if (isset($_GET['ga']) && !empty($_GET['ga'])) {
    $gaAsc = htmlspecialchars($_GET['ga']);
    filterOnlyNumber($gaAsc, 2, 2, 1);
} else {
    $gaAsc = 2;
}

if (isset($_GET['be']) && !empty($_GET['be'])) {
    $beAsc = htmlspecialchars($_GET['be']);
    filterOnlyNumber($beAsc, 2, 2, 1);
} else {
    $beAsc = 2;
}

if (isset($_GET['pr']) && !empty($_GET['pr'])) {
    $prAsc = htmlspecialchars($_GET['pr']);
    filterOnlyNumber($prAsc, 2, 2, 1);
} else {
    $prAsc = 2;
}

if (isset($_GET['ja']) && !empty($_GET['ja'])) {
    $jaAsc = htmlspecialchars($_GET['ja']);
    filterOnlyNumber($jaAsc, 2, 2, 1);
} else {
    $jaAsc = 2;
}

if (isset($_GET['ord']) && !empty($_GET['ord'])) {
    $order = $_GET['ord'];
    filterArray($order, 4);
} else {
    $order = array(1, 2, 3, 4);
}

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

    if ($gaAsc == 2)
        $gaString = "gu.game_id ASC";
    else
        $gaString = "gu.game_id DESC";

    if ($beAsc == 2)
        $beString = "bet ASC";
    else
        $beString = "bet DESC";

    if ($prAsc == 2)
        $prString = "profit ASC";
    else
        $prString = "profit DESC";

    if ($jaAsc == 2)
        $jaString = "ga.amount ASC";
    else
        $jaString = "ga.amount DESC";

    $statement = 'SELECT
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
                                    ORDER BY ';

    for ($i = 0; $i <= 3; $i++) {
        if ($order[$i] == 1)
            $statement = $statement . $gaString;
        if ($order[$i] == 2)
            $statement = $statement . $beString;
        if ($order[$i] == 3)
            $statement = $statement . $prString;
        if ($order[$i] == 4)
            $statement = $statement . $jaString;

        if ($i < 3)
            $statement = $statement . ", ";
    }

    $statement = $statement . " LIMIT 50";

    $stmt = $conn->prepare($statement);

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
        <script>
            $(function () {

                var searchUser = $("#search_user");
                searchUser.on('keypress', function (e) {
                    if (e.which === 13) {
                        window.location.href = 'user_stats.php?user=' + searchUser.val();
                    }
                });
            });
        </script>

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
                    <input id="search_user" class="validate" type="text" ">
                    <label for="search_user">Username</label>
                </div>
            </div>
        </div>
    </div>
</header>
<main class="<?php if ($current_game == $game_id) echo 'valign-wrapper'; ?>">
    <div class="container">
        <?php if (count($rowTable) > 0): ?>
            <div class="row">
                <h3><b><?php echo $username; ?></b></h3>
                <h5><b>Net profit: </b><?php echo($net_profit / 100); ?> bits</h5>
                <h5><b>Games played: </b><?php echo $games_played ?></h5>
            </div>
            <table class="highlight">
                <thead>
                <tr>
                    <th><a href="<?php
                        if ($gaAsc == 2)
                            userStatsLink($username, $page, 1, $beAsc, $prAsc, $jaAsc,
                                $order, 1);
                        else
                            userStatsLink($username, $page, 2, $beAsc, $prAsc, $jaAsc,
                                $order, 1);?>">Game #<i class="tiny material-icons sorter"><?php
                                if ($gaAsc == 2)
                                    echo 'arrow_drop_down';
                                else
                                    echo 'arrow_drop_up';
                                ?></i></a></th>
                    <th><a href="<?php
                        if ($beAsc == 2)
                            userStatsLink($username, $page, $gaAsc, 1, $prAsc, $jaAsc,
                                $order, 2);
                        else
                            userStatsLink($username, $page, $gaAsc, 2, $prAsc, $jaAsc,
                                $order, 2);?>">Bet<i class="tiny material-icons sorter"><?php
                                if ($beAsc == 2)
                                    echo 'arrow_drop_down';
                                else
                                    echo 'arrow_drop_up';
                                ?></i></a></th>
                    <th><a href="<?php
                        if ($prAsc == 2)
                            userStatsLink($username, $page, $gaAsc, $beAsc, 1, $jaAsc,
                                $order, 3);
                        else
                            userStatsLink($username, $page, $gaAsc, $beAsc, 2, $jaAsc,
                                $order, 3);?>">Profit<i class="tiny material-icons sorter"><?php
                                if ($prAsc == 2)
                                    echo 'arrow_drop_down';
                                else
                                    echo 'arrow_drop_up';
                                ?></i></a></th>
                    <th><a href="<?php
                        if ($jaAsc == 2)
                            userStatsLink($username, $page, $gaAsc, $beAsc, $prAsc, 1,
                                $order, 4);
                        else
                            userStatsLink($username, $page, $gaAsc, $beAsc, $prAsc, 2,
                                $order, 4);?>">Jackpot<i class="tiny material-icons sorter"><?php
                                if ($jaAsc == 2)
                                    echo 'arrow_drop_down';
                                else
                                    echo 'arrow_drop_up';
                                ?></i></a></th>
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

                        <td>
                            <a href="<?php echo "game_info.php?game=" . $item['game_id']; ?>"><?php echo $item['game_id']; ?></a>
                        </td>
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
            <div class="row">
        <?php else: ?>
            <h3 class="center-align"><i class="medium material-icons vmid">error</i> User '<?php echo $_GET['user']; ?>'
                does not exist.</h3>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include 'footer.php'; ?>
<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/24/17
 * Time: 10:11 PM
 *
 */
session_start();

$rowPerPage = 20;

function gamesHistoryLink($page = 1, $gaAsc = 1, $jaAsc = 1, $nuAsc = 1, $arrayOrd, $first)
{
    $pos = array_search($first, $arrayOrd);
    array_splice($arrayOrd, $pos, 1);
    array_unshift($arrayOrd, $first);

    echo "games_history.php?p=$page&ga=$gaAsc&ja=$jaAsc&nu=$nuAsc&ord[]=$arrayOrd[0]&ord[]=$arrayOrd[1]&ord[]=$arrayOrd[2]";
}

include "function.php";

if (isset($_GET['ga']) && !empty($_GET['ga'])) {
    $gaAsc = htmlspecialchars($_GET['ga']);
    filterOnlyNumber($gaAsc, 1, 2, 1);
} else {
    $gaAsc = 1;
}

if (isset($_GET['ja']) && !empty($_GET['ja'])) {
    $jaAsc = htmlspecialchars($_GET['ja']);
    filterOnlyNumber($jaAsc, 1, 2, 1);
} else {
    $jaAsc = 1;
}

if (isset($_GET['nu']) && !empty($_GET['nu'])) {
    $nuAsc = htmlspecialchars($_GET['nu']);
    filterOnlyNumber($nuAsc, 1, 2, 1);
} else {
    $nuAsc = 1;
}

if (isset($_GET['ord']) && !empty($_GET['ord'])) {
    $order = $_GET['ord'];
    filterArray($order, 3);
} else {
    $order = array(1, 2, 3);
}

try {
    include "connect.php";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    //Page count calculation
    $stmt = $conn->prepare('SELECT COUNT(game_id) AS count FROM game');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $pageCount = ceil($row['count'] / $rowPerPage);

    if (isset($_GET['p']) && !empty($_GET['p'])) {
        $page = htmlspecialchars($_GET['p']);
        filterOnlyNumber($page, 1, $pageCount, 1);
    } else
        $page = 1;

    //Creating the statement according to get parameters
    $statement = 'SELECT game_id, date_format(timedate, \'%h:%i %p\') AS time, winner_number, amount FROM game
                                      WHERE amount > 0
                                      ORDER BY ';

    if ($gaAsc == 2)
        $gaString = "game_id ASC";
    else
        $gaString = "game_id DESC";

    if ($jaAsc == 2)
        $jaString = "amount ASC";
    else
        $jaString = "amount DESC";

    if ($nuAsc == 2)
        $nuString = "winner_number ASC";
    else
        $nuString = "winner_number DESC";

    for ($i = 0; $i <= 1; $i++) {
        if ($order[$i] == 1)
            $statement = $statement . $gaString;
        if ($order[$i] == 2)
            $statement = $statement . $jaString;
        if ($order[$i] == 3)
            $statement = $statement . $nuString;

        if ($i < 1)
            $statement = $statement . ", ";
    }


    $statement = $statement . ' LIMIT :rows OFFSET :the_offset';

    // Selecting game history
    $stmt = $conn->prepare($statement);

    $stmt->execute(array('rows' => $rowPerPage, 'the_offset' => (($page - 1) * $rowPerPage)));

    $rowTable = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>
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
    <?php include "inc/header.php" ?>
</header>
<main>
    <div class="container">
        <div class="row top-buffer-30">
            <h3>Games history</h3>
            <div class="col l10 offset-l1 m10 offset-m1 s12">
                <table class="highlight">
                    <thead>
                    <tr>
                        <th><a href="<?php
                            if ($gaAsc == 2)
                                gamesHistoryLink($page, 1, $jaAsc, $nuAsc, $order, 1);
                            else
                                gamesHistoryLink($page, 2, $jaAsc, $nuAsc, $order, 1);
                            ?>">Game #<i class="tiny material-icons sorter"><?php
                                    if ($gaAsc == 2)
                                        echo 'arrow_drop_down';
                                    else
                                        echo 'arrow_drop_up';
                                    ?></i></a></th>
                        <th><a href="<?php
                            if ($jaAsc == 2)
                                gamesHistoryLink($page, $gaAsc, 1, $nuAsc, $order, 2);
                            else
                                gamesHistoryLink($page, $gaAsc, 2, $nuAsc, $order, 2);
                            ?>">Jackpot<i class="tiny material-icons sorter"><?php
                                    if ($jaAsc == 2)
                                        echo 'arrow_drop_down';
                                    else
                                        echo 'arrow_drop_up';
                                    ?></i></a></th>
                        <th><a href="<?php
                            if ($nuAsc == 2)
                                gamesHistoryLink($page, $gaAsc, $jaAsc, 1, $order, 3);
                            else
                                gamesHistoryLink($page, $gaAsc, $jaAsc, 2, $order, 3);
                            ?>">Number<i class="tiny material-icons sorter"><?php
                                    if ($nuAsc == 2)
                                        echo 'arrow_drop_down';
                                    else
                                        echo 'arrow_drop_up';
                                    ?></i></a></th>
                        <th>Time</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($rowTable as $item) {
                        echo "<tr>
                <td><a href=\"game_info.php?game_id=" . $item['game_id'] . "\" target=\"_self\">" . $item['game_id'] . "</a></td>" .
                            "<td>" . ($item['amount'] / 100) . " bits</td>" .
                            "<td><div class='chip'>" . $item['winner_number'] . "</div></td>" .
                            "<td>" . $item['time'] . "</td>" .
                            "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row centerWrap">
            <div class="centeredDiv">
                <?php if ($pageCount > 1): ?>
                <ul class="pagination">
                    <!--                        Go left (pagination) -->
                    <li class="<?php
                    if ($page > 1)
                        echo 'waves-effect';
                    else
                        echo 'disabled';
                    ?>"><a href="<?php
                        if ($page > 1)
                            gamesHistoryLink($page + 1, $gaAsc, $jaAsc, $nuAsc, $order, $order[0]);
                        else
                            echo '#!';
                        ?>"><i class="material-icons">chevron_left</i></a></li>
                    <!--Pages-->
                    <?php
                    if ($pageCount <= 15) {
                        for ($i = 1; $i <= $pageCount; $i++) : ?>
                            <li class="<?php if ($page == $i)
                                echo 'active';
                            else
                                echo 'waves-effect'; ?>"><a
                                        href="<?php gamesHistoryLink($i, $gaAsc, $jaAsc, $nuAsc, $order, $order[0]); ?>">
                                    <?php echo $i; ?></a></li>
                        <?php endfor;
                    } else {
                        if ($page <= 8) {
                            for ($i = 1; $i <= 14; $i++) :?>
                                <li class="<?php if ($page == $i)
                                    echo 'active';
                                else
                                    echo 'waves-effect'; ?>"><a
                                            href="<?php gamesHistoryLink($i, $gaAsc, $jaAsc, $nuAsc, $order, $order[0]); ?>">
                                        <?php echo $i; ?></a></li>
                            <?php endfor;
                            echo '<li>...</li>'; ?>
                            <li class="<?php if ($page == $pageCount)
                                echo 'active';
                            else
                                echo 'waves-effect'; ?>"><a
                                        href="<?php gamesHistoryLink($i, $gaAsc, $jaAsc, $nuAsc, $order, $order[0]); ?>">
                                    <?php echo $pageCount; ?></a></li>
                            <?php
                        } else { ?>
                            <li class="<?php if ($page == 1)
                                echo 'active';
                            else
                                echo 'waves-effect'; ?>"><a
                                        href="<?php gamesHistoryLink($i, $gaAsc, $jaAsc, $nuAsc, $order, $order[0]); ?>">
                                    <?php echo 1; ?></a></li>
                            <?php
                            echo '<li>...</li>';
                            if ($pageCount - $page > 7) {
                                for ($i = $page - 6; $i <= $page + 6; $i++) :?>
                                    <li class="<?php if ($page == $i)
                                        echo 'active';
                                    else
                                        echo 'waves-effect'; ?>"><a
                                                href="<?php gamesHistoryLink($i, $gaAsc, $jaAsc, $nuAsc, $order, $order[0]); ?>">
                                            <?php echo $i; ?></a></li>
                                <?php endfor;
                                echo '<li>...</li>'; ?>
                                <li class="<?php if ($page == $pageCount)
                                    echo 'active';
                                else
                                    echo 'waves-effect'; ?>"><a
                                            href="<?php gamesHistoryLink($i, $gaAsc, $jaAsc, $nuAsc, $order, $order[0]); ?>">
                                        <?php echo $pageCount; ?></a></li>
                                <?php
                            } else {
                                for ($i = $pageCount - 13; $i <= $pageCount; $i++) :?>
                                    <li class="<?php if ($page == $i)
                                        echo 'active';
                                    else
                                        echo 'waves-effect'; ?>"><a
                                                href="<?php gamesHistoryLink($i, $gaAsc, $jaAsc, $nuAsc, $order, $order[0]); ?>">
                                            <?php echo $i; ?></a></li>
                                <?php endfor;
                            }
                        }

                    } ?>
                    <!--                        Go right (pagination) -->
                    <li class="<?php
                    if ($page < $pageCount)
                        echo 'waves-effect';
                    else
                        echo 'disabled';
                    ?>"><a href="<?php
                        if ($page < $pageCount)
                            gamesHistoryLink($page + 1, $gaAsc, $jaAsc, $nuAsc, $order, $order[0]);
                        else
                            echo '#!';
                        ?>"><i class="material-icons">chevron_right</i></a></li>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php include "inc/footer.php"; ?>
</body>
</html>
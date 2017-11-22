<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/19/17
 * Time: 4:57 PM
 */
session_start();

$servername = "localhost";

function filterOnlyNumber(&$number, $default, $max, $min)
{
    if (is_numeric($number)) {
        if (($number <= $max) && ($number >= $min))
            $number = floor($number);
        else
            $number = $default;
    } else
        $number = $default;
}

function gameInfoLink($game, $page = 1, $n = 2, $f = 2, $ff = 2, $fi = 1, $se = 2, $th = 3)
{
    echo "game_info.php?game_id=$game&p=$page&n=$n&f=$f&ff=$ff&fi=$fi&se=$se&th=$th";
}

if (isset($_GET['n']) && !empty($_GET['n'])) {
    $nAsc = $_GET['n'];
    filterOnlyNumber($nAsc, 2, 2, 1);
} else {
    $nAsc = 2;
}

if (isset($_GET['f']) && !empty($_GET['f'])) {
    $fAsc = $_GET['f'];
    filterOnlyNumber($fAsc, 2, 2, 1);
} else {
    $fAsc = 2;
}

if (isset($_GET['ff']) && !empty($_GET['ff'])) {
    $ffAsc = $_GET['ff'];
    filterOnlyNumber($ffAsc, 2, 2, 1);
} else {
    $ffAsc = 2;
}

if (isset($_GET['fi']) && !empty($_GET['fi'])) {
    $first = $_GET['fi'];
    filterOnlyNumber($first, 1, 3, 1);
} else {
    $first = 1;
}

if (isset($_GET['se']) && !empty($_GET['se'])) {
    $second = $_GET['se'];
    filterOnlyNumber($second, 2, 3, 1);
} else {
    $second = 2;
}

if (isset($_GET['th']) && !empty($_GET['th'])) {
    $third = $_GET['th'];
    filterOnlyNumber($third, 3, 3, 1);
} else {
    $third = 3;
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=lottery", "root", "5720297Ff");
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    //Selecting current game
    $stmt = $conn->prepare('SELECT game_id, amount FROM game ORDER BY game_id DESC, timedate DESC LIMIT 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_game = $row['game_id'];

    //Selecting min game
    $stmt = $conn->prepare('SELECT game_id, amount FROM game ORDER BY game_id ASC, timedate ASC LIMIT 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $min_game = $row['game_id'];

    if (isset($_GET['game_id']) && !empty($_GET['game_id'])) {
        $game_id = $_GET['game_id'];
        filterOnlyNumber($game_id, $current_game - 1, $current_game, $min_game);
    } else {
        $game_id = $current_game - 1;
    }


    if ($current_game != $game_id) {

        if ($nAsc == 2)
            $nuOrd = 'ASC';
        else
            $nuOrd = 'DESC';

        if ($fAsc == 2)
            $fOrd = 'ASC';
        else
            $fOrd = 'DESC';

        if ($ffAsc == 2)
            $ffOrd = 'ASC';
        else
            $ffOrd = 'DESC';

        if ($first == 1 && $second == 2 && $third == 3)
            $lastPart = 'nxf.number_id ' . $nuOrd . ', nxf.frequency ' . $fOrd . ', fxft.fxf ' . $ffOrd;
        else if ($first == 1 && $second == 3 && $third == 2)
            $lastPart = 'nxf.number_id ' . $nuOrd . ', fxft.fxf ' . $ffOrd . ', nxf.frequency ' . $fOrd;
        else if ($first == 2 && $second == 1 && $third == 3)
            $lastPart = 'nxf.frequency ' . $fOrd . ', nxf.number_id ' . $nuOrd . ', fxft.fxf ' . $ffOrd;
        else if ($first == 2 && $second == 3 && $third == 1)
            $lastPart = 'nxf.frequency ' . $fOrd . ', fxft.fxf ' . $ffOrd . ', nxf.number_id ' . $nuOrd;
        else if ($first == 3 && $second == 2 && $third == 1)
            $lastPart = 'fxft.fxf ' . $ffOrd . ', nxf.frequency ' . $fOrd . ', nxf.number_id ' . $nuOrd;
        else if ($first == 3 && $second == 1 && $third == 2)
            $lastPart = 'fxft.fxf ' . $ffOrd . ', nxf.number_id ' . $nuOrd . ', nxf.frequency ' . $fOrd;
        else
            $lastPart = 'nxf.number_id ' . $nuOrd . ', nxf.frequency ' . $fOrd . ', fxft.fxf ' . $ffOrd;

        $statement = 'SELECT nxf.number_id AS number_id, nxf.frequency AS frequency, fxft.fxf AS fxf
                                    FROM (SELECT number_id, COUNT(number_id) AS frequency FROM numberxuser WHERE game_id = :game_id1
                                    GROUP BY number_id) AS nxf
                                    INNER JOIN
                                    (SELECT frequency, COUNT(frequency) AS fxf FROM(
                                    SELECT number_id, COUNT(number_id) AS frequency FROM numberxuser
                                    WHERE game_id = :game_id2
                                    GROUP BY number_id) AS sometable
                                    GROUP BY frequency) AS fxft
                                    ON fxft.frequency = nxf.frequency
                                    ORDER BY ' . $lastPart . ' LIMIT 20 OFFSET :offs';

        //Selecting game information
        $stmt = $conn->prepare('SELECT amount, winner_number, date_format(timedate, \'%M %D, %Y %h:%i %p\') AS time FROM game 
                                     WHERE game_id = :game_id');
        $stmt->execute(array('game_id' => $game_id));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $amount = $row['amount'];
        $winner_number = $row['winner_number'];
        $timedate = $row['time'];

        $stmt = $conn->prepare('SELECT COUNT(DISTINCT number_id) AS count FROM numberxuser WHERE game_id = :game_id');
        $stmt->execute(array('game_id' => $game_id));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $row['count'];
        $pageCount = ceil($count / 20);


        if (isset($_GET['p']) && !empty($_GET['p'])) {
            $page = $_GET['p'];
            filterOnlyNumber($page, 1, $pageCount, 1);
        } else
            $page = 1;

        //Selecting numbers information
        $stmt = $conn->prepare($statement);

        $stmt->execute(array('game_id1' => $game_id, 'game_id2' => $game_id, 'offs' => (($page - 1) * 20)));
        $rowTable = $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bitcoin</title>
    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
    <script src="js/autobahn.js"></script>
    <script>
        $(function () {

            var searchGame = $("#search_game");
            searchGame.on('keypress', function (e) {
                if (e.which === 13) {

                    var valid = $.isNumeric(searchGame.val());
                    if (valid)
                        window.location.href = 'game_info.php?game_id=' + searchGame.val() +
                        <?php echo "'&p=$page&n=$nAsc&f=$fAsc&ff=$ffAsc&fi=$first&se=$second&th=$third'"?>;
                }
            });
        });
    </script>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<header>
    <!-- Profile Structure -->
    <?php if (isset($_SESSION['username']) && !empty($_SESSION['username'])) : ?>
        <ul id="profileDropdown" class="dropdown-content">
            <li><a href="#!"><i class="material-icons left">person</i>Profile</a></li>
            <li><a href="#!"><i class="material-icons left">exit_to_app</i>Logout</a></li>
        </ul>
    <?php endif; ?>
    <!-- Navbar goes here -->
    <nav>
        <div class="nav-wrapper green">
            <a href="#" class="brand-logo left">BitPVP</a>
            <ul id="nav-mobile" class="right .hide-on-small-only nav-letters">
                <li><a href="game_info.php"><i class="material-icons left">assignment</i><b>Game Info</b></a></li>
                <li><a href="rank.php"><i class="material-icons left">assistant_photo</i><b>Ranking</b></a></li>
                <li><a href="stats.php"><i class="material-icons left">assessment</i><b>Stats</b></a></li>
                <?php if (isset($_SESSION['username']) && !empty($_SESSION['username'])): ?>
                    <li class="no-link-nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>
                    <li class="no-link-nav"><i class="material-icons left">account_balance_wallet</i>
                        <b>Balance: <span id="balanceNumber"><?php include 'updateBalance.php'; ?></span> bits</b></li>
                    <li><a class="dropdown-button" href="#" data-activates="profileDropdown">
                            <b><?php echo $_SESSION['username']; ?></b><i
                                    class="material-icons right">arrow_drop_down</i></a>
                    </li>
                <?php else: ?>
                    <li><a href="registration.php"><b>Register</b></a></li>
                    <li><a href="login.php"><b>Login</b></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <div class="row top-buffer-15">
        <div class="col s4 offset-s4">
            <div class="row">
                <div class="input-field col s12">
                    <i class="material-icons prefix">search</i>
                    <input id="search_game" class="validate" type="number" ">
                    <label for="search_game">Game number</label>
                </div>
            </div>
        </div>
    </div>
</header>
<main class="<?php if ($current_game == $game_id) echo 'valign-wrapper'; ?>">
    <div class="container">
        <?php if ($current_game != $game_id) : ?>
            <div class="row">
                <h3><b>Game #<?php echo $game_id ?></b></h3>
                <h5><?php echo $timedate; ?></h5>
                <h5><b>Jackpot: </b><?php echo($amount / 100); ?> bits</h5>
                <div>
                    <b class="h5Span">Winner number: </b>
                    <div class="chip"><?php echo $winner_number; ?></div>
                </div>
            </div>
            <div class="row">
                <table class="highlight">
                    <thead>
                    <tr>
                        <th><?php if ($nAsc == 2) : ?>
                                <a href="<?php
                                if ($first != 1) {
                                    if ($second == 1)
                                        gameInfoLink($game_id, $page, 1, $fAsc, $ffAsc, 1, $first, $third);
                                    else
                                        gameInfoLink($game_id, $page, 1, $fAsc, $ffAsc, 1, $first, $second);
                                } else
                                    gameInfoLink($game_id, $page, 1, $fAsc, $ffAsc, $first, $second, $third);
                                ?>">
                                    Number<i class="tiny material-icons sorter">arrow_drop_down</i></a>
                            <?php else: ?>
                                <a href="<?php
                                if ($first != 1) {
                                    if ($second == 1)
                                        gameInfoLink($game_id, $page, 2, $fAsc, $ffAsc, 1, $first, $third);
                                    else
                                        gameInfoLink($game_id, $page, 2, $fAsc, $ffAsc, 1, $first, $second);
                                } else
                                    gameInfoLink($game_id, $page, 2, $fAsc, $ffAsc, $first, $second, $third);
                                ?>">
                                    Number<i class="tiny material-icons sorter">arrow_drop_up</i></a>
                            <?php endif; ?>
                        </th>
                        <th><?php if ($fAsc == 2) : ?>
                                <a href="<?php
                                if ($first != 2) {
                                    if ($second == 2)
                                        gameInfoLink($game_id, $page, $nAsc, 1, $ffAsc, 2, $first, $third);
                                    else
                                        gameInfoLink($game_id, $page, $nAsc, 1, $ffAsc, 2, $first, $second);
                                } else
                                    gameInfoLink($game_id, $page, $nAsc, 1, $ffAsc, $first, $second, $third);
                                ?>">
                                    Frequency<i class="tiny material-icons sorter">arrow_drop_down</i></a>
                            <?php else: ?>
                                <a href="<?php
                                if ($first != 2) {
                                    if ($second == 2)
                                        gameInfoLink($game_id, $page, $nAsc, 2, $ffAsc, 2, $first, $third);
                                    else
                                        gameInfoLink($game_id, $page, $nAsc, 2, $ffAsc, 2, $first, $second);
                                } else
                                    gameInfoLink($game_id, $page, $nAsc, 2, $ffAsc, $first, $second, $third);
                                ?>">
                                    Frequency<i class="tiny material-icons sorter">arrow_drop_up</i></a>
                            <?php endif; ?>
                        </th>
                        <th><?php if ($ffAsc == 2) : ?>
                                <a href="<?php
                                if ($first != 3) {
                                    if ($second == 3)
                                        gameInfoLink($game_id, $page, $nAsc, $fAsc, 1, 3, $first, $third);
                                    else
                                        gameInfoLink($game_id, $page, $nAsc, $fAsc, 1, 3, $first, $second);
                                } else
                                    gameInfoLink($game_id, $page, $nAsc, $fAsc, 1, $first, $second, $third);
                                ?>">
                                    Frequency of frequency<i class="tiny material-icons sorter">arrow_drop_down</i></a>
                            <?php else: ?>
                                <a href="<?php
                                if ($first != 3) {
                                    if ($second == 3)
                                        gameInfoLink($game_id, $page, $nAsc, $fAsc, 2, 3, $first, $third);
                                    else
                                        gameInfoLink($game_id, $page, $nAsc, $fAsc, 2, 3, $first, $second);
                                } else
                                    gameInfoLink($game_id, $page, $nAsc, $fAsc, 2, $first, $second, $third);
                                ?>">
                                    Frequency of frequency<i class="tiny material-icons sorter">arrow_drop_up</i></a>
                            <?php endif; ?>
                        </th>
                        <th>Player</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($rowTable as $item) {
                        if ($item['number_id'] == $winner_number)
                            echo '<tr class="win">';
                        else
                            echo '<tr>';
                        echo '<td><div class="chip">' . $item['number_id'] . '</div></td>' .
                            '<td>' . $item['frequency'] . '</td>' .
                            '<td>' . $item['fxf'] . '</td>' .
                            '<td>Master</td>
              </tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="row centerWrap">
                <div class="centeredDiv">
                    <ul class="pagination">
                        <li class="<?php
                        if ($page > 1)
                            echo 'waves-effect';
                        else
                            echo 'disabled';
                        ?>"><a href="<?php
                            if ($page > 1)
                                gameInfoLink($game_id, $page - 1, $nAsc, $fAsc, $ffAsc, $first, $second, $third);
                            else
                                echo '#!';
                            ?>">
                                <i class="material-icons">chevron_left</i></a></li>
                        <?php
                        if ($pageCount <= 15) {
                            for ($i = 1; $i <= $pageCount; $i++) : ?>
                                <li class="<?php if ($page == $i)
                                    echo 'active';
                                else
                                    echo 'waves-effect'; ?>"><a
                                            href="<?php gameInfoLink($game_id, $i, $nAsc, $fAsc, $ffAsc, $first, $second, $third); ?>">
                                        <?php echo $i; ?></a></li>
                            <?php endfor;
                        } else {
                            if ($page <= 8) {
                                for ($i = 1; $i <= 14; $i++) :?>
                                    <li class="<?php if ($page == $i)
                                        echo 'active';
                                    else
                                        echo 'waves-effect'; ?>"><a
                                                href="<?php gameInfoLink($game_id, $i, $nAsc, $fAsc, $ffAsc, $first, $second, $third); ?>">
                                            <?php echo $i; ?></a></li>
                                <?php endfor;
                                echo '<li>...</li>'; ?>
                                <li class="<?php if ($page == $pageCount)
                                    echo 'active';
                                else
                                    echo 'waves-effect'; ?>"><a
                                            href="<?php gameInfoLink($game_id, $pageCount, $nAsc, $fAsc, $ffAsc, $first, $second, $third); ?>">
                                        <?php echo $pageCount; ?></a></li>
                                <?php
                            } else { ?>
                                <li class="<?php if ($page == 1)
                                    echo 'active';
                                else
                                    echo 'waves-effect'; ?>"><a
                                            href="<?php gameInfoLink($game_id, 1, $nAsc, $fAsc, $ffAsc, $first, $second, $third); ?>">
                                        <?php echo 1; ?></a></li>
                                <?php
                                echo '<li>...</li>';
                                if ($pageCount - $page > 7) {
                                    for ($i = $page - 6; $i <= $page + 6; $i++) :?>
                                        <li class="<?php if ($page == $i)
                                            echo 'active';
                                        else
                                            echo 'waves-effect'; ?>"><a
                                                    href="<?php gameInfoLink($game_id, $i, $nAsc, $fAsc, $ffAsc, $first, $second, $third); ?>">
                                                <?php echo $i; ?></a></li>
                                    <?php endfor;
                                    echo '<li>...</li>'; ?>
                                    <li class="<?php if ($page == $pageCount)
                                        echo 'active';
                                    else
                                        echo 'waves-effect'; ?>"><a
                                                href="<?php gameInfoLink($game_id, $pageCount, $nAsc, $fAsc, $ffAsc, $first, $second, $third); ?>">
                                            <?php echo $pageCount; ?></a></li>
                                    <?php
                                } else {
                                    for ($i = $pageCount - 13; $i <= $pageCount; $i++) :?>
                                        <li class="<?php if ($page == $i)
                                            echo 'active';
                                        else
                                            echo 'waves-effect'; ?>"><a
                                                    href="<?php gameInfoLink($game_id, $i, $nAsc, $fAsc, $ffAsc, $first, $second, $third); ?>">
                                                <?php echo $i; ?></a></li>
                                    <?php endfor;
                                }
                            }

                        } ?>
                        <li class="<?php
                        if ($page < $pageCount)
                            echo 'waves-effect';
                        else
                            echo 'disabled';
                        ?>"><a href="<?php
                            if ($page < $pageCount)
                                gameInfoLink($game_id, $page + 1, $nAsc, $fAsc, $ffAsc, $first, $second, $third);
                            else
                                echo '#!';
                            ?>">
                                <i class="material-icons">chevron_right</i></a></li>
                    </ul>
                </div>
            </div>
        <?php else : ?>
            <div class="row">
                <h3 class="center-align"><i class="medium material-icons vmid">error</i>&nbsp;This game is currently
                    being played.</h3>
            </div>
        <?php endif; ?>
    </div>
</main>
<footer class="page-footer green">
    <div class="container">
        <div class="row">
            <div class="col l6 s12">
                <h5 class="white-text">License</h5>
                <p class="grey-text text-lighten-4">Peruvian license N48D1489A-ADS4</p>
            </div>
            <div class="col l4 offset-l2 s12">
                <h5 class="white-text">Useful Links</h5>
                <ul>
                    <li><a class="grey-text text-lighten-3" href="rank.php">Ranking</a></li>
                    <li><a class="grey-text text-lighten-3" href="stats.php">Stats</a></li>
                    <li><a class="grey-text text-lighten-3" href="game_info.php">Game Info</a></li>
                    <li><a class="grey-text text-lighten-3" href="#!">Guide</a></li>
                    <li><a class="grey-text text-lighten-3" href="#!">Games history</a></li>
                    <li><a class="grey-text text-lighten-3" href="registration.php">Registration</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-copyright">
        <div class="container">
            © <?php echo date('Y'); ?> Copyright BitPVP
        </div>
    </div>
</footer>
</body>

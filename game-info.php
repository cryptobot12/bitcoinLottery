<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/19/17
 * Time: 4:57 PM
 */
session_start();

include "globals.php";
include "function.php";
include "inc/login_checker.php";

$_SESSION['last_url'] = 'game_info';

function gameInfoLink($game, $page = 1, $n = 2, $f = 2, $ff = 2, $arrayOrd, $first)
{
    global $base_dir;

    $pos = array_search($first, $arrayOrd);
    array_splice($arrayOrd, $pos, 1);
    array_unshift($arrayOrd, $first);

    $link = $base_dir . "game-info/$game/" . $page . $n . $f . $ff;

    foreach ($arrayOrd as $i) {
        $link .= $i;
    }

    echo $link;
}

if (isset($_GET['n']) && !empty($_GET['n'])) {
    $nAsc = htmlspecialchars($_GET['n']);
    filterOnlyNumber($nAsc, 2, 2, 1);
} else {
    $nAsc = 2;
}

if (isset($_GET['f']) && !empty($_GET['f'])) {
    $fAsc = htmlspecialchars($_GET['f']);
    filterOnlyNumber($fAsc, 2, 2, 1);
} else {
    $fAsc = 2;
}

if (isset($_GET['ff']) && !empty($_GET['ff'])) {
    $ffAsc = htmlspecialchars($_GET['ff']);
    filterOnlyNumber($ffAsc, 2, 2, 1);
} else {
    $ffAsc = 2;
}

if (!empty($_GET['ord'])) {
    $order = $_GET['ord'];
    filterArray($order, 3);
} else {
    $order = array(1, 2, 3);
}

$rowsPerPage = 20;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    //Selecting current game
    $stmt = $conn->prepare('SELECT game_id, amount FROM game ORDER BY game_id DESC, game_date DESC LIMIT 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_game = $row['game_id'];

    //Selecting min game
    $stmt = $conn->prepare('SELECT game_id, amount FROM game ORDER BY game_id ASC, game_date ASC LIMIT 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $min_game = $row['game_id'];

    //Selecting last game
    $stmt = $conn->prepare('SELECT game_id, amount FROM game ORDER BY game_id DESC, game_date DESC LIMIT 1 OFFSET 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $last_game = $row['game_id'];

    if (isset($_GET['game_id']) && !empty($_GET['game_id'])) {
        $game_id = htmlspecialchars($_GET['game_id']);
        filterOnlyNumber($game_id, $last_game, $current_game, $min_game);
    } else {
        $game_id = $last_game;
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

        $lastPart = '';

        for ($i = 0; $i <= 3; $i++) {
            if ($order[$i] == 1)
                $lastPart = $lastPart . "nxf.number_id " . $nuOrd;
            if ($order[$i] == 2)
                $lastPart = $lastPart . "nxf.frequency " . $fOrd;
            if ($order[$i] == 3)
                $lastPart = $lastPart . "fxft.fxf " . $ffOrd;

            if ($i < 2)
                $lastPart = $lastPart . ", ";
        }

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
                                    ORDER BY ' . $lastPart . ' LIMIT :the_limit OFFSET :offs';

        //Selecting game information
        $stmt = $conn->prepare('SELECT amount, winner_number, date_format(game_date, \'%M %D, %Y %h:%i %p\') AS time FROM game 
                                     WHERE game_id = :game_id');
        $stmt->execute(array('game_id' => $game_id));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $amount = $row['amount'];
        $winner_number = $row['winner_number'];
        $game_date = $row['time'];

        $stmt = $conn->prepare('SELECT COUNT(DISTINCT number_id) AS count FROM numberxuser WHERE game_id = :game_id');
        $stmt->execute(array('game_id' => $game_id));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $row['count'];
        $pageCount = ceil($count / 20);


        if (isset($_GET['p']) && !empty($_GET['p'])) {
            $page = htmlspecialchars($_GET['p']);
            filterOnlyNumber($page, 1, $pageCount, 1);
        } else
            $page = 1;

        //Selecting numbers information
        $stmt = $conn->prepare($statement);

        $stmt->execute(array('game_id1' => $game_id, 'game_id2' => $game_id, 'the_limit' => $rowsPerPage, 'offs' => (($page - 1) * 20)));
        $rowTable = $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$title = "Game Info - BitcoinPVP";

include "inc/header.php"; ?>
<main class="valign-wrapper">
    <div class="container">
        <div class="row top-buffer-15">
            <div class="col s4 offset-s4">
                <div class="input-field col s12">
                    <i class="material-icons prefix">search</i>
                    <input id="search_game" class="validate" type="number" ">
                    <label for="search_game">Game number</label>
                </div>
            </div>
        </div>
        <?php if ($current_game != $game_id) : ?>
            <div class="row">
                <h3><b>Game #<?php echo $game_id ?></b></h3>
                <h5><?php echo $game_date; ?></h5>
                <h5><b>Jackpot: </b><?php echo($amount / 100); ?> bits</h5>
                <div>
                    <b class="h5Span">Winner number: </b>
                    <div class="chip yellow"><b><?php echo $winner_number; ?></b></div>
                </div>
            </div>
            <div class="row">
                <div class="col l10 offset-l1 m10 offset-m1 s12">
                    <div class="row"></div>
                    <table class="highlight">
                        <thead>
                        <tr>
                            <th><?php if ($nAsc == 2) : ?>
                                    <a href="<?php gameInfoLink($game_id, $page, 1, $fAsc, $ffAsc, $order, 1); ?>">
                                        Number<i class="tiny material-icons sorter">arrow_drop_down</i></a>
                                <?php else: ?>
                                    <a href="<?php gameInfoLink($game_id, $page, 2, $fAsc, $ffAsc, $order, 1); ?>">
                                        Number<i class="tiny material-icons sorter">arrow_drop_up</i></a>
                                <?php endif; ?>
                            </th>
                            <th><?php if ($fAsc == 2) : ?>
                                    <a href="<?php gameInfoLink($game_id, $page, $nAsc, 1, $ffAsc, $order, 2); ?>">
                                        Frequency<i class="tiny material-icons sorter">arrow_drop_down</i></a>
                                <?php else: ?>
                                    <a href="<?php gameInfoLink($game_id, $page, $nAsc, 2, $ffAsc, $order, 2); ?>">
                                        Frequency<i class="tiny material-icons sorter">arrow_drop_up</i></a>
                                <?php endif; ?>
                            </th>
                            <th><?php if ($ffAsc == 2) : ?>
                                    <a href="<?php gameInfoLink($game_id, $page, $nAsc, $fAsc, 1, $order, 3); ?>">
                                        f(Frequency)<i
                                                class="tiny material-icons sorter">arrow_drop_down</i></a>
                                <?php else: ?>
                                    <a href="<?php gameInfoLink($game_id, $page, $nAsc, $fAsc, 2, $order, 3); ?>">
                                        f(Frequency)<i
                                                class="tiny material-icons sorter">arrow_drop_up</i></a>
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
                            echo '<td><div class="chip yellow no-marg-bot"><b>' . $item['number_id'] . '</b></div></td>' .
                                '<td>' . $item['frequency'] . '</td>' .
                                '<td>' . $item['fxf'] . '</td>' .
                                '<td><a href="#!" onclick="showList(' . $item['number_id'] . ');">See players</a></td>
              </tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <!-- Modal Structure -->
                <div id="modal1" class="modal">
                    <div class="modal-content">
                        <div class="modal-title-np">
                            <b>Number: </b><div id="numberSelected" class="chip yellow"></div>
                        </div>
                        <div id="playersList"></div>
                    </div>
                    <div class="modal-footer">
                        <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Close</a>
                    </div>
                </div>
            </div>

            <div class="row centerWrap">
                <div class="centeredDiv">
                    <?php if ($pageCount > 1) : ?>
                        <ul class="pagination">
                            <!--Go left (pagination)-->
                            <li class="<?php
                            if ($page > 1)
                                echo 'waves-effect';
                            else
                                echo 'disabled';
                            ?>"><a href="<?php
                                if ($page > 1)
                                    gameInfoLink($game_id, $page - 1, $nAsc, $fAsc, $ffAsc, $order, $order[0]);
                                else
                                    echo '#!';
                                ?>">
                                    <i class="material-icons">chevron_left</i></a></li>
                            <!--Pages-->
                            <?php
                            if ($pageCount <= 15) {
                                for ($i = 1; $i <= $pageCount; $i++) : ?>
                                    <li class="<?php if ($page == $i)
                                        echo 'active';
                                    else
                                        echo 'waves-effect'; ?>"><a
                                                href="<?php gameInfoLink($game_id, $i, $nAsc, $fAsc, $ffAsc, $order, $order[0]); ?>">
                                            <?php echo $i; ?></a></li>
                                <?php endfor;
                            } else {
                                if ($page <= 8) {
                                    for ($i = 1; $i <= 14; $i++) :?>
                                        <li class="<?php if ($page == $i)
                                            echo 'active';
                                        else
                                            echo 'waves-effect'; ?>"><a
                                                    href="<?php gameInfoLink($game_id, $i, $nAsc, $fAsc, $ffAsc, $order, $order[0]); ?>">
                                                <?php echo $i; ?></a></li>
                                    <?php endfor;
                                    echo '<li>...</li>'; ?>
                                    <li class="<?php if ($page == $pageCount)
                                        echo 'active';
                                    else
                                        echo 'waves-effect'; ?>"><a
                                                href="<?php gameInfoLink($game_id, $pageCount, $nAsc, $fAsc, $ffAsc, $order, $order[0]); ?>">
                                            <?php echo $pageCount; ?></a></li>
                                    <?php
                                } else { ?>
                                    <li class="<?php if ($page == 1)
                                        echo 'active';
                                    else
                                        echo 'waves-effect'; ?>"><a
                                                href="<?php gameInfoLink($game_id, 1, $nAsc, $fAsc, $ffAsc, $order, $order[0]); ?>">
                                            <?php echo 1; ?></a></li>
                                    <?php
                                    echo '<li>...</li>';
                                    if ($pageCount - $page > 7) {
                                        for ($i = $page - 6; $i <= $page + 6; $i++) :?>
                                            <li class="<?php if ($page == $i)
                                                echo 'active';
                                            else
                                                echo 'waves-effect'; ?>"><a
                                                        href="<?php gameInfoLink($game_id, $i, $nAsc, $fAsc, $ffAsc, $order, $order[0]); ?>">
                                                    <?php echo $i; ?></a></li>
                                        <?php endfor;
                                        echo '<li>...</li>'; ?>
                                        <li class="<?php if ($page == $pageCount)
                                            echo 'active';
                                        else
                                            echo 'waves-effect'; ?>"><a
                                                    href="<?php gameInfoLink($game_id, $pageCount, $nAsc, $fAsc, $ffAsc, $order, $order[0]); ?>">
                                                <?php echo $pageCount; ?></a></li>
                                        <?php
                                    } else {
                                        for ($i = $pageCount - 13; $i <= $pageCount; $i++) :?>
                                            <li class="<?php if ($page == $i)
                                                echo 'active';
                                            else
                                                echo 'waves-effect'; ?>"><a
                                                        href="<?php gameInfoLink($game_id, $i, $nAsc, $fAsc, $ffAsc, $order, $order[0]); ?>">
                                                    <?php echo $i; ?></a></li>
                                        <?php endfor;
                                    }
                                }

                            } ?>
                            <!--Go right(pagination)-->
                            <li class="<?php
                            if ($page < $pageCount)
                                echo 'waves-effect';
                            else
                                echo 'disabled';
                            ?>"><a href="<?php
                                if ($page < $pageCount)
                                    gameInfoLink($game_id, $page + 1, $nAsc, $fAsc, $ffAsc, $order, $order[0]);
                                else
                                    echo '#!';
                                ?>">
                                    <i class="material-icons">chevron_right</i></a></li>
                        </ul>

                    <?php endif; ?>
                </div>
            </div>
        <?php else : ?>
            <div class="row centerWrap">
                <div class="centeredDiv">
                    <span class="h5Span"><i
                                class="material-icons left">error</i>This game is currently being played</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>
<!--    Jquery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
<script src="<?php echo $base_dir; ?>js/game-info-script.js"></script>
<script>
    var base_dir = "<?php echo $base_dir; ?>";
    var game_id = <?php echo $game_id; ?>;
    $(function () {

        var searchGame = $("#search_game");
        searchGame.on('keypress', function (e) {
            if (e.which === 13) {

                var valid = $.isNumeric(searchGame.val());
                if (valid)
                    window.location.href = '<?php echo $base_dir; ?>game-info/' + searchGame.val() +
                        '/<?php echo $page . $nAsc . $fAsc . $ffAsc;

                            foreach ($order as $i) {
                                echo $i;
                            } ?>';
            }
        });
    });

    $(document).ready(function () {
        M.AutoInit();

    });
</script>
<?php include "inc/footer.php"; ?>



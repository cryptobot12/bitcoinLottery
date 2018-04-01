<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/25/2017
 * Time: 6:16 PM
 */
session_start();

$rowPerPage = 25;

include "globals.php";
include "function.php";
include "inc/login_checker.php";

$_SESSION['last_url'] = 'rank';

function rankLink($page = 1, $raAsc = 1, $gaAsc = 1, $arrayOrd, $first)
{
    $pos = array_search($first, $arrayOrd);
    array_splice($arrayOrd, $pos, 1);
    array_unshift($arrayOrd, $first);

    $link = "rank/" . $page . $raAsc . $gaAsc;

    foreach ($arrayOrd as $i){
        $link .= $i;
    }

    echo $link;
}

if (isset($_GET['ra']) && !empty($_GET['ra'])) {
    $raAsc = htmlspecialchars($_GET['ra']);
    filterOnlyNumber($raAsc, 2, 2, 1);
} else {
    $raAsc = 2;
}

if (isset($_GET['ga']) && !empty($_GET['ga'])) {
    $gaAsc = htmlspecialchars($_GET['ga']);
    filterOnlyNumber($gaAsc, 1, 2, 1);
} else {
    $gaAsc = 1;
}

if (isset($_GET['ord']) && !empty($_GET['ord'])) {
    $order = $_GET['ord'];
    filterArray($order, 2);
} else {
    $order = array(1, 2);
}

try {

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    if (empty($_GET['user'])) {
        $stmt = $conn->prepare('SELECT COUNT(username) AS userCount
                        FROM user');
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $pageCount = ceil($row['userCount'] / $rowPerPage);

        if (isset($_GET['p']) && !empty($_GET['p'])) {
            $page = htmlspecialchars($_GET['p']);
            filterOnlyNumber($page, 1, $pageCount, 1);
        } else
            $page = 1;

        if ($gaAsc == 2)
            $gaString = "user.games_played ASC";
        else
            $gaString = "user.games_played DESC";

        if ($raAsc == 2)
            $raString = "rank ASC";
        else
            $raString = "rank DESC";

        $statement = 'SELECT  username, net_profit, games_played, FIND_IN_SET( net_profit, (
                SELECT GROUP_CONCAT( net_profit
                ORDER BY net_profit DESC )
                FROM user 
                 WHERE user.games_played <> 0)
                ) AS rank
                FROM user
                WHERE user.games_played <> 0
                ORDER BY ';

        for ($i = 0; $i <= 1; $i++) {
            if ($order[$i] == 1)
                $statement = $statement . $raString;
            if ($order[$i] == 2)
                $statement = $statement . $gaString;

            if ($i < 1)
                $statement = $statement . ", ";
        }

        $statement = $statement . ' LIMIT :rows OFFSET :the_offset';

        $stmt = $conn->prepare($statement);
        $stmt->execute(array('rows' => $rowPerPage, 'the_offset' => (($page - 1) * $rowPerPage)));
        $rowTable = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        $statement = 'SELECT  username, net_profit, games_played, FIND_IN_SET( net_profit, (
                SELECT GROUP_CONCAT( net_profit
                ORDER BY net_profit DESC )
                FROM user 
                 WHERE user.games_played <> 0)
                ) AS rank
                FROM user
                WHERE user.username = :username';

        $stmt = $conn->prepare($statement);
        $stmt->execute(array('username' => htmlspecialchars($_GET['user'])));
        $rowTable = $stmt->fetch(PDO::FETCH_ASSOC);

    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
$title = "Ranking - BitcoinPVP";
include "inc/header.php"; ?>
<main>
    <div class="container">
        <div class="row top-buffer-15">
            <div class="col l4 offset-l4 m8 offset-m2 s12">
                <div class="input-field col s12">
                    <i class="material-icons prefix">search</i>
                    <input id="search_user" class="validate" type="text" ">
                    <label for="search_user">Username</label>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <?php if (empty($_GET['user'])) : ?>
            <div class="row">
                <div class="col l10 offset-l1 m10 offset-m1 s12">
                    <table class="highlight">
                        <thead>
                        <tr>
                            <th><a href="<?php echo $base_dir;
                                if ($raAsc == 2)
                                    rankLink($page, 1, $gaAsc, $order, 1);
                                else
                                    rankLink($page, 2, $gaAsc, $order, 1);
                                ?>">Rank<i class="tiny material-icons sorter"><?php
                                        if ($raAsc == 2)
                                            echo 'arrow_drop_down';
                                        else
                                            echo 'arrow_drop_up';
                                        ?></i></a></th>
                            <th>User</th>
                            <th>Net Profit</th>
                            <th><a href="<?php echo $base_dir;
                                if ($gaAsc == 2)
                                    rankLink($page, $raAsc, 1, $order, 2);
                                else
                                    rankLink($page, $raAsc, 2, $order, 2);
                                ?>">Games played<i class="tiny material-icons sorter"><?php
                                        if ($gaAsc == 2)
                                            echo 'arrow_drop_down';
                                        else
                                            echo 'arrow_drop_up';
                                        ?></i></a></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($rowTable as $item) {
                            echo "<tr>
            <td><b>" . $item['rank'] . "</b></td>
                <td><a href='" . $base_dir . "user-stats/" . $item['username'] . "'>" . $item['username'] . "</a></td>" .
                                "<td><span class='";

                            if ($item['net_profit'] > 0)
                                echo "win-text";
                            elseif ($item['net_profit'] == 0)
                                echo "neutral-text";
                            else
                                echo "lose-text";

                            echo "'>" . $item['net_profit'] / 100 . " bits</span></td>" .
                                "<td>" . $item['games_played'] . "</td>" .
                                "</tr>";
                        } ?>
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
                                if ($page > 1) {
                                    echo $base_dir;
                                    rankLink($page - 1, $raAsc, $gaAsc, $order, $order[0]);
                                } else
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
                                                href="<?php echo $base_dir;
                                                rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
                                            <?php echo $i; ?></a></li>
                                <?php endfor;
                            } else {
                                if ($page <= 8) {
                                    for ($i = 1; $i <= 14; $i++) :?>
                                        <li class="<?php if ($page == $i)
                                            echo 'active';
                                        else
                                            echo 'waves-effect'; ?>"><a
                                                    href="<?php echo $base_dir;
                                                    rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
                                                <?php echo $i; ?></a></li>
                                    <?php endfor;
                                    echo '<li>...</li>'; ?>
                                    <li class="<?php if ($page == $pageCount)
                                        echo 'active';
                                    else
                                        echo 'waves-effect'; ?>"><a
                                                href="<?php echo $base_dir;
                                                rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
                                            <?php echo $pageCount; ?></a></li>
                                    <?php
                                } else { ?>
                                    <li class="<?php if ($page == 1)
                                        echo 'active';
                                    else
                                        echo 'waves-effect'; ?>"><a
                                                href="<?php echo $base_dir;
                                                rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
                                            <?php echo 1; ?></a></li>
                                    <?php
                                    echo '<li>...</li>';
                                    if ($pageCount - $page > 7) {
                                        for ($i = $page - 6; $i <= $page + 6; $i++) :?>
                                            <li class="<?php if ($page == $i)
                                                echo 'active';
                                            else
                                                echo 'waves-effect'; ?>"><a
                                                        href="<?php echo $base_dir;
                                                        rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
                                                    <?php echo $i; ?></a></li>
                                        <?php endfor;
                                        echo '<li>...</li>'; ?>
                                        <li class="<?php if ($page == $pageCount)
                                            echo 'active';
                                        else
                                            echo 'waves-effect'; ?>"><a
                                                    href="<?php echo $base_dir;
                                                    rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
                                                <?php echo $pageCount; ?></a></li>
                                        <?php
                                    } else {
                                        for ($i = $pageCount - 13; $i <= $pageCount; $i++) :?>
                                            <li class="<?php if ($page == $i)
                                                echo 'active';
                                            else
                                                echo 'waves-effect'; ?>"><a
                                                        href="<?php echo $base_dir;
                                                        rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
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
                                if ($page < $pageCount) {
                                    echo $base_dir;
                                    rankLink($page + 1, $raAsc, $gaAsc, $order, $order[0]);
                                } else
                                    echo '#!';
                                ?>">
                                    <i class="material-icons">chevron_right</i></a></li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php if (isset($rowTable['username'])): ?>
                    <div class="col l10 offset-l1 m10 offset-m1 s12">
                        <table class="highlight">
                            <thead>
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Net Profit</th>
                                <th>Games played</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            echo "<tr><td><b>";
                            if ($rowTable['rank'] != 0)
                                echo $rowTable['rank'];
                            else
                                echo "Unranked";

                            echo "</b></td>
                <td><a href='" . $base_dir . "user-stats/" . $rowTable['username'] . "'>" . $rowTable['username'] . "</a></td>" .
                                "<td>" . $rowTable['net_profit'] / 100 . " bits</td>" .
                                "<td>" . $rowTable['games_played'] . "</td>" .
                                "</tr>";
                            ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                <div class="row"></div>
                <div class="row"></div>
                <div class="row"></div>
                <div class="row centerWrap">
                    <div class="centeredDiv">
                        <span class="h5Span"><i class="material-icons left">error</i> User
                        '<?php echo htmlspecialchars($_GET['user']); ?>' does not exist.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>
<!--    Jquery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
<script>
    $(function () {

        var searchUser = $("#search_user");
        searchUser.on('keypress', function (e) {
            if (e.which === 13) {
                window.location.href = '<?php echo $base_dir; ?>rank/user/' + searchUser.val();
            }
        });
    });

    $(document).ready(function () {
        M.AutoInit();

    });

</script>
<?php include "inc/footer.php"; ?>



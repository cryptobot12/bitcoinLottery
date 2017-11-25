<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/25/2017
 * Time: 6:16 PM
 */
session_start();

$rowPerPage = 25;

include "function.php";

function rankLink($page = 1, $raAsc = 1, $gaAsc = 1, $arrayOrd, $first)
{
    $pos = array_search($first, $arrayOrd);
    array_splice($arrayOrd, $pos, 1);
    array_unshift($arrayOrd, $first);

    echo "rank.php?p=$page&ra=$raAsc&ga=$gaAsc&ord[]=$arrayOrd[0]&ord[]=$arrayOrd[1]";
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
    include "connect.php";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    if (!(isset($_GET['user']) && !empty($_GET['user']))) {
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

        $statement = 'SELECT
                      rank,
                      user.username,
                      user.net_profit,
                      user.games_played
                    FROM user INNER JOIN
                      (SELECT
                        user.user_id,
                      CASE
                      WHEN @prevRank = net_profit
                        THEN @curRank
                      WHEN @prevRank := net_profit
                        THEN @curRank := @curRank + 1
                      END AS rank,
                      net_profit
                    FROM user,
                      (SELECT
                         @curRank := 0,
                         @prevRank := NULL) r
                    ORDER BY net_profit DESC) AS r1
                    ON user.user_id = r1.user_id
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
        $statement = 'SELECT
                      rank,
                      user.username,
                      user.net_profit,
                      user.games_played
                    FROM user INNER JOIN
                      (SELECT
                        user.user_id,
                      CASE
                      WHEN @prevRank = net_profit
                        THEN @curRank
                      WHEN @prevRank := net_profit
                        THEN @curRank := @curRank + 1
                      END AS rank,
                      net_profit
                    FROM user,
                      (SELECT
                         @curRank := 0,
                         @prevRank := NULL) r
                    ORDER BY net_profit DESC) AS r1
                    ON user.user_id = r1.user_id
                    WHERE user.username = :username';

        $stmt = $conn->prepare($statement);
        $stmt->execute(array('username' => htmlspecialchars($_GET['user'])));
        $rowTable = $stmt->fetch(PDO::FETCH_ASSOC);

    }

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
    <script>
        $(function () {

            var searchUser = $("#search_user");
            searchUser.on('keypress', function (e) {
                if (e.which === 13) {
                    window.location.href = 'rank.php?user=' + searchUser.val();
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
    <?php include "header.php" ?>
    <div class="row top-buffer-15">
        <div class="col s4 offset-s4">
            <div class="input-field col s12">
                <i class="material-icons prefix">search</i>
                <input id="search_user" class="validate" type="text" ">
                <label for="search_user">Username</label>
            </div>
        </div>
    </div>
</header>
<main class="<?php
if (isset($_GET['user']) && !empty($_GET['user']) && (!isset($rowTable['username'])))
    echo 'valign-wrapper'; ?>">
    <div class="container">
        <?php if (!(isset($_GET['user']) && !empty($_GET['user']))) : ?>
            <div class="row">
                <div class="col l10 offset-l1 m10 offset-m1 s12">
                    <table class="highlight">
                        <thead>
                        <tr>
                            <th><a href="<?php
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
                            <th><a href="<?php
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
                <td><a href='user_stats.php?user=" . $item['username'] . "'>" . $item['username'] . "</a></td>" .
                                "<td>" . $item['net_profit'] / 100 . " bits</td>" .
                                "<td>" . $item['games_played'] . "</td>" .
                                "</tr>";
                        } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row centerWrap">
                <div class="centeredDiv">
                    <ul class="pagination">
                        <!--                        Go left (pagination) -->
                        <li class="<?php
                        if ($page > 1)
                            echo 'waves-effect';
                        else
                            echo 'disabled';
                        ?>"><a href="<?php
                            if ($page > 1)
                                rankLink($page - 1, $raAsc, $gaAsc, $order, $order[0]);
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
                                            href="<?php rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
                                        <?php echo $i; ?></a></li>
                            <?php endfor;
                        } else {
                            if ($page <= 8) {
                                for ($i = 1; $i <= 14; $i++) :?>
                                    <li class="<?php if ($page == $i)
                                        echo 'active';
                                    else
                                        echo 'waves-effect'; ?>"><a
                                                href="<?php rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
                                            <?php echo $i; ?></a></li>
                                <?php endfor;
                                echo '<li>...</li>'; ?>
                                <li class="<?php if ($page == $pageCount)
                                    echo 'active';
                                else
                                    echo 'waves-effect'; ?>"><a
                                            href="<?php rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
                                        <?php echo $pageCount; ?></a></li>
                                <?php
                            } else { ?>
                                <li class="<?php if ($page == 1)
                                    echo 'active';
                                else
                                    echo 'waves-effect'; ?>"><a
                                            href="<?php rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
                                        <?php echo 1; ?></a></li>
                                <?php
                                echo '<li>...</li>';
                                if ($pageCount - $page > 7) {
                                    for ($i = $page - 6; $i <= $page + 6; $i++) :?>
                                        <li class="<?php if ($page == $i)
                                            echo 'active';
                                        else
                                            echo 'waves-effect'; ?>"><a
                                                    href="<?php rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
                                                <?php echo $i; ?></a></li>
                                    <?php endfor;
                                    echo '<li>...</li>'; ?>
                                    <li class="<?php if ($page == $pageCount)
                                        echo 'active';
                                    else
                                        echo 'waves-effect'; ?>"><a
                                                href="<?php rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
                                            <?php echo $pageCount; ?></a></li>
                                    <?php
                                } else {
                                    for ($i = $pageCount - 13; $i <= $pageCount; $i++) :?>
                                        <li class="<?php if ($page == $i)
                                            echo 'active';
                                        else
                                            echo 'waves-effect'; ?>"><a
                                                    href="<?php rankLink($i, $raAsc, $gaAsc, $order, $order[0]); ?>">
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
                                rankLink($page + 1, $raAsc, $gaAsc, $order, $order[0]);
                            else
                                echo '#!';
                            ?>">
                                <i class="material-icons">chevron_right</i></a></li>
                    </ul>
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
                            echo "<tr>
            <td><b>" . $rowTable['rank'] . "</b></td>
                <td><a href='user_stats.php?user=" . $rowTable['username'] . "'>" . $rowTable['username'] . "</a></td>" .
                                "<td>" . $rowTable['net_profit'] / 100 . " bits</td>" .
                                "<td>" . $rowTable['games_played'] . "</td>" .
                                "</tr>";
                            ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <h3 class="center-align"><i class="medium material-icons vmid">error</i> User
                        '<?php echo htmlspecialchars($_GET['user']); ?>'
                        does not exist.</h3>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include "footer.php"; ?>
</body>

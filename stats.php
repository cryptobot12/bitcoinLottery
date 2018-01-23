<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/25/2017
 * Time: 6:37 PM
 */
session_start();

include "connect.php";
include "inc/login_checker.php";

$_SESSION['last_url'] = 'stats.php';

try {

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT deposits, withdrawals, net, games_played, gross_profit, our_profit,
                                      max_jackpot, total_users, total_plays FROM stats');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $deposits = $result['deposits'] / 100;
    $withdrawals = $result['withdrawals'] / 100;
    $net = $result['net'] / 100;
    $games_played = $result['games_played'];
    $gross_profit = $result['gross_profit'] / 100;
    $our_profit = $result['our_profit'] / 100;
    $max_jackpot = $result['max_jackpot'] / 100;
    $total_users = $result['total_users'];
    $total_plays = $result['total_plays'];

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
    <?php include "inc/header.php"; ?>
</header>
<main>
    <div class="row top-buffer-15">
        <div class="col l4 offset-l4 m8 offset-m2 s12">
            <div class="card z-depth-5">
                <div class="card-content">
                    <h3>Server stats</h3>
                    <table class="striped">
                        <tbody>
                        <tr>
                            <th>Deposits</th>
                            <td><?php echo $deposits; ?> bits</td>
                        </tr>
                        <tr>
                            <th>Withdrawals</th>
                            <td><?php echo $withdrawals; ?> bits</td>
                        </tr>
                        <tr>
                            <th>Net</th>
                            <td><?php echo $net; ?> bits</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th>Players Gross Profit</th>
                            <td><?php echo $gross_profit; ?> bits</td>
                        </tr>
                        <tr>
                            <th>Our Profit</th>
                            <td><?php echo $our_profit; ?> bits</td>
                        </tr>
                        <tr>
                            <th>Biggest Jackpot</th>
                            <td><?php echo $max_jackpot; ?> bits</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th>Total Users</th>
                            <td><?php echo $total_users; ?></td>
                        </tr>
                        <tr>
                            <th>Games Played</th>
                            <td><?php echo $games_played; ?></td>
                        </tr>
                        <tr>
                            <th>Total Plays</th>
                            <td><?php echo $total_plays; ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include "inc/footer.php"; ?>
</body>
</html>
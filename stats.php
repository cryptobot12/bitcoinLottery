<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/25/2017
 * Time: 6:37 PM
 */
session_start();

include "globals.php";
include "inc/login_checker.php";

$_SESSION['last_url'] = 'stats';

try {

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT deposits, withdrawals, net, games_played, gross_profit,
                                      max_jackpot, total_users FROM stats');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $deposits = $result['deposits'] / 100;
    $withdrawals = $result['withdrawals'] / 100;
    $net = $result['net'] / 100;
    $games_played = $result['games_played'];
    $gross_profit = $result['gross_profit'] / 100;
    $max_jackpot = $result['max_jackpot'] / 100;
    $total_users = $result['total_users'];

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$title = "Stats - BitcoinPVP";
include "inc/header.php";
?>
<main>
    <div class="row top-buffer-15">
        <div class="col l4 offset-l4 m8 offset-m2 s12">
            <div class="card">
                <div class="card-content">
                    <h3>Server Stats</h3>
                    <table class="highlight" >
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
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
<script>
    $(function () {

        var searchUser = $("#search_user");
        searchUser.on('keypress', function (e) {
            if (e.which === 13) {
                window.location.href = 'user_stats.php?user=' + searchUser.val();
            }
        });
    });

    $(document).ready(function () {
        M.AutoInit();
    });
</script>
<?php include "inc/footer.php"; ?>


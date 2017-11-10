<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/25/2017
 * Time: 6:37 PM
 */


try {
    $servername = "localhost";
    $conn = new PDO("mysql:host=$servername;dbname=lottery", "root", "5720297Ff");
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

    echo "<table>
            <tr>
                <th>Deposits</th>
                <td>" . $deposits . " bits</td>
            </tr>
            <tr>
                <th>Withdrawals</th>
                <td>" . $withdrawals . " bits</td>
            </tr>
            <tr>
                <th>Net</th>
                <td>" . $net . " bits</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th>Players Gross Profit</th>
                <td>" . $gross_profit . " bits</td>
            </tr>
            <tr>
                <th>Our Profit</th>
                <td>" . $our_profit . " bits</td>
            </tr>
            <tr>
                <th>Biggest Jackpot</th>
                <td>" . $max_jackpot . " bits</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th>Total Users</th>
                <td>$total_users</td>
            </tr>
            <tr>
                <th>Games Played</th>
                <td>$games_played</td>
            </tr>
            <tr>
                <th>Total Plays</th>
                <td>$total_plays</td>
            </tr>
          </table>";

}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}

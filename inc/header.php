<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/22/17
 * Time: 1:10 PM
 */
if ($logged_in) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $stmt = $conn->prepare('SELECT balance FROM user WHERE user_id = :user_id');
        $stmt->execute(array('user_id' => $user_id));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $balance = $row['balance'] / 100;
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}

 ?>
    <!-- Profile Structure -->
    <?php if (!empty($username)) : ?>
        <ul id="profileDropdown" class="dropdown-content">
            <li><a href="account.php"><i class="material-icons left">build</i>Account</a></li>
            <li><a href="php_actions/logout.php"><i class="material-icons left">exit_to_app</i>Logout</a></li>
        </ul>
    <?php endif; ?>
    <!-- Stats structure   -->
    <ul id="statsDropdown" class="dropdown-content">
        <li><a href="user_stats.php"><i class="material-icons left">person</i>User stats</a></li>
        <li><a href="game_info.php"><i class="material-icons left">assignment</i>Game Info</a></li>
        <li><a href="games_history.php"><i class="material-icons left">access_time</i>Games History</a></li>
        <li><a href="stats.php"><i class="material-icons left">assessment</i>Server Stats</a></li>
    </ul>
    <!-- Navbar goes here -->
    <nav>
        <div class="nav-wrapper blue darken-3">
            <a href="index.php" class="brand-logo left">BitcoinPVP</a>
            <ul id="nav-mobile" class="right .hide-on-small-only nav-letters">
                <li class=""><a href="help.php"><i class="material-icons left">help</i>Help</a></li>
                <li class=""><a href="rank.php"><i class="material-icons left">assistant_photo</i>Ranking</a></li>
                <li><a class="dropdown-button" href="#" data-activates="statsDropdown">
                        <i class="material-icons left">trending_up</i>Stats<i
                            class="material-icons right">arrow_drop_down</i></a></li>
                <li class="no-link-nav hide-on-med-and-down">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>
                <?php if  (!empty($username)): ?>
                    <li class="no-link-nav"><i class="material-icons left">account_balance_wallet</i>
                        Balance: <span id="my_balance"><?php echo $balance; ?></span> bits</li>
                    <li><a class="dropdown-button" href="#" data-activates="profileDropdown">
                            <?php echo $username; ?><i
                                class="material-icons right">arrow_drop_down</i></a>
                    </li>
                <?php else: ?>
                    <li><a href="registration.php">Register</a></li>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/22/17
 * Time: 1:10 PM
 */?>
    <!-- Profile Structure -->
    <?php if (isset($_SESSION['username']) && !empty($_SESSION['username'])) : ?>
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
        <div class="nav-wrapper green">
            <a href="index.php" class="brand-logo left">BitPVP</a>
            <ul id="nav-mobile" class="right .hide-on-small-only nav-letters">
                <li><a href="rank.php"><i class="material-icons left">assistant_photo</i><b>Ranking</b></a></li>
                <li><a class="dropdown-button" href="#" data-activates="statsDropdown">
                        <i class="material-icons left">trending_up</i><b>Stats</b><i
                            class="material-icons right">arrow_drop_down</i></a></a></li>
                <li class="no-link-nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>
                <?php if (isset($_SESSION['username']) && !empty($_SESSION['username'])): ?>
                    <li class="no-link-nav"><i class="material-icons left">account_balance_wallet</i>
                        <b>Balance: <span id="balanceNumber"><?php include 'update_balance_part.php'; ?></span> bits</b></li>
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

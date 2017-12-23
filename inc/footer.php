<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/22/17
 * Time: 1:12 PM
 */
$_SESSION['url'] = "{$_SERVER['REQUEST_URI']}";
?>
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
                    <li><a class="grey-text text-lighten-3" href="stats.php">Server Stats</a></li>
                    <li><a class="grey-text text-lighten-3" href="game_info.php">Game Info</a></li>
                    <li><a class="grey-text text-lighten-3" href="games_history.php">Games history</a></li>
                    <li><a class="grey-text text-lighten-3" href="user_stats.php">User stats</a></li>
                    <li><a class="grey-text text-lighten-3" href="#!">Guide</a></li>
                    <li><a class="grey-text text-lighten-3" href="registration.php">Registration</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-copyright">
        <div class="container">
            © <?php echo date('Y'); ?> Copyright BitcoinPVP
        </div>
    </div>
</footer>
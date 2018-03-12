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

function display_header($title, $base_dir, $username, $balance)
{
    echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <title>" . $title . "</title>
   

    <!-- Compiled and minified CSS -->
    <link rel=\"stylesheet\"
          href=\"https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css\">
    <link href=\"https://fonts.googleapis.com/icon?family=Material+Icons\" rel=\"stylesheet\">";

    echo "<!-- Custom style -->
    <link href=\"" . $base_dir . "css/style.css\" rel=\"stylesheet\">

    <!--Let browser know website is optimized for mobile-->
    <link rel=\"icon\" href=\"" . $base_dir . "img/favicon_symbol.png\" type=\"image/gif\" sizes=\"16x16\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/>
</head>
<body>
<header>";


//    Profile Structure
    if (!empty($username)) {
        echo "<ul id=\"profileDropdown\" class=\"dropdown-content\">
            <li><a href=\"" . $base_dir . "account\"><i class=\"material-icons left\">build</i>Account</a></li>
            <li><a href=\"" . $base_dir . "php_actions/logout\"><i class=\"material-icons left\">exit_to_app</i>Logout</a></li>
        </ul>";
    }

//    Stats structure
    echo "<ul id=\"statsDropdown\" class=\"dropdown-content\">
        <li><a href=\"" . $base_dir . "user_stats\"><i class=\"material-icons left\">person</i>User stats</a></li>
        <li><a href=\"" . $base_dir . "game_info\"><i class=\"material-icons left\">assignment</i>Game Info</a></li>
        <li><a href=\"" . $base_dir . "games_history\"><i class=\"material-icons left\">access_time</i>Games History</a></li>
        <li><a href=\"" . $base_dir . "stats\"><i class=\"material-icons left\">assessment</i>Server Stats</a></li>
    </ul>";
//     Navbar goes here

    echo "<nav id=\"nav-top\">
        <div class=\"nav-wrapper black darken-3\">
            <a href=\"" . $base_dir . "\" class=\"brand-logo left\"><img src=\"" . $base_dir . "img/nav-logo.png\" height=\"56\" ></a>
            <ul id=\"nav-mobile\" class=\"right .hide-on-small-only nav-letters\">
                <li class=\"\"><a href=\"" . $base_dir . "help\"><i class=\"material-icons left\">help</i>Help</a></li>
                <li class=\"\"><a href=\"" . $base_dir . "rank\"><i class=\"material-icons left\">assistant_photo</i>Ranking</a></li>
                <li><a class=\"dropdown-button\" href=\"#\" data-activates=\"statsDropdown\">
                        <i class=\"material-icons left\">trending_up</i>Stats<i
                            class=\"material-icons right\">arrow_drop_down</i></a></li>
                <li class=\"no-link-nav hide-on-med-and-down\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>";
    if (!empty($username)) {
        echo "<li class=\"no-link-nav\"><i class=\"material-icons left\">account_balance_wallet</i>
                        Balance: <span id=\"my_balance\"><?php echo $balance; ?></span> bits</li>
                    <li><a class=\"dropdown-button\" href=\"#\" data-activates=\"profileDropdown\">
                            <?php echo $username; ?><i
                                class=\"material-icons right\">arrow_drop_down</i></a>
                    </li>";
    } else {
        echo "<li><a href=\"" . $base_dir . "registration\">Register</a></li>
                    <li><a href=\"" . $base_dir . "login\">Login</a></li>";
    }

    echo "</ul>
        </div>
    </nav>
</header>";
}

?>

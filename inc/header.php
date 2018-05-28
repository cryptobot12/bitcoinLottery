<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/22/17
 * Time: 1:10 PM
 */

require_once '/home/luckiestguyever/PhpstormProjects/bitcoinLottery/vendor/autoload.php';

if ($logged_in) {
    $driver = new \Nbobtc\Http\Driver\CurlDriver();
    $driver
        ->addCurlOption(CURLOPT_VERBOSE, true)
        ->addCurlOption(CURLOPT_STDERR, '/var/logs/curl.err');

    $client = new \Nbobtc\Http\Client('http://puppetmaster:vz6qGFsHBv5auSSDhTPWPktVu@localhost:18332');
    $client->withDriver($driver);

    $command = new \Nbobtc\Command\Command('getbalance', $username);

    /** @var \Nbobtc\Http\Message\Response */
    $response = $client->sendCommand($command);

    /** @var string */
    $output = json_decode($response->getBody()->getContents());

    $balance = $output->result * 1000000;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="<?php echo $base_dir; ?>css/style.css" rel="stylesheet">

    <!--Let browser know website is optimized for mobile-->
    <link rel="icon" href="<?php echo $base_dir; ?>img/favicon_symbol.png" type="image/gif" sizes="16x16">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<header>


    <!--    //    Profile Structure-->
    <?php if (!empty($username)) : ?>
        <ul id="profileDropdown" class="dropdown-content">
            <li><a href="<?php echo $base_dir; ?>account"><i class="material-icons left">build</i>Account</a></li>
            <li><a href="<?php echo $base_dir; ?>actions/logout"><i
                            class="material-icons left">exit_to_app</i>Logout</a>
            </li>
        </ul>
    <?php endif; ?>

    <!--    //    Stats structure-->
    <ul id="statsDropdown" class="dropdown-content">
        <li><a href="<?php echo $base_dir; ?>rank"><i class="material-icons left">assistant_photo</i>Ranking</a>
        </li>
        <li><a href="<?php echo $base_dir; ?>user-stats"><i class="material-icons left">person</i>User Stats</a></li>
        <li><a href="<?php echo $base_dir; ?>game-info"><i class="material-icons left">assignment</i>Game Info</a></li>
        <li><a href="<?php echo $base_dir; ?>games-history"><i class="material-icons left">access_time</i>Games History</a>
        </li>
        <li><a href="<?php echo $base_dir; ?>stats"><i class="material-icons left">assessment</i>Server Stats</a></li>
    </ul>
    <!--    //     Navbar goes here-->

    <nav id="nav-top">
        <div class="nav-wrapper black darken-3">
            <a href="#" data-target="slide-out" class="sidenav-trigger show-on-medium-and-down"><i
                        class="material-icons">menu</i></a>
            <a href="<?php echo $base_dir; ?>" class="brand-logo"><img
                        src="<?php echo $base_dir; ?>img/nav-logo.png"></a>
            <ul id="nav-mobile" class="right hide-on-med-and-down nav-letters">
                <li><a href="<?php echo $base_dir; ?>help"><i class="material-icons left">help</i>Help</a></li>

                <li><a class="dropdown-trigger" href="#" data-target="statsDropdown">
                        <i class="material-icons left">trending_up</i>Stats<i
                                class="material-icons right">arrow_drop_down</i></a></li>
                <li class="no-link-nav hide-on-med-and-down">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>
                <?php if (!empty($username)) : ?>
                    <li class="no-link-nav"><i class="material-icons left">account_balance_wallet</i>
                        Balance: <span id="my_balance"><?php echo $balance; ?></span> bits
                    </li>
                    <li><a class="dropdown-trigger" href="#" data-target="profileDropdown">
                            <?php echo $username; ?><i
                                    class="material-icons right">arrow_drop_down</i></a>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo $base_dir; ?>registration">Register<i
                                    class="material-icons right white-text">person_add</i></a></li>
                    <li><a href="<?php echo $base_dir; ?>login">Login<i
                                    class="material-icons right white-text">navigate_next</i></a></li>
                <?php endif; ?>

            </ul>
        </div>
    </nav>

    <ul id="slide-out" class="sidenav">
        <?php if (!empty($username)) : ?>
            <li class="black"><a class="white-text"><i class="material-icons white-text">account_balance_wallet</i>
                    Balance: <span id="my_balance"><?php echo $balance; ?></span> bits</a></li>

            <li>
                <a class="subheader"><b>Welcome, <?php echo $username; ?></b></a>
            </li>
            <li><a href="<?php echo $base_dir; ?>account"><i
                            class="material-icons">build</i>Account</a></li>
            <li><a href="<?php echo $base_dir; ?>actions/logout"><i
                            class="material-icons">exit_to_app</i>Logout</a></li>
            <li>
                <div class="divider"></div>
            </li>
        <?php else: ?>
            <li class="black"><a class="white-text" href="<?php echo $base_dir; ?>registration"><i
                            class="material-icons white-text">person_add</i>Register</a></li>
            <li class="black"><a class="white-text" href="<?php echo $base_dir; ?>login"><i
                            class="material-icons white-text">navigate_next</i>Login</a></li>
        <?php endif; ?>
        <li><a class="waves-effect" href="<?php echo $base_dir; ?>rank"><i
                        class="material-icons">assistant_photo</i>Ranking</a></li>
        <li><a class="waves-effect" href="<?php echo $base_dir; ?>user-stats"><i class="material-icons">person</i>User
                Stats</a></li>
        <li><a class="waves-effect" href="<?php echo $base_dir; ?>game-info"><i
                        class="material-icons">assignment</i>Game Info</a></li>
        <li><a class="waves-effect" href="<?php echo $base_dir; ?>games-history"><i
                        class="material-icons">access_time</i>Games
                History</a></li>
        <li><a class="waves-effect" href="<?php echo $base_dir; ?>stats"><i class="material-icons">assessment</i>Server
                Stats</a></li>
        <li>
            <div class="divider"></div>
        </li>
        <li><a class="waves-effect" href="<?php echo $base_dir; ?>help"><i class="material-icons">help</i>Help</a>
        </li>
    </ul>
</header>
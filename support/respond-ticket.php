<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 3/27/18
 * Time: 5:45 PM
 */
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require '../vendor/autoload.php';

include '../function.php';
include '../globals.php';

$ticket_id = $_GET['ticket'];
$validator = $_GET['val'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $conn->prepare('SELECT ticket_id, subject, content, submitted_on, validator FROM ticket 
WHERE ticket_id = :ticket_id');
    $stmt->execute(array('ticket_id' => $ticket_id));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $hashed_validator = $result['validator'];

    if (password_verify($validator, $hashed_validator)) {
        $ticket_id = $result['ticket_id'];
        $subject = $result['subject'];
        $content = $result['content'];
        $submitted_on = $result['submitted_on'];

    } else {
        header("Location: " . $base_dir . "lost");
        die();
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support</title>

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="<?php echo $base_dir; ?>css/style.css" rel="stylesheet">

    <!--Let browser know website is optimized for mobile-->
    <link rel="icon" href="<?php echo $base_dir; ?>img/favicon_symbol.png" type="image/gif" sizes="16x16">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="word-wrap: break-word;">
<header>
    <!--    //     Navbar goes here-->

    <nav id="nav-top">
        <div class="nav-wrapper black darken-3">
            <a class="brand-logo left"><img
                        src="<?php echo $base_dir; ?>img/nav-logo.png" height="56"></a>

        </div>
    </nav>
</header>
<main class="valign-wrapper">
    <div class="container">
        <div class="col l10 offset-l1">
            <div class="row">
                <div class="col offset-l1">
                    <h5><b>Ticket #<?php echo $ticket_id; ?></b></h5>
                </div>
            </div>
            <div class="row">
                <div class="col l8 offset-l2 m10 offset-m1 s12">
                    <div class="row">
                        <div class="col m2 s4"><b>Subject: </b></div>
                        <div class="col m10 s8">
                            <?php echo $subject; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col m2 s4"><b>Content: </b></div>
                        <div class="col m10 s8"><?php echo $content; ?></div>
                    </div>
                    <div class="row">
                        <div class="col m2 s4"><b>Submitted on: </b></div>
                        <div class="col m10 s8"><?php echo $submitted_on; ?></div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</main>
<footer class="page-footer grey lighten-4">
    <div class="container">
        <div class="row">
            <div class="col l6 s12">
                <h5 class="black-text">License</h5>
                <p class="black-text text-lighten-4">Peruvian license N48D1489A-ADS4</p>
            </div>
        </div>
    </div>
    <div class="footer-copyright">
        <div class="container black-text">
            © <?php echo date('Y'); ?> Copyright BitcoinPVP
        </div>
    </div>
</footer>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
</body>
</html>
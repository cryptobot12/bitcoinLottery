<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 3/30/18
 * Time: 4:54 PM
 */
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require '../../vendor/autoload.php';

include '../../function.php';
include '../../globals.php';

$response = $_POST['response'];
$ticket_id = $_POST['ticket_id'];
$validator = $_POST['validator'];

$recaptcha_response = $_POST['g-recaptcha-response'];

/* Captcha verifying */
$privatekey = "6Lf1d0EUAAAAAPhwWXktY_b1rBWR_ClydgLfj8g1";


$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = array(
    'secret' => $privatekey,
    'response' => $_POST["g-recaptcha-response"]
);
$options = array(
    'http' => array(
        'method' => 'POST',
        'content' => http_build_query($data)
    )
);
$context = stream_context_create($options);
$verify = file_get_contents($url, false, $context);
$captcha_success = json_decode($verify);

if ($captcha_success->success) {
    if (!empty($ticket_id) && !empty($validator)) {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $stmt = $conn->prepare('SELECT user_id, ticket_id, subject, content, submitted_on, validator FROM ticket
WHERE ticket_id = :ticket_id');
            $stmt->execute(array('ticket_id' => $ticket_id));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $hashed_validator = $result['validator'];

            if (password_verify($validator, $hashed_validator) && !empty($hashed_validator)) {
                $ticket_id = $result['ticket_id'];
                $subject = $result['subject'];
                $content = $result['content'];
                $submitted_on = $result['submitted_on'];

                $stmt = $conn->prepare('SELECT username, email FROM user WHERE user_id = :user_id');
                $stmt->execute(array('user_id' => $result['user_id']));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $username = $result['username'];
                $email = $result['email'];

                $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
                try {
                    //Server settings
                    $mail->SMTPDebug = 0;                                 // Enable verbose debug output
                    $mail->isSMTP();                                      // Set mailer to use SMTP
                    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
                    $mail->SMTPAuth = true;                               // Enable SMTP authentication
                    $mail->Username = 'no-reply@bitcoinpvp.net';                 // SMTP username
                    $mail->Password = 'ECV)88y~7C9yrSL8uxhNSnpC+';                           // SMTP password
                    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
                    $mail->Port = 587;                                    // TCP port to connect to

                    //Recipients
                    $mail->setFrom('support@bitconpvp.net', 'BitcoinPVP Support Team');
                    $mail->addAddress($email);     // Add a recipient


                    //Content
                    $mail->CharSet = 'UTF-8';
                    $mail->isHTML(true);                                  // Set email format to HTML
                    $mail->Subject = '[BitcoinPVP Support] Re: ' . $subject;
                    $mail->Body = '<div style="width: 700px; margin: 0 auto;">
                                        <div style="background: black"><img
                                                    src="http://www.bitcoinpvp.net/img/nav-logo.png" height="40"></div>

                                        <div style="width: 75%; margin: 50px auto; color: black;">

                                            <p>Greetings <span
                                                        style="color: red;"><b>' . $username . '</b></span>,</p>
                                            <p>Just wanted to let you know that I’ve updated your request
                                                (# ' . $ticket_id . ' ?>).
                                                Just reply to this e-mail to add any additional comments.</p>

                                            <div style="width: 90%; margin: auto; color: black;">
                                                <p><b>' . $subject . '</b></p>
                                                <p><i>' . $content . '</i></p>
                                            </div>

                                            <br>
                                            <div>' . $response . '</div>

                                            <p>BitcoinPVP Support Team</p>
                                        </div>

                                        <div style="background: black; color: white; padding: 10px;">
                                            © <?php echo date(\'Y\'); ?> Copyright BitcoinPVP
                                        </div>
                                    </div>';

                    $mail->send();
                } catch (Exception $e) {
                    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
                }
            } else {
                header("Location: " . $base_dir . "lost");
                die();
            }

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    } else {
        header("Location: " . $base_dir . "lost");
        die();
    }
} else {
    header("Location: " . $base_dir . "lost");
    die();
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support</title>

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/css/materialize.min.css">
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
        <div class="centerWrap">
            <div class="centeredDiv">
                <span class="h5Span"><i class="material-icons left">check</i>Your message has been sent.</span>
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
<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 3/27/18
 * Time: 5:45 PM
 */

include '../function.php';
include '../globals.php';

$ticket_id = $_GET['ticket'];
$validator = $_GET['val'];

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

            $stmt = $conn->prepare('SELECT username FROM user WHERE user_id = :user_id');
            $stmt->execute(array('user_id' => $result['user_id']));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $username = $result['username'];

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
        <div class="col l10 offset-l1">
            <div class="row"></div>
            <div class="row"></div>
            <div class="row"></div>
            <div class="row">
                <div class="col offset-l1">
                    <h5><b>Ticket #<?php echo $ticket_id; ?></b></h5>
                </div>
            </div>
            <div class="row">
                <div class="col l8 offset-l2 m10 offset-m1 s12">
                    <div class="row">
                        <div class="col m2 s4"><b>User:</b></div>
                        <div class="col m10 s8">
                            <?php echo $username; ?>
                        </div>
                    </div>
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
            <div class="row">
                <div class="col l8 offset-l2 m10 offset-m1 s12">
                    <form id="response" method="post"
                          action="<?php echo $support_base_dir; ?>support-actions/respond-ticket-action">
                        <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                        <input type="hidden" name="validator" value="<?php echo $validator; ?>">
                        <div class="input-field col s12">
                            <textarea id="message" name="response" class="materialize-textarea"></textarea>
                            <label for="message">Message</label>
                        </div>
                        <div class="input-field col s12">
                            <div class="card-panel">
                                <h6><b>Please review your message one more time...</b></h6>
                                <div class="row"></div>
                                <div>
                                    <div style="width: 700px; margin: 0 auto;">
                                        <div style="background: black"><img
                                                    src="https://www.bitcoinpvp.net/img/nav-logo.png" height="40"></div>

                                        <div style="width: 75%; margin: 50px auto; color: black;">

                                            <p>Greetings <span
                                                        style="color: red;"><b><?php echo $username; ?></b></span>,</p>
                                            <p>Just wanted to let you know that I’ve updated your request
                                                (#<?php echo $ticket_id; ?>).
                                                Just reply to this e-mail to add any additional comments.</p>

                                            <div style="width: 90%; margin: auto; color: black;">
                                                <p><b><?php echo $subject; ?></b></p>
                                                <p><i><?php echo $content; ?></i></p>
                                            </div>

                                            <br>
                                            <div id="message_preview"></div>

                                            <p>BitcoinPVP Support Team</p>
                                        </div>

                                        <div style="background: black; color: white; padding: 10px;">
                                            © <?php echo date('Y'); ?> Copyright BitcoinPVP
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="input-field col s12">
                            <button id="preview_button" class="g-recaptcha waves-effect waves-light btn right
                             amber darken-3"
                                    data-sitekey="6Lf1d0EUAAAAAHlf_-pGuqjxWwBfy-UVkdJt-xLf"
                                    data-callback="sendMessage">Send<i class="material-icons right">send</i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row"></div>
            <div class="row"></div>
            <div class="row"></div>
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
<script src="<?php echo $support_base_dir; ?>js/support.js"></script>
<script>
    function sendMessage() {
        $("#response").submit();
    }
</script>
<!-- Recaptcha-->
<script src='https://www.google.com/recaptcha/api.js'></script>
</body>
</html>
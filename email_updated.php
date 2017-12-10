<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/1/17
 * Time: 9:55 PM
 */

session_start();

if (!(isset($_SESSION['email_updated']) && !empty($_SESSION['email_updated']))) {
    header("Location: error.php");
} else {
    if ($_SESSION['email_updated'] == true)
        $_SESSION['email_updated'] = false;
    else
        header("Location: error.php");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bitcoin</title>
    <!--    Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
    <script src="js/autobahn.js"></script>
    <script>
        window.setTimeout(function(){

            // Move to a new location or you can do something else
            window.location.href = "<?php echo $_SESSION['url']; ?>";

        }, 5000);
    </script>

    <link href="css/style.css" rel="stylesheet">

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<header>
    <?php include "inc/header.php" ?>
</header>
<main class="valign-wrapper">
    <div class="container" >
        <h4 class="center-align"><i class="medium material-icons vmid">check</i> Your email has been updated.</h4>
    </div>
</main>
<?php include "inc/footer.php"; ?>
</body>


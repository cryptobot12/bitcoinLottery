<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/1/17
 * Time: 7:24 PM
 */
session_start();

include '../random.php';
include '../connect.php';

$new_email = $_POST['new-email'];
$confirm_email = $_POST['confirm-email'];
$user_id = $_SESSION['user_id'];

if ($new_email == $confirm_email) {
    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        //Valid email

        $code = rand_string(4);

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            /*****Checking if email is taken ****/

            $stmt = $conn->prepare('SELECT COUNT(email) AS email_count FROM user WHERE email = :new_email');
            $stmt->execute(array('new_email' => $new_email));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $email_count = $result['email_count'];

            if ($email_count == 0) {

                $stmt = $conn->prepare('SELECT email FROM user WHERE user_id = :user_id');
                $stmt->execute(array('user_id' => $user_id));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $email = $result['email'];

                $stmt = $conn->prepare('UPDATE user SET code = :code, code_expires = DATE_ADD(NOW(), INTERVAL 30 MINUTE),
              new_email = :new_email WHERE user_id = :user_id');
                $stmt->execute(array('code' => $code, 'user_id' => $user_id, 'new_email' => $new_email));

                /* Send email with code here */

                $to = $email;
                $subject = "Updating email";

                $message = "<b>This is HTML message.</b>";
                $message .= "<h1>This is headline.</h1>";
                $message .= "<h1>Your code is $code</h1>";

                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                $retval = mail($to, $subject, $message, $headers);

                /*if ($retval == true) {
                    echo "Message sent successfully...";
                } else {
                    echo "Message could not be sent...";
                }*/

                /*****************************/
            }

            header("Location: ../account.php");
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    } else {
        //Invalid email
        header("Location: ../error.php");
    }
}
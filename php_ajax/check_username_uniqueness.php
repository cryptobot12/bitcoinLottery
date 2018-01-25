<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/19/17
 * Time: 8:40 PM
 */

session_start();

include "../connect.php";
include "../inc/login_checker.php";

$username = strtolower($_POST['username']);

//For transfer
if ($logged_in) {

    if ($username != $_SESSION['username']) {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            /*****Checking if email is taken ****/

            $stmt = $conn->prepare("SELECT COUNT(user_id) AS user_count FROM user WHERE username = :username");
            $stmt->execute(array('username' => $username));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_count = $result['user_count'];

            $user_exists = false;

            if ($user_count > 0) {
                $user_exists = true;
            }

            $returnAjax = json_encode(array('exists' => $user_exists, 'same' => false));

            echo $returnAjax;

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    } else {
        $returnAjax = json_encode(array('exists' => true,'same' => true));
        echo $returnAjax;
    }

} //For user creation
else {


    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        /*****Checking if email is taken ****/

        $stmt = $conn->prepare("SELECT COUNT(user_id) AS user_count FROM user WHERE username = :username");
        $stmt->execute(array('username' => $username));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_count = $result['user_count'];

        $user_exists = false;

        if ($user_count > 0) {
            $user_exists = true;
        }

        $returnAjax = json_encode(array('taken' => $user_exists));

        echo $returnAjax;

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}

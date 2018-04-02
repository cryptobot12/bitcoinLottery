<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 12/1/17
 * Time: 10:27 PM
 */
session_start();

include "../globals.php";

$email = strtolower($_POST['email']);

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    /*****Checking if email is taken ****/

    //echo $email;

    $stmt = $conn->prepare("SELECT COUNT(email) AS email_count FROM user WHERE email = :new_email");
    $stmt->execute(array('new_email' => $email));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $email_count = $result['email_count'];

    $email_taken = false;

    if ($email_count > 0) {
        $email_taken = true;
    }

    $returnAjax = json_encode(array('taken' => $email_taken));

    echo $returnAjax;

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

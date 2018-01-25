<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 1/25/2018
 * Time: 1:13 PM
 */

include "../connect.php";

//To run in cron jobs
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    //Delete the users who did not confirmed their email
    $stmt = $conn->prepare('SELECT user_id FROM email_confirmation WHERE expires < CURRENT_TIMESTAMP');
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $item) {
        $stmt = $conn->prepare('DELETE FROM user WHERE user_id = :user_id');
        $stmt->execute(array('user_id' => $item['user_id']));
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
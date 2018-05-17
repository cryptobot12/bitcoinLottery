<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 1/30/18
 * Time: 12:25 AM
 */

include "../globals.php";
include "../inc/login_checker.php";

$chat_message = htmlspecialchars($_POST['message']);


if ($logged_in && strlen($chat_message) > 0 && strlen($chat_message) <= 180) {

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $stmt = $conn->prepare("SELECT count(message_id) AS message_count FROM chat 
WHERE DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %H:%i') = DATE_FORMAT(sentat, '%Y-%m-%d %H:%i')");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $message_count = $result['message_count'];

        if ($message_count <= 5) {
            $stmt = $conn->prepare('INSERT INTO chat(user_id, username, message, sentat)
VALUES (:user_id, :username, :message, CURRENT_TIMESTAMP)');
            $stmt->execute(array('user_id' => $user_id,  'username' => $username, 'message' => $chat_message));

            $stmt = $conn->prepare('SELECT sentat FROM chat WHERE message_id = LAST_INSERT_ID()');
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $sentat = $result['sentat'];

            //Broadcasting
            $entryData = array('category' => 'all', 'option' => 3, 'user' => $username, 'chat_message' => $chat_message,
                'sentat' => $sentat);

            $context = new ZMQContext();
            $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
            $socket->connect("tcp://localhost:5555");

            $socket->send(json_encode($entryData));
        }
        else {
            echo "tmm";
        }

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    } catch (ZMQSocketException $e) {
        echo $e->getMessage();
    }


} else {
    echo "Either you haven't logged in or your message content is illegal.";
}
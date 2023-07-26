<?php


    session_start();

    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: signIn.php');
    exit;
    }

    if (!isset($_POST['messageID'])) {
    header('Location: messages.php');
    exit;
    }

    include('connectionData.txt');
    $conn = mysqli_connect($server, $user, $pass, $dbname, $port) or die('Error connecting to MySQL server.');


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['decryptedMessage']) && isset($_POST['originalMessage'])) {
        $originalMessage = $_POST['originalMessage'];
        $decryptedMessage = $_POST['decryptedMessage'];
        $messageID = $_POST['messageID'];
        
        if ($originalMessage === $decryptedMessage) {
            $sql = "select m.senderID, m.receiverID from Messages m WHERE m.messageID = '$messageID'";   
            $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
            $row = $result->fetch_assoc();
            $sender = $row['senderID'];
            $receiver = $row['receiverID'];
            $sql = "UPDATE Messages 
            SET status = 'Accepted',
                senderID = '$receiver',
                receiverID = '$sender'
            WHERE messageID = '$messageID'";
            $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
            mysqli_close($conn);
            echo "Comfirmation! Standby...";
            header('Refresh: 3; URL=messages.php');
            exit;
        }
        else {
            mysqli_close($conn);
            echo "no match. Standby...";
            header('Refresh: 3; URL=validationPage.php');
            exit;
        }
        }
    }
  ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('Location: signIn.php');
  exit;
}

if (!isset($_POST['messageID'])) {
  header('Location: messages.php');
  exit;
}

$messageID = $_POST['messageID'];

include('connectionData.txt');
$conn = mysqli_connect($server, $user, $pass, $dbname, $port) or die('Error connecting to MySQL server.');

$clientID = $_SESSION['clientID'];
$sql = "SELECT * FROM Messages WHERE messageID = '$messageID' AND receiverID = '$clientID'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
  $deleteSql = "DELETE FROM Messages WHERE messageID = '$messageID'";
  $deleteResult = mysqli_query($conn, $deleteSql);

  if ($deleteResult) {
    echo "Message deleted successfully.";
  } else {
    echo "Failed to delete the message.";
  }
} else {
  echo "Invalid message ID.";
}

mysqli_close($conn);

header('Location: messages.php');
exit;
?>

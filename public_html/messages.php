<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('Location: signIn.php');
  exit;
}

if (isset($_POST['signOut'])) {
  header('Location: signOut.php');
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Profile Page</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
</head>
  <body>
  <h1>Welcome, <?php echo $_SESSION['userName']; ?></h1>

  <div>
    <a href="profile.php" class="return-link">Click here to return to profile</a>
  </div>

  <div>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
          <input type="submit" name="signOut" value="Sign Out" class= sign-out-button>
    </form>
  </div>

</body>
</html>



<?php
include('connectionData.txt');
$conn = mysqli_connect($server, $user, $pass, $dbname, $port) or die('Error connecting to MySQL server.');
$clientID = $_SESSION['clientID'];

$sql = "SELECT * FROM Messages WHERE receiverID = '$clientID'";
$messages = mysqli_query($conn, $sql) or die(mysqli_error($conn));
if (!$messages) {
  trigger_error('Could not access Messages', E_USER_ERROR);
}

foreach ($messages as $message) {
  $messageID = $message['messageID'];
  $sender = $message['senderID'];
  $status = $message['status'];
  $sentAt = $message['sentAt'];
  $artName = $message['artName'];
  $buyer = $message['buyerUname'];

  $buttonColorDecline = 'black';
  $buttonColorAccept = 'white';
  $fontColorDecline = 'white';
  $fontColorAccept = 'black';


  if ($status === 'Pending') {
    echo '<p>(Request to Buy) Art Name: ' . $artName . '&nbsp;&nbsp;&nbsp;&nbsp;Buyer: ' . $buyer . '</p>';

    // Delete button (decline)
    echo '<form action="deleteMessage.php" method="POST">';
    echo '<input type="hidden" name="messageID" value="' . $messageID . '">';
    echo '<button style="background-color: ' . $buttonColorDecline . '; color: ' . $fontColorDecline . '" type="submit" name="delete">Decline</button>';
    echo '</form>';

    // Validate button (confirm)
    echo '<form action="validationPage.php" method="POST">';
    echo '<input type="hidden" name="messageID" value="' . $messageID . '">';
    echo '<button style="background-color: ' . $buttonColorAccept . '; color: ' . $fontColorAccept . '" type="submit" name="validate">Confirm</button>';
    echo '</form>';
  } else {
    echo '<p>(Confirm Received) Art Name: ' . $artName . '&nbsp;&nbsp;&nbsp;&nbsp;Buyer: ' . $buyer . '</p>';

    echo '<form action="deleteMessage.php" method="POST">';
    echo '<input type="hidden" name="messageID" value="' . $messageID . '">';
    echo '<button style="background-color: ' . $buttonColorDecline . '; color: ' . $fontColorDecline . '" type="submit" name="delete">Decline</button>';
    echo '</form>';

    echo '<form action="finalValidation.php" method="POST">';
    echo '<input type="hidden" name="messageID" value="' . $messageID . '">';
    echo '<button style="background-color: ' . $buttonColorAccept . '; color: ' . $fontColorAccept . '" type="submit" name="validate">Confirm</button>';
    echo '</form>';
  }
  echo '</div>';
}

mysqli_free_result($result);
mysqli_close($conn);
?>











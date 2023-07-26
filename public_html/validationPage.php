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

$sql = "SELECT publicKey FROM Clients WHERE clientID = '$clientID'";
$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

$publicKey = $row['publicKey'];

$publicKey = wordwrap($publicKey, 64, "\n", true);
$publicKey = "-----BEGIN PUBLIC KEY-----\n" . $publicKey . "\n-----END PUBLIC KEY-----";
//  cat encrypted.txt | base64 -d | openssl rsautl -decrypt -inkey privateKey.pem > decrypted.txt
$originalMessage = bin2hex(random_bytes(16));
echo $originalMessage;
$publicKeyResource = openssl_get_publickey($publicKey);
openssl_public_encrypt($originalMessage, $encryptedMessage, $publicKeyResource);
$encodedMessage = base64_encode($encryptedMessage);


mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Encrypt Message</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
  <script>
    function copyToClipboard() {
      var copyText = document.getElementById("encryptedMessage");
      copyText.select();
      copyText.setSelectionRange(0, 99999); 
      document.execCommand("copy");
    }
  </script>
</head>
<body>
  <h1>Encrypt Message</h1>
  <div>
    <textarea id="encryptedMessage" rows="7" cols="50"><?php echo $encodedMessage; ?></textarea>
    <button onclick="copyToClipboard()">Copy Message</button>
  </div>
  <h2>Decrypted Message</h2>
  <div>
    <form action="validationPageOPS.php" method="POST" >
      <input type="hidden" name="messageID" value="<?php echo $_POST['messageID'] ?? ''; ?>">
      <input type="hidden" name="originalMessage" value="<?php echo $originalMessage ?? ''; ?>">

      <label for="decryptedMessage"></label>
      <input type="text" name="decryptedMessage" id="decryptedMessage"><br>
      <input type="submit" value="Submit Request" class="form-submit">
    </form>
  </div>
</body>
</html>




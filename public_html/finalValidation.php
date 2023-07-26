<?php
// Start the session
session_start();

// Check if user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('Location: signOut.php');
  exit;
}

if (isset($_POST['signOut'])) {
  header('Location: signOut.php');
  exit;
}

if (!isset($_POST['messageID'])) {
    header('Location: messages.php');
    exit;
  }
?>

<!-- Protected page content here -->
<!DOCTYPE html>
<html>
<head>
  <title>Buy Request </title>
  <link rel="stylesheet" type="text/css" href="styles.css">
  <script>

    function handleFileSelection() {
      const imageFile1 = document.getElementById('imageFile1').files[0];
      const imageFile2 = document.getElementById('imageFile2').files[0];
      
      calculateHash(imageFile1, 'hashHex1');
      calculateHash(imageFile2, 'hashHex2');
      
      return false;
    }

    function calculateHash(imageFile, inputName) {
      if (!imageFile) {
        alert('Please select an image file.');
        return false;
      }
      const reader = new FileReader();
      reader.onload = async function (e) {
        const imageBuffer = e.target.result;
        const hashBuffer = await crypto.subtle.digest('SHA-256', imageBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hashHex = hashArray.map(byte => byte.toString(16).padStart(2, '0')).join('');
        console.log('Hash value (hex):', hashHex);

        const form = document.getElementById('myForm');
        const hashInput = document.createElement('input');
        hashInput.type = 'hidden';
        hashInput.name = inputName;
        hashInput.value = hashHex;
        form.appendChild(hashInput);

        form.submit();
      };
      reader.readAsArrayBuffer(imageFile);
      return false; // Prevent default form submission
    }
  </script>
</head>

  <body>
  <h1>User: <?php echo $_SESSION['userName']; ?></h1>

  <h5>Authenticate original image and establish new Public Image</h5>

  <!-- php script that prints art owned by client -->

<form action="" method="POST" id="myForm" onsubmit="return handleFileSelection()">
  <input type="hidden" name="messageID" value="<?php echo $_POST['messageID'] ?? ''; ?>">

  <label for="imageFile1" class="form-label">Original Image:</label>
  <input type="file" name="item1[imageFile1]" id="imageFile1" accept="image/jpeg" class="form-input">

  <label for="imageFile2" class="form-label">Public Image:</label>
  <input type="file" name="item1[imageFile2]" id="imageFile2" accept="image/jpeg" class="form-input">

  <input type="submit" value="Submit Request" class="form-submit">
</form>

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


<!-- ^ styling submission boxes -->


<!-- Code to submit art to database -->
<?php
include('connectionData.txt');
$conn = mysqli_connect($server, $user, $pass, $dbname, $port) or die('Error connecting to MySQL server.');
// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Access the hash value
  if (empty($_POST['hashHex1']) || empty($_POST['hashHex2'])) {
    echo "Please fill in both fields.";
    return;
  }
  $messageID = $_POST['messageID'];
  $clientID = $_SESSION['clientID'];
  $originalHash = $_POST['hashHex1'];
  $publicCopy = $_POST['hashHex2'];

  $sql = "select a.artID 
            from Art a 
            join Messages m on m.artID = a.artID
            where a.hashArtID = '$originalHash'
            and m.messageID = '$messageID'";

  $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
  $row = $result->fetch_assoc();
  $artID = $row['artID'];



  if ($artID === NULL){
    echo "no match found";
  }
  else {
    $sql = "update Art a set ownerID = '$clientID' where a.artID = '$artID'";
    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    $sql = "INSERT INTO OwnershipHx (clientID, artID, wmHash) VALUES ('$clientID', '$artID', '$publicCopy')";
    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    $sql = "DELETE FROM Messages WHERE messageID = '$messageID'";
    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));


    echo "Success: database updated, returning to messages";
    header('Refresh: 3; URL=messages.php');
    exit; 
  }
}
mysqli_close($conn);

?>






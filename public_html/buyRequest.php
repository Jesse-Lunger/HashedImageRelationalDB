<?php
// Start the session
session_start();

// Check if user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  // Redirect to the login page
  header('Location: signOut.php');
  exit;
}

if (isset($_POST['signOut'])) {
  // Redirect to the sign-out page
  header('Location: signOut.php');
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
  <h1>Welcome, <?php echo $_SESSION['userName']; ?></h1>

  <!-- php script that prints art owned by client -->

<form action="" method="POST" id="myForm" onsubmit="return handleFileSelection()">

  <label for="imageFile1" class="form-label">Upload Sellers Image Here:</label>
  <input type="file" name="item1[imageFile1]" id="imageFile1" accept="image/jpeg" class="form-input">

  <label for="imageFile2" class="form-label">Upload Art Image Here:</label>
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
  $sellerHash = $_POST['hashHex1'];
  $artHash = $_POST['hashHex2'];

  $sql = "select c.clientID, a.artName, a.artID from Clients c 
          join OwnershipHx o using(clientID)
          join Art a on o.artID = a.artID
          where c.idHash = '$sellerHash' 
          and o.wmHash = '$artHash'";
  
  $result = $conn->query($sql);
  $row = $result->fetch_assoc();
  $sellerID = $row['clientID'];
  $userName = $_SESSION['userName'];
  $artID = $row['artID'];
  

  if ($sellerID == "") {
    echo "No Match found";
    return false;
  }
  $artName = $row["artName"];

  $buyerID = $_SESSION['clientID'];


  $sql = "insert into Messages (senderID, receiverID, artName, buyerUname, artID) 
          values ('$buyerID', '$sellerID', '$artName', '$userName', '$artID')";
  $result = $conn->query($sql);
  if (!$result) {
    echo "Error: " . $sql . "<br>" . $conn->error;
    return false;
  }
  echo "Request sent";

}
mysqli_close($conn);

?>






<html>
<head>
  <title>Art Broker</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
  <script>
    function calculateHash() {
      const imageFile = document.getElementById('imageFile').files[0];
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
          hashInput.name = 'hashHex';
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

<h3>Connecting to ArtBroker database using MySQL/PHP</h3>
<hr>

<p>
website that uses differen't encryption techniques to buy and sell art
<p>


<!-- Menu for adding clients-->

<h1> Create Account</h1>
<form action="" method="POST" id="myForm" enctype="multipart/form-data" onsubmit="return calculateHash()">
  <label for="fname">First Name:</label>
  <input type="text" name="item1[fname]" id="fname"><br>
  <label for="lname">Last Name:</label>
  <input type="text" name="item1[lname]" id="lname"><br>
  <label for="uname">User Name:</label>
  <input type="text" name="item1[uname]" id="uname"><br>

  <label for="pass">Password:</label>
  <input type="text" name="item1[pass]" id="pass"><br>


  <label for="imageFile" class="form-label">jpeg: ID Image:</label>
  <input type="file" name="imageFile" id="imageFile" accept="image/jpeg" class="form-input">

  <label for="pemFile" class="form-label">pem: Public Key</label>
  <input type="file" name="pemFile" id="pemFile" accept=".pem" class="form-input">

  <input type="submit" value="Submit">
  <input type="reset" value="Reset">
</form>




<a href="signIn.php" >Need to sign in? click here</a>

</body>
</html>



<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

function extractPublicKeyFromPEM($pemContents) {
  preg_match('/-----BEGIN PUBLIC KEY-----\r?\n(.*?)\r?\n-----END PUBLIC KEY-----/s', $pemContents, $matches);
  if (isset($matches[1])) {
    return trim($matches[1]);
  } else {
    return null;
  }
}

include('connectionData.txt');

$conn = mysqli_connect($server, $user, $pass, $dbname, $port)
or die('Error connecting to MySQL server.');

if (isset($_POST['item1'])) {
  // For adding clients 
  if (     isset($_POST['item1']['fname']) 
      &&   isset($_POST['item1']['lname']) 
      &&   isset($_POST['item1']['uname']) 
      &&   isset($_POST['item1']['pass']) 
     &&   isset($_FILES["pemFile"])
      &&   $_POST['item1']['fname'] !== '' 
      &&   $_POST['item1']['lname'] !== '' 
      &&   $_POST['item1']['uname'] !== '' 
      &&   $_POST['item1']['pass'] !== ''
     &&   $_FILES["pemFile"] !== ''
    )
  {
    $fname = $_POST['item1']['fname'];
    $lname = $_POST['item1']['lname'];
    $uname = $_POST['item1']['uname'];
    $pass = $_POST['item1']['pass'];
    $idHash = $_POST['hashHex'];

    $file = $_FILES['pemFile'];
    $filePath = $file["tmp_name"];
    $pemContents = file_get_contents($filePath);
    $pkey = extractPublicKeyFromPEM($pemContents);
  } else {
    trigger_error('All entries must be filled', E_USER_ERROR);
  }
  $sql = "select username from Clients where username = '" . $uname . "'";
  $result = $conn->query($sql);

  if ($result->num_rows !== 0) {
    echo "Username already exists";
    return;
  }
  $result->close();
  $sql = "SELECT COALESCE(MIN(clientID) + 1, 1) AS new_key FROM (SELECT 0 AS clientID UNION ALL SELECT clientID FROM Clients) t1 WHERE NOT EXISTS (SELECT 1 FROM Clients t2 WHERE t2.clientID = t1.clientID + 1)";
  $result = $conn->query($sql);

  if (!$result) {
    echo "Error: " . $sql . "<br>" . $conn->error;
  } else {
    $row = $result->fetch_assoc();
    $new_key = $row['new_key'];
  }

  $sql = "SELECT EXISTS (SELECT * FROM Clients WHERE idHash = '$idHash') AS RecordExists";
  $result = $conn->query($sql);
  $row = $result->fetch_assoc();
  $recordExists = $row['RecordExists'];
  
  if ($recordExists == 1) {
      echo "image taken, please use a differen't image";
      return;
  }
  

  $sql = "INSERT INTO Clients (clientID, fname, lname, userName, pass, publickey, idHash) VALUES ('$new_key', '$fname', '$lname', '$uname', '$pass', '$pkey', '$idHash')";
  $result = $conn->query($sql);

  if (!$result) {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }



  session_start();

  $_SESSION['loggedin'] = true;
  $_SESSION['clientID'] = $new_key;
  $_SESSION['userName'] = $uname;


  header('Location: profile.php');
  
  
}

mysqli_close($conn);
?>

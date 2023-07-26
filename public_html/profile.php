<?php
// Start the session
session_start();

// Check if user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  // Redirect to the login page
  header('Location: signIn.php');
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
  <title>Profile Page</title>
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

  <p>List of your art</p>
  <!-- php script that prints art owned by client -->
  <?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    include('connectionData.txt');

    $conn = mysqli_connect($server, $user, $pass, $dbname, $port) or die('Error connecting to MySQL server.');

    $clientID = $_SESSION['clientID'];
    $sql = "SELECT artName, timeAdded FROM Art WHERE ownerID = '$clientID'";
    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    if (!$result) {
      trigger_error('Could not access art', E_USER_ERROR);
    }

    print "<pre>";
    print "Art Name \t \t Time Added\n"; // Column names
    while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
      if (strlen($row['artName']) > 7){
        $artName = substr($row['artName'], 0, 7) . "...";
      }
      else {
        $artName = $row['artName'] . "\t";
      }
      print "$artName \t \t $row[timeAdded]\n"; // Entries
    }
    print "</pre>";

  ?>


  <form action="" method="POST" id="myForm" onsubmit="return handleFileSelection()">

    <label for="imageFile1" class="form-label">Original Image:</label>
    <input type="file" name="item1[imageFile1]" id="imageFile1" accept="image/jpeg" class="form-input">



    <label for="imageFile2" class="form-label">WM Image:</label>
    <input type="file" name="item1[imageFile2]" id="imageFile2" accept="image/jpeg" class="form-input">

    <label for="artName" class="form-label">Name of Art:</label>
    <input type="text" name="item1[artName]" id="artName" class="form-input">

    <input type="submit" value="Submit To Database" class="form-submit">
  </form>

  <div>
    <a href="messages.php" class="return-link">Click to view requests</a>
  </div>

  <div>
    <a href="buyRequest.php" class="return-link">Click here request to buy</a>
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
  if (    isset($_POST['hashHex1']) 
      &&  isset($_POST['hashHex2'])
      &&  isset($_POST['item1']['artName']) 
      &&  $_POST['hashHex1'] !== "" 
      &&  $_POST['hashHex2'] !== "" 
      &&  $_POST['item1']['artName'] !== "") 
  {
    $originalHash = $_POST['hashHex1'];
    $waterMarkHash = $_POST['hashHex2'];
    if ($originalHash == $waterMarkHash){
      printf("Photo are the same, please use the client to prepare photos before submission to database");
      return;
    }
    $artName = $_POST['item1']['artName'];
    $clientID = $_SESSION['clientID'];

  } else {
    printf("\n All above fields must be filled \n");
    return;
  }


  //checks to see if art Exists within Database
  $sql = "SELECT hashArtID FROM Art WHERE hashArtID = '" . $originalHash . "'";
  $result = $conn->query($sql);
  if ($result) {
    if ($result->num_rows > 0) {
      printf("\nArt already exists in the database.\n");
      return;
    }
  }

  $sql = "INSERT INTO Art (ownerID, artName, hashArtID, uploadedBy) 
          VALUES ('$clientID', '$artName', '$originalHash', '$clientID')";
  $result = $conn->query($sql);
  if (!$result) {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $artID = $conn->insert_id;

  $sql = "INSERT INTO OwnershipHx (ArtID, clientID, wmHash) 
          VALUES ('$artID', '$clientID', '$waterMarkHash')";
  $result = $conn->query($sql);
  if (!$result) {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
  
}
mysqli_close($conn);

?>
<!-- ^ code to sumbit art to database -->






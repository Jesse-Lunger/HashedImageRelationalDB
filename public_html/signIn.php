<html>
<head>
       <title>Art Broker</title>
</head>
<body bgcolor="white">
<style>
  form {
    display: flex;
    flex-direction: column;
    max-width: 300px; 
    margin-right: auto;
  }

  label {
    width: 120px; 
    margin-right: 10px; 
  }

  input[type="text"] {
    flex: 1;
  }

  input[type="submit"],
  input[type="reset"] {
    margin-top: 5px; 
  }

</style>

<h3>Connecting to ArtBroker database using MySQL/PHP</h3>
<hr>

<p>
website that uses different encryption techniques to buy and sell art
<p>

<!-- Menu for adding clients-->

<h1> Sign In</h1>
<form action="" method="POST">
  <label for="uname">User Name:</label>
  <input type="text" name="item1[uname]" id="uname"><br>
  <label for="pass">Password:</label>
  <input type="text" name="item1[pass]" id="pass"><br>
  <input type="submit" value="Submit">
  <input type="reset" value="Reset">
</form>

<a href="accountCreation.php" >Need to create an account? click here</a>


</body>
</html>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('connectionData.txt');

    $conn = mysqli_connect($server, $user, $pass, $dbname, $port)
        or die('Error connecting to MySQL server.');

    if (isset($_POST['item1']['uname']) && isset($_POST['item1']['pass']) && $_POST['item1']['uname'] !== '' && $_POST['item1']['pass'] !== '') {
        $uname = $_POST['item1']['uname'];
        $pass = $_POST['item1']['pass'];
    } else {
        trigger_error('All entries must be filled', E_USER_ERROR);
    }

    $sql = "SELECT clientID, username, pass FROM Clients WHERE username = '" . $uname . "' AND pass = '" . $pass . "'";
    $result = $conn->query($sql);

    if ($result->num_rows === 0) {
        echo "Username or password do not match";
        return;
    } else {
      $row = $result->fetch_assoc();
      $new_key = $row['clientID'];
    }

    mysqli_close($conn);
    session_start();

      // Set session variables
    $_SESSION['loggedin'] = true;
    $_SESSION['clientID'] = $new_key;
    $_SESSION['userName'] = $uname;

    // Redirect to the protected page
    header('Location: profile.php');
  
  
}
?>

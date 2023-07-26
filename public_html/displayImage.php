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

<!DOCTYPE html>
<html>
<head>
    <title>Upload Image</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="file"] {
            margin-bottom: 10px;
        }

        input[type="submit"] {
            background-color: black;
            color: white;
            border: none;
            padding: 10px 20px;
            text-decoration: none;
            cursor: pointer;
        }

        .image-container {
            margin-top: 20px;
        }

        .image-container img {
            max-width: 400px;
            max-height: 400px;
        }

        .return-link {
            display: inline-block;
            margin-top: 20px;
            font-size: 14px;
        }

        .sign-out-button {
        position: absolute;
        top: 10px;
        right: 10px;
        display: inline-block;
        font-size: 14px;
        }



    </style>
</head>
<body>
    <h1>Upload Image</h1>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
        <label for="image">Select an image to upload:</label>
        <input type="file" name="image" id="image">
        <br>
        <input type="submit" value="Upload">
    </form>



<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"])) {
        // Get the contents of the uploaded image
        $imageData = file_get_contents($_FILES["image"]["tmp_name"]);

        // Get the image MIME type
        $imageType = $_FILES["image"]["type"];

        // Display the uploaded image
        echo '<div class="image-container">';
        echo '<img src="data:' . $imageType . ';base64,' . base64_encode($imageData) . '" alt="Uploaded Image">';
        echo '</div>';
    }
    ?>

    <div class="return-link">
        <a href="profile.php">Click here to return to profile</a>
    </div>

    <p>

    </p>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="sign-out-button">
        <input type="submit" name="signOut" value="Sign Out">
    </form>
</body>
</html>

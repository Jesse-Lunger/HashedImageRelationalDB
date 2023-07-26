<?php
// Connect to the database
include('connectionData.txt');
$conn = mysqli_connect($server, $user, $pass, $dbname, $port) or die('Error connecting to MySQL server.');

// Query to retrieve client data
$sql = "SELECT clientID, fname, lname FROM Clients";
$result = mysqli_query($conn, $sql);

// Fetch the data into an array
$clients = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Close the database connection
mysqli_close($conn);

// Send the response as JSON
header("Content-type: application/json");
echo json_encode($clients);
?>

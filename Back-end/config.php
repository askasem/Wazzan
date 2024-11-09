<?php

// Database server name
$servername = "db_host";

// Database username
$username = "db_username";

// Database password
$password = "db_password";

// Database name
$dbname = "db_name";

// Enable reporting of MySQLi errors
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
  // If the connection failed, output an error message and terminate the script
  die("Connection failed: " . $conn->connect_error);
}

?>
<?php

// Include the database configuration file
include 'config.php';

// SQL query to calculate the average of the 'stars' column from the 'evaluation' table
$sql = 'select avg(stars) from evaluation as av';

// Execute the query
$res = $conn->query($sql);

// Initialize the average variable
$av = 0;

// If the query was successful, fetch the average value
if ($res) {
    $av = $res->fetch_row()[0];
}

// Close the database connection
$conn->close();

// Print the average value
print($av);

?>
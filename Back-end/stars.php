<?php

// Try block to handle exceptions
try {
    // Include the database configuration file
    include 'config.php';
    
    // Get the raw POST data
    $json = file_get_contents('php://input');
    
    // Decode the JSON data into a PHP object
    $data = json_decode($json);
    
    // Check if the 'stars' property is present and not empty
    if (!empty($data->stars)) {
        // SQL query to insert the stars value into the evaluation table
        $insert_sql = 'insert into evaluation(stars) values (?)';
        
        // Prepare the SQL statement
        $stmt = $conn->prepare($insert_sql);
        
        // Bind the stars value to the SQL statement as an integer
        $stmt->bind_param("i", $data->stars);
        
        // Execute the SQL statement
        $stmt->execute();
        
        // Close the database connection
        $conn->close();
        
        // Return a JSON response indicating success
        echo json_encode('Ok');
    } else {
        // Return a JSON response indicating a wrong parameter
        echo json_encode('wrong param');
    }
}
// Catch block to handle any exceptions that occur
catch (Exception $e) {
    // Return a JSON response indicating an error
    echo json_encode("Error in recording evaluation.");
    
    // Log the exception message
    error_log("Stars exception caught: $e");
}
?>
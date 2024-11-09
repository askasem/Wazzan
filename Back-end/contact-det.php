<?php

// Start of the try block to catch any exceptions
try {
    // Get the raw POST data
    $json = file_get_contents('php://input');
    // Decode the JSON data into a PHP object
    $data = json_decode($json);

    // Check if the required fields are not empty
    if (!empty($data->name) && !empty($data->content) && !empty($data->subject)) {
        // Include the database configuration file
        include 'config.php';

        // SQL query to insert data into the contact table
        $insert_sql = 'insert into contact(name,email,phone,Subject,Message) values (?,?,?,?,?)';
        // Prepare the SQL statement
        $stmt = $conn->prepare($insert_sql);
        // Bind the parameters to the SQL query
        $stmt->bind_param("sssss", $data->name, $data->email, $data->phone, $data->subject, $data->content);
        // Execute the SQL statement
        $stmt->execute();
        // Close the database connection
        $conn->close();

        // Return a success response
        echo json_encode('Ok');
    } else {
        // Return an error response if required fields are missing
        echo json_encode('wrong params');
    }
}
// Catch any exceptions that occur
catch (Exception $e) {
    // Return a generic error response
    echo json_encode("Error in recording contact details.");
    // Log the exception message
    error_log("Contact exception caught: $e");
}
?>
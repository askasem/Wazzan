<?php


// If this is a preflight (OPTIONS) request, respond with 200 OK and exit
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// error_log("parse.php: Received request\n");

$data = file_get_contents('php://input'); //Ammar says this line was necessary in getting the POST parameter, due to some problem in getting JSON from POST
$data = substr($data, 0, 150); //currently, any text longer than this would be trimmed. Consider ignoring it all together.

// error_log("parse.php: Raw input data: " . $data);

$data = json_decode($data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("parse.php: JSON decode error: " . json_last_error_msg());
    $error = ['error' => true, 'message' => 'Invalid JSON input'];
    echo json_encode($error);
    exit;
}

// error_log("parse.php: Decoded input data: " . print_r($data, true));

include('IbmAi.php');

$ibmAi = IbmAi::getInstance(); //new IbmAi();

try {
    if (isset($data['content'])) {
        // error_log("parse.php: Content key exists in input data");
        $response = $ibmAi->shakkel($data);
        // error_log("parse.php: Response from shakkel: " . print_r($response, true));
        echo json_encode($response);
    } else {
        throw new Exception('Invalid input data');
    }
} catch (Exception $e) {
    error_log("parse.php: Exception caught: " . $e->getMessage());
    $error = ['error' => true, 'message' => 'Error has occurred in parse: ' . $e->getMessage()];
    echo json_encode($error);
}
?>
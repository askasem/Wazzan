<?php


// If this is a preflight (OPTIONS) request, respond with 200 OK and exit
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// error_log("Received request: " . $_SERVER['REQUEST_METHOD']);

$data = file_get_contents('php://input'); //Ammar says this line was necessary in getting the POST parameter, due to some problem in getting JSON from POST
// error_log("Raw POST data: " . $data);

$data = substr($data, 0, 1000); //currently, any text longer than this would be trimmed. Consider ignoring it all together.
// error_log("Trimmed data: " . $data);

$data = json_decode($data, true);
// error_log("Decoded JSON data: " . print_r($data, true));

include('IbmAi.php');
//echo $data;
$ibmAi = IbmAi::getInstance(); //new IbmAi();	
//$ibmAi->question = $data;
//echo $data;

try {
    $response = $ibmAi->game_reply($data);
    // error_log("AI response: " . print_r($response, true));
    echo json_encode($response); 
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    $error = ['error' => true, 'message' => 'Error has occurred in parse'];
    echo json_encode($error);
}
?>
<?php
// Accessing to the authentication and responses classes
require_once "classes/NewUser.class.php";
require_once "classes/Responses.class.php";
// Class instantiation
$NewUser = new NewUser;
$Responses = new Responses;
// Validating post method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CORS
    header("Access-Control-Allow-Origin: *");
    // Storing the received post data
    $post_body = file_get_contents("php://input");
    //  Sending the stored data to the signUp method
    $arr_data = $NewUser->signUp($post_body);
    // Returning the response
    header("Content-type: application/json");
    if (isset($arr_data["result"]["error_id"])) {
        $response_code = $arr_data["result"]["error_id"];
        http_response_code($response_code);
    } else {
        http_response_code(200);
    }
    echo json_encode($arr_data);
} else {
    header("Content-type: application/json");
    $arr_data = $Responses->error_405();
    echo json_encode($arr_data);
}
<?php
// Accesing users and responses classes
require_once "classes/Responses.class.php";
require_once "classes/Users.class.php";
// Initializing classes
$Responses = new Responses;
$Users = new Users;
// Headers
// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS, PATCH, DELETE");
// API RESTful
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["token"]) && isset($_GET["page"])) {
        $token = $_GET["token"];
        $page = $_GET["page"];
        $users = $Users->usersList($token, $page);
        header("Content-Type: application/json");
        echo json_encode($users);
        http_response_code(200);
    } else if (isset($_GET["token"]) && isset($_GET["uname"])) {
        $token = $_GET["token"];
        $uname = $_GET["uname"];
        $user = $Users->getUser($token, $uname);
        header("Content-Type: application/json");
        echo json_encode($user);
        http_response_code(200);
    } else {
        header("Content-Type: application/json");
        $arr_data = $Responses->error_401();
        echo json_encode($arr_data);
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Getting post sent data and sending it to the post handler
    $post_body = file_get_contents("php://input");
    $arr_data = $Users->post($post_body);
    // Response
    header("Content-type: application/json");
    if (!isset($arr_data["result"]["error_id"])) http_response_code(200);
    $response_code = $arr_data["result"]["error_id"];
    http_response_code($response_code);
    echo json_encode($arr_data);
} else if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // Getting put sent data and sending it to the put handler
    $put_body = file_get_contents("php://input");
    $arr_data = $Users->put($put_body);
    // Response
    header("Content-type: application/json");
    if (!isset($arr_data["result"]["error_id"])) http_response_code(200);
    $response_code = $arr_data["result"]["error_id"];
    http_response_code($response_code);
    echo json_encode($arr_data);
} else if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $headers = getallheaders();
    // print_r($headers);
    if (isset($headers["token"]) && isset($headers["id-users"])) {
        // Getting data by the headers
        $send = [
            "token" => $headers["token"],
            "id-users" => $headers["id-users"]
        ];
        $delete_body = json_encode($send);
    } else {
        // Getting data sent by post
        $delete_body = file_get_contents("php://input");
    }
    // Delete handler
    $arr_data = $Users->delete($delete_body);
    // Response
    header("Content-type: application/json");
    if (!isset($arr_data["result"]["error_id"])) http_response_code(200);
    $response_code = $arr_data["result"]["error_id"];
    http_response_code($response_code);
    echo json_encode($arr_data);
} else {
    header("Content-type: application/json");
    $arr_data = $Responses->error_405();
    echo json_encode($arr_data);
}
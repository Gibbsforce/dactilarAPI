<?php
// Accessing to the cart and responses classes
require_once "classes/Cart.class.php";
require_once "classes/Responses.class.php";
// Instantiating classes
$Cart = new Cart();
$Responses = new Responses();
// Headers
// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS, PATCH, DELETE");
// GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["token"]) && isset($_GET["uname"])) {
        $token = $_GET["token"];
        $uname = $_GET["uname"];
        $cart = $Cart->getCartFromUser($token, $uname);
        header("Content-Type: application/json");
        echo json_encode($cart);
        http_response_code(200);
    } else {
        header("Content-Type: application/json");
        $arr_data = $Responses->error_401();
        echo json_encode($arr_data);
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_body = file_get_contents("php://input");
    $arr_data = $Cart->addToCart($post_body);
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
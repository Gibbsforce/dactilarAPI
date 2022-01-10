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
    }
} else {
    header("Content-type: application/json");
    $arr_data = $Responses->error_405();
    echo json_encode($arr_data);
}
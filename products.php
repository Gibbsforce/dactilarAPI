<?php
// Accessing to the products and responses classes
require_once "classes/Products.class.php";
require_once "classes/CorsAccessControl.class.php";
require_once "classes/Responses.class.php";
// Instantiating classes
$Products = new Products();

$Cors = new CorsAccessControl();
$domain = "https://dactilar.com.pe";
$Cors->add($domain);
$Cors->send();
print_r($Cors);

// header('Access-Control-Allow-Origin: *');
// $Responses = new Responses();
// GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (!isset($_GET["page"]) && !isset($_GET["id"])) {
        $products = $Products->getProducts(1);
        // header('Access-Control-Allow-Origin: *');
        // header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS, PATCH, DELETE');
        // header('Access-Control-Allow-Credentials: true');
        // header('Access-Control-Allow-Headers: Authorization, Content-Type, x-xsrf-token, x_csrftoken, Cache-Control, X-Requested-With');
        header("X-Content-Type-Options: nosniff");
        header("Content-Type: application/json");
        echo json_encode($products);
        http_response_code(200);
    } else if (isset($_GET["page"])) {
        $page = $_GET["page"];
        $products = $Products->getProducts($page);
        // header('Access-Control-Allow-Origin: *');
        // header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS, PATCH, DELETE');
        // header('Access-Control-Allow-Credentials: true');
        // header('Access-Control-Allow-Headers: Authorization, Content-Type, x-xsrf-token, x_csrftoken, Cache-Control, X-Requested-With');
        header("X-Content-Type-Options: nosniff");
        header("Content-Type: application/json");
        echo json_encode($products);
        http_response_code(200);
    } else if (isset($_GET["id"])) {
        $id = $_GET["id"];
        // header('Access-Control-Allow-Origin: *');
        // header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS, PATCH, DELETE');
        // header('Access-Control-Allow-Credentials: true');
        // header('Access-Control-Allow-Headers: Authorization, Content-Type, x-xsrf-token, x_csrftoken, Cache-Control, X-Requested-With');
        header("X-Content-Type-Options: nosniff");
        header("Content-Type: application/json");
        $product = $Products->getProduct($id);
        echo json_encode($product);
        http_response_code(200);
    }
} else {
    header("X-Content-Type-Options: nosniff");
    header("Content-type: application/json");
    $arr_data = $Responses->error_405();
    echo json_encode($arr_data);
}
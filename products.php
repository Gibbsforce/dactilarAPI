<?php
// Accessing to the products and responses classes
require_once "classes/Responses.class.php";
require_once "classes/Products.class.php";
// Instantiating classes
$Products = new Products();
// $Responses = new Responses();
// GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (!isset($_GET["page"]) && !isset($_GET["id"])) {
        $products = $Products->getProducts(1);
        $header = "Content-Type: application/json";
        echo json_encode($products);
        http_response_code(200);
    }
    else if (isset($_GET["page"])) {
        $page = $_GET["page"];
        $products = $Products->getProducts($page);
        $header = "Content-Type: application/json";
        echo json_encode($products);
        http_response_code(200);
    }
    else if (isset($_GET["id"])) {
        $id = $_GET["id"];
        $header = "Content-Type: application/json";
        $product = $Products->getProduct($id);
        echo json_encode($product);
        http_response_code(200);
    }
} else {
    header("Content-type: application/json");
    $arr_data = $Responses->error_405();
    echo json_encode($arr_data);
}
<?php
// Accessing to the products and responses classes
require_once "classes/Products.class.php";
require_once "classes/Responses.class.php";
// require_once "classes/CorsAccessControl.class.php";

// Instantiating classes
$Products = new Products();
$Responses = new Responses();
// $CorsAccessControl = new CorsAccessControl();

// Headers
// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
// header("Access-Control-Allow-Origin: https://dactilar.com.pe");
// header("Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS, PATCH, DELETE");
// header("Access-Control-Allow-Credentials: true");
// header("Access-Control-Allow-Headers: Authorization, Content-Type, x-xsrf-token, x_csrftoken, Cache-Control, X-Requested-With");

// To the htaccess

// Header always set Access-Control-Allow-Methods "POST, GET, PUT, OPTIONS, PATCH,DELETE"
// Header always set Access-Control-Allow-Origin "*"
// Header always set Access-Control-Allow-Credentials "true"
// Header always set Access-Control-Allow-Headers "content-type,Authorization,Cache-Control,X-Requested-With, X-XSRF-TOKEN"

// GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["all"])) {
        $all_products = $Products->getAllProducts(1);
        header("Content-Type: application/json");
        echo json_encode($all_products);
        http_response_code(200);
    } else if (isset($_GET["pages"])) {
        $pages = $_GET["pages"];
        $all_products = $Products->getAllProducts($pages);
        header("Content-Type: application/json");
        echo json_encode($all_products);
        http_response_code(200);
    } else if (isset($_GET["uid"])) {
        $uid = $_GET["uid"];
        $all_product = $Products->getAllProduct($uid);
        header("Content-Type: application/json");
        echo json_encode($all_product);
        http_response_code(200);
    } else if (!isset($_GET["page"]) && !isset($_GET["id"])) {
        $products = $Products->getProducts(1);
        header("X-Content-Type-Options: nosniff");
        header("Content-Type: application/json");
        echo json_encode($products);
        http_response_code(200);
    } else if (isset($_GET["page"])) {
        $page = $_GET["page"];
        $products = $Products->getProducts($page);
        header("X-Content-Type-Options: nosniff");
        header("Content-Type: application/json");
        echo json_encode($products);
        http_response_code(200);
    } else if (isset($_GET["id"])) {
        $id = $_GET["id"];
        header("X-Content-Type-Options: nosniff");
        header("Content-Type: application/json");
        $product = $Products->getProduct($id);
        echo json_encode($product);
        http_response_code(200);
    } else {
        header("X-Content-Type-Options: nosniff");
        header("Content-type: application/json");
        $arr_data = $Responses->error_405();
        echo json_encode($arr_data);
    }
// POST
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Receiving post sent data as a JSON string
    $post_body = file_get_contents("php://input");
    // Sending the data to post handler
    $arr_data = $Products->post($post_body);
    // Returning the response
    header("Content-type: application/json");

    // $response_code = $arr_data["result"]["error_id"];
    // http_response_code($response_code);
    // if (!isset($arr_data["result"]["error_id"])) http_response_code(200);
    // echo json_encode($arr_data);

    // if (isset($arr_data["result"]["error_id"])) {
    //     $response_code = $arr_data["result"]["error_id"];
    //     http_response_code($response_code);
    // } else {
    //     http_response_code(200);
    // }
    echo json_encode($arr_data);
// PUT
} else if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // Receiving put sent data as a JSON string
    $put_body = file_get_contents("php://input");
    // Sending the data to put handler
    $arr_data = $Products->put($put_body);
    // Returning the response
    header("Content-type: application/json");
    if (!isset($arr_data["result"]["error_id"])) http_response_code(200);
    $response_code = $arr_data["result"]["error_id"];
    http_response_code($response_code);
    echo json_encode($arr_data);
// DELETE
} else if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $headers = getallheaders();
    if (isset($headers["token"]) && isset($headers["product_id"])) {
        // Receiving data from headers
        $send = [
            "token" => $headers["token"],
            "product_id" => $headers["product_id"]
        ];
        $delete_body = json_encode($send);
    } else {
        // Receiving post sent data as a JSON string
        $delete_body = file_get_contents("php://input");
    }
    // Sending the data to delete handler
    $arr_data = $Products->delete($delete_body);
    // Returning the response
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
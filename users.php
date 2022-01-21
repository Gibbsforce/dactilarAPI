<?php
// Accediendo a las clases de usuarios y respuestas
require_once "classes/Responses.class.php";
require_once "classes/Users.class.php";
// Inicializando las clases
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
    } else if (isset($_GET["token"] && isset($_GET["uname"]))) {
        $token = $_GET["token"];
        $uname = $_GET["uname"];
        $user = $Users->getUser($token, $uname);
        header("Content-Type: application/json");
        echo json_encode($users);
        http_response_code(200);
    } else {
        header("Content-Type: application/json");
        $arr_data = $Responses->error_401();
        echo json_encode($arr_data);
    }
    // if (isset($_GET["page"])) {
    //     $page = $_GET["page"];
    //     $users_list = $Users->usersList($page);
    //     header("Content-type: application/json");
    //     echo json_encode($users_list);
    //     http_response_code(200);
    // } else if (isset($_GET["id"])) {
    //     $id_users = $_GET["id"];
    //     header("Content-type: application/json");
    //     $user = $Users->getUser($id_users);
    //     echo json_encode($user);
    //     http_response_code(200);
    // }
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibiendo datos enviados de post
    $post_body = file_get_contents("php://input");
    // Enviando datos al manejador metodo post
    $arr_data = $Users->post($post_body);
    // Devolviendo la respuesta
    header("Content-type: application/json");
    if (!isset($arr_data["result"]["error_id"])) http_response_code(200);
    $response_code = $arr_data["result"]["error_id"];
    http_response_code($response_code);
    echo json_encode($arr_data);
} else if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // Recibiendo datos enviados de post
    $put_body = file_get_contents("php://input");
    // Enviando datos al manejador metodo put
    $arr_data = $Users->put($put_body);
    // Devolviendo la respuesta
    header("Content-type: application/json");
    if (!isset($arr_data["result"]["error_id"])) http_response_code(200);
    $response_code = $arr_data["result"]["error_id"];
    http_response_code($response_code);
    echo json_encode($arr_data);
} else if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $headers = getallheaders();
    print_r($headers);
    if (isset($headers["token"]) && isset($headers["id-users"])) {
        // Recibiendo datos por el header
        $send = [
            "token" => $headers["token"],
            "id-users" => $headers["id-users"]
        ];
        $delete_body = json_encode($send);
    } else {
        // Recibiendo datos enviados de post
        $delete_body = file_get_contents("php://input");
    }
    // Enviando datos al manejador metodo delete
    $arr_data = $Users->delete($delete_body);
    // Devolviendo la respuesta
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
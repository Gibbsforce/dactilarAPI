<?php
// Accediendo a las clases de autenticacion y respuestas
require_once "classes/Auth.class.php";
require_once "classes/Responses.class.php";
// Instanciando las clases
$Auth = new Auth;
$Responses = new Responses;
// Validando metodo post
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["uid"]) && isset($_GET["token"])) {
        $uid = $_GET["uid"];
        $token = $_GET["token"];
        $arr_data = $Auth->validate($uid, $token);
        header("Content-type: application/json");
        if (isset($arr_data["result"]["error_id"])) {
            $response_code = $arr_data["result"]["error_id"];
            http_response_code($response_code);
        } else {
            http_response_code(200);
        }
        header("Location: localhot:3000/login");
        echo json_encode($arr_data);
    } else {
        header("Content-type: application/json");
        $arr_data = $Responses->error_405();
        echo json_encode($arr_data);
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Almacenando los datos recibidos mediante post
    $post_body = file_get_contents("php://input");
    // Enviando los datos almacenados al metodo login
    $arr_data = $Auth->login($post_body);
    // Devolviendo la respuesta
    header("Content-type: application/json");
    if (isset($arr_data["result"]["error_id"])) {
        $response_code = $arr_data["result"]["error_id"];
        http_response_code($response_code);
    } else {
        http_response_code(200);
    }
    echo json_encode($arr_data);
    //print_r(json_encode($arr_data));
} else {
    header("Content-type: application/json");
    $arr_data = $Responses->error_405();
    echo json_encode($arr_data);
}
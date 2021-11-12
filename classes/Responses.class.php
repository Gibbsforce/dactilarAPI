<?php
// Creando la clase para los errores de respuesta
class Responses {
    // Estado y resultados de la respuesta definidos
    public $response = [
        "status" => "OK",
        "result" => array()
    ];
    // Error 405
    public function error_405() {
        $this->response["status"] = "error";
        $this->response["result"] = array(
            "error_id" => "405",
            "error_message" => "No permitido"
        );
        return $this->response;
    }
    // Error para datos incorrectos (200)
    public function error_200($str = "Datos incorrectos") {
        $this->response["status"] = "error";
        $this->response["result"] = array(
            "error_id" => "200",
            "error_message" => $str
        );
        return $this->response;
    }
    // Error 400
    public function error_400() {
        $this->response["status"] = "error";
        $this->response["result"] = array(
            "error_id" => "400",
            "error_message" => "Datos incompletos o formato incorrecto"
        );
        return $this->response;
    }
    // Error 500
    public function error_500($str = "Error de servidor") {
        $this->response["status"] = "error";
        $this->response["result"] = array(
            "error_id" => "500",
            "error_message" => $str
        );
        return $this->response;
    }
    // Error 404
    public function error_404($str = "Error, recurso no encontrado") {
        $this->response["status"] = "error";
        $this->response["result"] = array(
            "error_id" => "404",
            "error_message" => $str
        );
        return $this->response;
    }
    // Error 401
    public function error_401($str = "No autorizado") {
        $this->response["status"] = "error";
        $this->response["result"] = array(
            "error_id" => "401",
            "error_message" => $str
        );
        return $this->response;
    }
    // Error 201
    public function error_201($str = "Created") {
        $this->response["status"] = "error";
        $this->response["result"] = array(
            "error_id" => "201",
            "error_message" => $str
        );
        return $this->response;
    }
}
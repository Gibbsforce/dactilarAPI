<?php
// Building class responses errors
class Responses {
    // Status and result for defined responses
    public $response = [
        "status" => "OK",
        "result" => array()
    ];
    // Error 405
    public function error_405() {
        $this->response["status"] = "error";
        $this->response["result"] = array(
            "error_id" => "405",
            "error_message" => "Not Allowed"
        );
        return $this->response;
    }
    // Error para datos incorrectos (200)
    public function error_200($str = "Incorrect data") {
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
            "error_message" => "Incomplete data or incorrect format"
        );
        return $this->response;
    }
    // Error 500
    public function error_500($str = "Server Error") {
        $this->response["status"] = "error";
        $this->response["result"] = array(
            "error_id" => "500",
            "error_message" => $str
        );
        return $this->response;
    }
    // Error 404
    public function error_404($str = "Not Found") {
        $this->response["status"] = "error";
        $this->response["result"] = array(
            "error_id" => "404",
            "error_message" => $str
        );
        return $this->response;
    }
    // Error 401
    public function error_401($str = "Unauthorized") {
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
    // Error 202
    public function error_202($str = "Accepted") {
        $this->response["status"] = "error";
        $this->response["result"] = array(
            "error_id" => "202",
            "error_message" => $str
        );
        return $this->response;
    }
}
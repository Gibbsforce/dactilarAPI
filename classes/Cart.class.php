<?php
// Accessing the Connection and Responses classes
require_once "Connection/Connection.php";
require_once "Responses.class.php";
// Cart inherits from Connection
class Cart extends Connection {
    private $token = "";
    // Getting the cart from user
    public function getCartFromUser($token, $uname) {
        $Responses = new Responses();
        $this->token = $token;
        $arr_token = $this->searchToken();
        if (!$arr_token) return $Responses->error_401("Unauthorized or your token has been deprecated");
        $username = $arr_token[0]["username"];
        if ($username !== $uname) return $Responses->error_401();
        $query = "SELECT `cart` FROM `users` WHERE username = '$username'";
        try {
            $data = parent::getData($query);
            if (!isset($data)) return $this->Responses->error_500();
            $result = array(
                "message" => "OK",
                "cart_result" => $data
            );
            return $result;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
    // Looking for the token method
    private function searchToken() {
        $query = "SELECT `id-token`, `username`, `state`, `status` FROM `users-token` WHERE `token` = '".$this->token."' AND `state` = 1 AND (`status` = 'user' OR `status` = 'admin')";
        try {
            $result = parent::getData($query);
            if ($result) return $result;
            return false;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
}
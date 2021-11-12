<?php
// Accediendo a las clases Connection y Responses
require_once "Connection/Connection.php";
require_once "Responses.class.php";
// Creando la clase Auth y heredando de Connection
class Auth extends Connection {
    // Creando el metodo login
    public function login($json) {
        $Responses = new Responses;
        $data = json_decode($json, true);
        // Validando si existen el usuario y la contrasena //////EL USUARIO MIENTRAS TANTO ES EL UNIQUE ID//////
        if (!isset($data["unique-id"]) || !isset($data["password"])) return $Responses->error_400();
        // Almacenando datos del usuario en variables y obteniendo sus datos
        $unique_id = $data["unique-id"];
        $password = $data["password"];
        $password = parent::encrypt($password);
        $data = $this->getUserData($unique_id);
        // Validando si datos del usuario existe
        if (!$data) return $Responses->error_200("El usuario $unique_id no existe");
        // Validando si la contrasena es correcta
        if ($password !== $data[0]["password"]) return $Responses->error_200("La contrasena es invalida");
        // Validando el estado del usuario
        if ($data[0]["state"] == false) return $Responses->error_200("Usuario inactivo");
        // Validando si se pudo agregar el token
        $verify = $this->addToken($data[0]["unique-id"]);
        if (!$verify) return $Responses->error_200("Error interno, no se ha podido guardar");
        // debug
        // $debug = print_r($data[0]);
        // Obteniendo el resultado y token del usuario
        $result = $Responses->response;
        $result["result"] = array(
            "token" => $verify
        );
        return $result;
    }
    // Metodo que obtiene los datos del usuario de la base de datos
    private function getUserData($unique_id) {
        // Obteniendo campos de la tabla users-auth
        $query = "SELECT `id-auth`, username, password, dni, `unique-id`, state, email FROM `users-auth` WHERE `unique-id` = '$unique_id'";
        $data = parent::getData($query);
        if (isset($data[0]["id-auth"])) return $data;
        return 0;
    }
    // Insertando y creando token a la tabla de la base de datos de users-token
    private function addToken($unique_id) {
        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(16, $val));
        $date = date("Y-m-d H:i");
        $state = true;
        $query = "INSERT INTO `users-token` (`unique-id`, token, state, date)VALUES('$unique_id', '$token', '$state', '$date')";
        $verified = parent::nonQuery($query);
        if (!$verified) return 0;
        return $token;
    }
}
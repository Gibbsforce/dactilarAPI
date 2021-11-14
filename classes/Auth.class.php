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
        if (!isset($data["user_login"]) || !isset($data["password"])) return $Responses->error_400();
        // Almacenando datos del usuario en variables y obteniendo sus datos
        $user_login = $data["user_login"];
        $password = $data["password"];
        $password = parent::encrypt($password);
        $data = $this->getUserData($user_login);
        // Validando si datos del usuario existe
        if (!$data) return $Responses->error_200("El usuario ".$user_login." no existe");
        // Validando si la contrasena es correcta
        if ($password !== $data[0]["password"]) return $Responses->error_200("La contrasena es invalida");
        // Validando el estado del usuario
        if ($data[0]["state"] == false) return $Responses->error_200("Usuario inactivo");
        // Validando si se pudo agregar el token
        $verify = $this->addToken($data[0]["username"], $data[0]["dni"], $data[0]["email"]);
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
    // Validando el registro
    public function validate($uid, $token) {
        $Responses = new Responses;
        // Obteniendo los datos de registro seleccionados con el metodo getsignUpData
        $data = $this->getSignUpData($uid);
        // Validando si validate es verdadero
        if ($data[0]["validate"] == true) return $Responses->error_200("The user has already been validated");
        // Validando si el uid es correcto
        if ($data[0]["unique-id"] !== $uid) return $Responses->error_200("Invalid unique id");
        // Validando si el token es correcto
        if ($data[0]["token"] !== $token) return $Responses->error_200("Invalid token");
        // Updaeting validation
        $update = $this->updateValidation($uid);
        // Validando si se pudo actualizar
        if (!$update) return $Responses->error_500("Error interno, no se ha podido actualizar");
        // Obteniendo el resultado
        $result = $Responses->response;
        $result["result"] = array(
            "validation" => true
        );
        return $result;
    }
    // Metodo que obtiene los datos del usuario de la base de datos
    private function getUserData($user_login) {
        // Obteniendo campos de la tabla users-auth
        $query = "SELECT `id-auth`, `username`, `password`, `dni`, `state`, `email` FROM `users-auth` WHERE `username` = '$user_login' OR `dni` = '$user_login' OR `email` = '$user_login'";
        $data = parent::getData($query);
        if (isset($data[0]["id-auth"])) return $data;
        return 0;
    }
    // Metodo que obtiene el id y token del usuario registrado
    private function getSignUpData($uid) {
        $query = "SELECT `id-users`, `unique-id`, `token`, `validate` FROM `users-auth` WHERE `unique-id` = '$uid'";
        $data = parent::getData($query);
        if (isset($data[0]["unique-id"]) && isset($data[0]["token"])) return $data;
        return false;
    }
    // Updating token state and validate
    private function updateValidation($uid) {
        $query = "UPDATE `users-auth` SET `state` = 1, `validate` = 1 WHERE `unique-id` = '$uid'";
        $updated = parent::nonQuery($query);
        if ($updated > 0) return $updated;
        return false;
    }
    // Insertando y creando token a la tabla de la base de datos de users-token
    private function addToken($username, $dni, $email) {
        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(16, $val));
        $date = date("Y-m-d H:i");
        $state = true;
        $query = "INSERT INTO `users-token` (`username`, `dni`, `email`, `token`, `state`, `date`)VALUES('$username', '$dni', '$email', '$token', '$state', '$date')";
        $verified = parent::nonQuery($query);
        if (!$verified) return 0;
        return $token;
    }
}
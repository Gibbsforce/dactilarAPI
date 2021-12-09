<?php
// Accessing classes Connection and Responses
require_once "Connection/Connection.php";
require_once "Responses.class.php";
// Building the Auth class and inheriting from Connection
class Auth extends Connection {
    // Building the loging method
    public function login($json) {
        $Responses = new Responses;
        $data = json_decode($json, true);
        // Validating if user and password exist
        if (!isset($data["user_login"]) || !isset($data["password"])) return $Responses->error_400();
        // Validating if user and password are not empty
        if (empty($data["user_login"])) return $Responses->error_200("User is empty");
        if (empty($data["user_login"])) return $Responses->error_200("The password is empty");
        // Storing client data
        $user_login = $data["user_login"];
        $password = $data["password"];
        $password = parent::encryptOpenSSL($password);
        $data = $this->getUserData($user_login);
        // Validating if user server data exists
        if (!$data) return $Responses->error_200("The user ".$user_login." doesn't exist");
        // Validating if password is correct
        if ($password !== $data[0]["password"]) return $Responses->error_200("The password is wrong");
        // Validating if user has activated its account
        if ($data[0]["validate"] == false) return $Responses->error_200("The user ".$user_login." has no activated its account");
        // Validatning if user is active
        if ($data[0]["state"] == false) return $Responses->error_200("Inactive user");
        // Validating if token added
        $verify = $this->addToken($data[0]["username"], $data[0]["dni"], $data[0]["email"], $data[0]["status"]);
        if (!$verify) return $Responses->error_200("Intern error, couldn't save");
        // Returning the result with the token
        $result = $Responses->response;
        $result["result"] = array(
            "username" => $data[0]["username"],
            "token" => $verify
        );
        return $result;
    }
    // Validating signup method
    public function validate($uid, $token) {
        $Responses = new Responses;
        // Getting data register from server with the getSignUp method
        $data = $this->getSignUpData($uid);
        // Validating if the user has already been validated
        if ($data[0]["validate"] == true) return $Responses->error_200("The user has already been validated");
        // Validating if the unique id is correct
        if ($data[0]["unique-id"] !== $uid) return $Responses->error_200("Invalid unique id");
        // Validating if the token is correct
        if ($data[0]["token"] !== $token) return $Responses->error_200("Invalid token");
        // Updating validation
        $update = $this->updateValidation($uid);
        // Validating if the update was successful
        if (!$update) return $Responses->error_500("Intern error, couldn't save");
        // Getting the result
        $result = $Responses->response;
        $result["result"] = array(
            "validation" => true
        );
        return $result;
    }
    // Getting user data from server method
    private function getUserData($user_login) {
        $query = "SELECT `id-auth`, `username`, `password`, `dni`, `state`, `email`, `status`, `validate` FROM `users-auth` WHERE `username` = '$user_login' OR `dni` = '$user_login' OR `email` = '$user_login'";
        $data = parent::getData($query);
        if (isset($data[0]["id-auth"])) return $data;
        return false;
    }
    // Getting the unique id and token from server method
    private function getSignUpData($uid) {
        $query = "SELECT `id-users`, `unique-id`, `token`, `status`, `validate` FROM `users-auth` WHERE `unique-id` = '$uid'";
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
    // Adding token to server method
    private function addToken($username, $dni, $email, $status) {
        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(16, $val));
        $date = date("Y-m-d H:i");
        $state = true;
        $query = "INSERT INTO `users-token` (`username`, `dni`, `email`, `token`, `state`, `status`, `date`)VALUES('$username', '$dni', '$email', '$token', '$state', '$status', '$date')";
        $verified = parent::nonQuery($query);
        if (!$verified) return false;
        return $token;
    }
}
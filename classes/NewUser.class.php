<?php
// Accediendo a las clases Connection y Responses
require_once "Connection/Connection.php";
require_once "Responses.class.php";
// Creando la clase NewUser y heredando Connection
class NewUser extends Connection {
    private $id_users = "";
    private $name = "";
    private $last_name = "";
    private $dni = "";
    private $phone = "";
    private $email = "";
    private $password = ""; 
    private $address = "";
    private $country = "";
    private $state_city = "";
    private $city_district = "";
    private $zipcode = "";
    private $created = "0000-00-00";
    private $username = "";
    private $token = "";
    private $image = "";
    private $status = "";
    private $check_password = "";

    public function signUp($json) {
        $Responses = new Responses();
        $data = json_decode($json, true);

        if (
            !isset($data["name"]) ||
            !isset($data["last_name"]) ||
            !isset($data["dni"]) ||
            !isset($data["phone"]) ||
            !isset($data["email"]) ||
            !isset($data["password"]) ||
            !isset($data["check-password"]) ||
            !isset($data["address"]) ||
            !isset($data["country"]) ||
            !isset($data["state-city"]) ||
            !isset($data["city-district"]) ||
            !isset($data["zipcode"]) ||
            !isset($data["username"])
        ) return $Responses->error_400();

        $this->name = $data["name"];
        $this->last_name = $data["last_name"];
        $this->dni = $data["dni"];
        $this->phone = $data["phone"];
        $this->email = $data["email"];
        $this->password = $data["password"];
        $this->check_password = $data["check-password"];
        $this->address = $data["address"];
        $this->country = $data["country"];
        $this->state_city = $data["state-city"];
        $this->city_district = $data["city-district"];
        $this->zipcode = $data["zipcode"];
        $this->username = $data["username"];
        $this->created = date("Y-m-d H:i");
        $this->status = "user";

        $val = true;
        $this->token = bin2hex(openssl_random_pseudo_bytes(16, $val));

        if (isset($data["image"])) $this->image = $data["image"];

        if (strlen($this->dni) != 8 || !is_numeric($this->dni)) return $Responses->error_200("Please, add a valid DNI number");
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) return $Responses->error_200("Please, add a valid email");
        if (strlen($this->username) < 6) return $Responses->error_200("Username too small");
        if (strlen($this->username) > 32) return $Responses->error_200("Username too large");
        if (strlen($this->phone) < 9 || !is_numeric($this->phone)) return $Responses->error_200("Please, add a valid phone number");
        if (strlen($this->zipcode) < 4 || !is_numeric($this->zipcode)) return $Responses->error_200("Please, add a valid zipcode");
        if (strlen($this->password) < 8 || strlen($this->check_password) < 8) return $Responses->error_200("Password too small");
        if (strlen($this->password) > 32 || strlen($this->check_password) > 32) return $Responses->error_200("Password too large");

        if ($this->password != $this->check_password) return $Responses->error_200("The passwords don't match");

        $result_user_exist = $this->existingUser($this->dni, $this->email, $this->username);
        if ($result_user_exist[0]["dni"] === $this->dni) return $Responses->error_200("DNI number already exists");
        if ($result_user_exist[0]["email"] === $this->email) return $Responses->error_200("The email already exists");
        if ($result_user_exist[0]["username"] === $this->username) return $Responses->error_200("The username already exists");

        $result = $this->addUser();
        if ($result) return $Responses->error_201("User created successfully");
        return $Responses->error_500();
    }

    private function existingUser($dni, $email, $username) {
        $query = "SELECT `dni`, `email`, `username` FROM `users` WHERE `dni` = '".$dni."' OR `email` = '".$email."' OR `username` = '".$username."'";
        $result = parent::getData($query);
        if ($result) return $result;
        return false;
    }

    private function addUser() {
        $query = "INSERT INTO `users` (
            `name`,
            `last_name`,
            `dni`,
            `phone`,
            `email`,
            `address`,
            `country`,
            `state-city`,
            `city-district`,
            `zipcode`,
            `username`,
            `created`,
            `image`
        )VALUES(
            '".$this->name."',
            '".$this->last_name."',
            '".$this->dni."',
            '".$this->phone."',
            '".$this->email."',
            '".$this->address."',
            '".$this->country."',
            '".$this->state_city."',
            '".$this->city_district."',
            '".$this->zipcode."',
            '".$this->username."',
            '".$this->created."',
            '".$this->image."'
        )";
        $result = parent::nonQuery($query);
        if (!$result) return false;
        return true;
    }
}
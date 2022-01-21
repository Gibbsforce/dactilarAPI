<?php
// Accessing Connection and Responses classes
require_once "Connection/Connection.php";
require_once "Responses.class.php";
// Building Users class and inheritate from Connection
class Users extends Connection {
    // Assinging users table in local private variable
    private $table = "users";
    // Data to local variables
    private $name = "";
    private $last_name = "";
    private $dni = "";
    private $phone = "";
    private $email = "";
    private $address = "";
    private $country = "";
    private $state_city = "";
    private $city_district = "";
    private $zipcode = "";
    private $created = "";
    private $username = "";
    private $image = "";
    private $token = "";
    // Getting data from users table and obtaining total data
    public function usersList($token, $page = 1) {
        $Responses = new Responses();
        $this->token = $token;
        $arr_token = $this->searchToken();
        if (!$arr_token) return $Responses->error_401("Unauthorized or your token has been deprecated");
        $status = $arr_token[0]["status"];
        if ($status !== "admin") return $Responses->error_401();
        $start = 0;
        $qty = 5;
        if ($page > 1) $start = $qty * ($page - 1);
        $query_total = "SELECT `id-users` FROM ".$this->table."";
        $total = parent::getData($query_total); // maybe check this in the future
        $query = 
            "SELECT
                `id-users`,
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
                `created`,
                `username`,
                `image`,
                `cart`
            FROM ".$this->table." ORDER BY `id-users` ASC limit $start, $qty";
        try {
            $data = parent::getData($query);
            if (!isset($data)) return $this->Responses->error_500();
            $result = array(
                "page" => intval($page),
                "results" => $data,
                "total_pages" => ceil(count($total) / $qty),
                "total_results" => count($total)
            );
            return $result;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
    // Getting user data by username
    public function getUser($token, $uname) {
        $Responses = new Responses();
        $this->token = $token;
        $arr_token = $this->searchToken();
        if (!$arr_token) return $Responses->error_401("Unauthorized or your token has been deprecated");
        $username = $arr_token[0]["username"];
        if ($username !== $uname) return $Responses->error_401();
        $query = "SELECT * FROM ".$this->table." WHERE `username` = '$uname'";
        try {
            $result = parent::getData($query);
            if (!isset($result)) return $this->Responses->error_500();
            return $result;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
    // POST method to create user
    public function post($json) {
        $Responses = new Responses;
        $data = json_decode($json, true);
        // Verifying if token is being sent
        if (!isset($data["token"])) return $Responses->error_401();
        $this->token = $data["token"];
        $arr_token = $this->searchToken();
        if (!$arr_token) return $Responses->error_401("Unauthorized or your token has been deprecated");
        // Only admin can create user this way
        if ($arr_token[0]["status"] !== "admin") return $Responses->error_401();
        // Name, dni and email mandatory
        if (!isset($data["dni"]) || !isset($data["email"])) return $Responses->error_400();
        $this->dni = $data["dni"];
        $this->email = $data["email"];
        // Sent user data to local variables
        if (isset($data["name"])) $this->name = $data["name"];
        if (isset($data["last_name"])) $this->last_name = $data["last_name"];
        if (isset($data["phone"])) $this->phone = $data["phone"];
        if (isset($data["address"])) $this->address = $data["address"];
        if (isset($data["country"])) $this->country = $data["country"];
        if (isset($data["state-city"])) $this->state_city = $data["state-city"];
        if (isset($data["city-district"])) $this->city_district = $data["city-district"];
        if (isset($data["zipcode"])) $this->zipcode = $data["zipcode"];
        if (isset($data["created"])) $this->created = $data["created"];
        if (isset($data["username"])) $this->username = $data["username"];
        // image *fix this later*
        if (isset($data["image"])) {
            $img = $this->processImage($data["image"]);
            $this->image = $img;
        }
        try {
            $added = $this->addUser();
            if (!$added) return $Responses->error_500();
            $response = $Responses->response;
            $response["result"] = array(
                "id-users" => $added
            );
            return $response;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
    // Query that creates user
    private function addUser() {
        $this->created = date("Y-m-d H:i:s");
        $query = "INSERT INTO ".$this->table." (
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
            `created`,
            `username`,
            `image`)VALUES(
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
                '".$this->created."',
                '".$this->username."',
                '".$this->image."'
            )";
        $added = parent::nonQueryId($query);
        if ($added) return $added;
        return false;
    }
    // PUT method that updates user
    public function put($json) {
        $Responses = new Responses;
        $data = json_decode($json, true);
        // Verifying if token is being sent
        if (!isset($data["token"])) return $Responses->error_401();
        $this->token = $data["token"];
        $arr_token = $this->searchToken();
        if (!$arr_token) return $Responses->error_401("Token enviado invalido o ha caducado");
        // Getting username and make it mandatory
        if (!isset($data["username"])) return $Responses->error_400();
        $this->username = $data["username"];
        $uname = $arr_token[0]["username"]
        if ($uname !== $this->username) return $Responses->error_401();
        // Fields to update
        if (isset($data["name"])) {
            if (!preg_match("/^([a-zA-Z']+)$/", $data["name"]) return $Responses->error_200("Please, add a valid name");
            $this->name = $data["name"];
        }
        if (isset($data["last_name"])) {
            if (!preg_match("/^([a-zA-Z']+)$/", $data["last_name"]) return $Responses->error_200("Please, add a valida username");
            $this->last_name = $data["last_name"];
        }
        if (isset($data["dni"])) {
            if (strlen($data["dni"]) < 8 || !is_numeric($data["dni"])) return $Responses->error_200("Please, add a valid DNI number");
            $this->dni = $data["dni"];
        }
        if (isset($data["phone"])) {
            if (strlen($data["phone"]) < 9 || !is_numeric($data["phone"])) return $Responses->error_200("Please, add a valid phone number");
            $this->phone = $data["phone"];
        }
        if (isset($data["email"])) {
            if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) return $Responses->error_200("Please, add a valid email");
            $this->email = $data["email"];
        }
        if (isset($data["address"])) {
            $this->address = $data["address"];
        }
        if (isset($data["country"])) {
            $this->country = $data["country"];
        }
        if (isset($data["state-city"])) {
            $this->state_city = $data["state-city"];
        }
        if (isset($data["city-district"])) {
            $this->city_district = $data["city-district"];
        }
        if (isset($data["zipcode"])) {
            if (strlen($data["zipcode"]) < 4 || !is_numeric($data["zipcode"])) return $Responses->error_200("Please, add a valid zipcode");
            $this->zipcode = $data["zipcode"];
        }
        if (isset($data["created"])) {
            $this->created = date("Y-m-d H:i:s");
        }
        if (isset($data["username"])) {
            if (strlen($data["username"]) < 7) return $Responses->error_200("Username too small");
            if (strlen($data["username"]) > 32) return $Responses->error_200("Username too large");
            $this->username = $data["username"];
        }
        $result_user_exist = $this->existingUser($this->dni, $this->email, $this->username);
        if ($result_user_exist[0]["dni"] === $this->dni) return $Responses->error_200("DNI number already exists");
        if ($result_user_exist[0]["email"] === $this->email) return $Responses->error_200("The email already exists");
        if (strtolower($result_user_exist[0]["username"]) === strtolower($this->username)) return $Responses->error_200("The username already exists");
        try {
            $added = $this->updateUser();
            if (!$added) return $Responses->error_500();
            $response = $Responses->response;
            $response["result"] = array(
                "username" => $this->username
            );
            return $response;
        } catch (PDOException $error) {
            return Responses::prepare(500, $error->getMessage());
        }
    }
    // Metodo del query que actualiza usuario
    private function updateUser() {
        $query = "UPDATE ".$this->table."
            SET `name` = '".$this->name."',
                `last_name` = '".$this->last_name."',
                `dni` = '".$this->dni."',
                `phone` = '".$this->phone."',
                `email` = '".$this->email."',
                `address` = '".$this->address."',   
                `country` = '".$this->country."',
                `state-city` = '".$this->state_city."',
                `city-district` = '".$this->city_district."',
                `zipcode` = '".$this->zipcode."',
                `created` = '".$this->created."',
                `username` = '".$this->username."
            ' WHERE `username` = '".$this->username."'";
        $added = parent::nonQuery($query);
        if ($added > 0) return $added;
        return false;
    }
    // Metodo DELETE para eliminar usuario
    public function delete($json) {
        $Responses = new Responses;
        $data = json_decode($json, true);
        // Validando si existe token
        if (!isset($data["token"])) return $Responses->error_401();
        $this->token = $data["token"];
        $array_token = $this->searchToken();
        if (!$array_token) return $Responses->error_401("Token enviado invalido o ha caducado");
        // Campo id-users obligatorio
        if (!isset($data["id-users"])) return $Responses->error_400();
        $this->id_users = $data["id-users"];
        // Eliminando usuario
        $added = $this->removeUser();
        if (!$added) return $Responses->error_500();
        $response = $Responses->response;
        $response["result"] = array(
            "id-users" => $this->id_users
        );
        return $response;
    }
    // Metodo del query que elimina usuario
    private function removeUser() {
        $query = "DELETE FROM ".$this->table." WHERE `id-users` = '".$this->id_users."'";
        $removed = parent::nonQuery($query);
        if ($removed > 0) return $removed;
        return false;
    }
    // Metodo para buscar token
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
    // Metodo que actualiza token
    private function updateToken($id_token) {
        $date = date("Y-m-d H:i");
        $query = "UPDATE `users-token` SET `date` = '".$date."' WHERE `id-token` = '".$id_token."'";
        $updated = parent::nonQuery($query);
        if ($updated > 0) return $updated;
        return false;
    }
    // Metodo para imagen
    public function processImage($image) {
        $dir = dirname(__DIR__)."/public/images/";
        $array_image = explode(";base64,", $image);
        $ext = explode("/", mime_content_type($image))[1];
        $image_base64 = base64_decode($array_image[1]);
        $file = $dir.uniqid().".".$ext;
        file_put_contents($file, $image_base64);
        // $new_dir = str_replace("\\", "/", $file);
        return $file;
    }
    // Method for existing user
    private function existingUser($dni, $email, $username) {
        $query = "SELECT `dni`, `email`, `username` FROM `users` WHERE `dni` = '".$dni."' OR `email` = '".$email."' OR `username` = '".$username."'";
        $result = parent::getData($query);
        if ($result) return $result;
        return false;
    }
}
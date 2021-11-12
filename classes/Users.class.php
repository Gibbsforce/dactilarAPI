<?php
// Accediendo a las clases Connection y Responses
require_once "Connection/Connection.php";
require_once "Responses.class.php";
// Creando la clase Users y heredando de Connection
class Users extends Connection {
    // Asignando tabla users a una variable privada
    private $table = "users";
    private $id_users = "";
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
    private $created = "0000-00-00";
    private $username = "";
    private $token = "";
    private $image = "";
    // Obteniendo datos de la tabla users y filtrando cantidad de datos obtenidos
    public function usersList($page = 1) {
        $start = 0;
        $quanty = 5;
        if ($page > 1) $start = ($quanty * ($page - 1));
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
                `username`
            FROM ".$this->table." limit $start, $quanty";
        $data = parent::getData($query);
        return $data;
    }
    // Obteniendo datos de la tabla users y obteniendo usuario por id
    public function getUser ($id) {
        $query = "SELECT * FROM ".$this->table." WHERE `id-users` = '$id'";
        return parent::getData($query);
    }
    // Meotodo POST para crear usuario
    public function post($json) {
        $Responses = new Responses;
        $data = json_decode($json, true);
        // Validando si existe token
        if (!isset($data["token"])) return $Responses->error_401();
        $this->token = $data["token"];
        $array_token = $this->searchToken();
        if (!$array_token) return $Responses->error_401("Token enviado invalido o ha caducado");
        // Campos name, dni e email obligatorios
        if (!isset($data["name"]) || !isset($data["dni"]) || !isset($data["email"])) return $Responses->error_400();
        $this->name = $data["name"];
        $this->dni = $data["dni"];
        $this->email = $data["email"];
        // if (isset($data["id-users"])) $this->id_users = $data["id-users"];
        if (isset($data["last_name"])) $this->last_name = $data["last_name"];
        if (isset($data["phone"])) $this->phone = $data["phone"];
        if (isset($data["address"])) $this->address = $data["address"];
        if (isset($data["country"])) $this->country = $data["country"];
        if (isset($data["state-city"])) $this->state_city = $data["state-city"];
        if (isset($data["city-district"])) $this->city_district = $data["city-district"];
        if (isset($data["zipcode"])) $this->zipcode = $data["zipcode"];
        if (isset($data["created"])) $this->created = $data["created"];
        if (isset($data["username"])) $this->username = $data["username"];

        // imagen
        if (isset($data["image"])) {
            $img = $this->processImage($data["image"]);
            $this->image = $img;
        }

        $added = $this->addUser();
        if (!$added) return $Responses->error_500();
        $response = $Responses->response;
        $response["result"] = array(
            "id-users" => $added
        );
        return $response;
    }
    // Metodo del query que crea usuario
    private function addUser() {
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
        return 0;
    }
    // Meotodo PUT para actualizar usuario
    public function put($json) {
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
        // Campos a actualizar
        if (isset($data["name"])) $this->name = $data["name"];
        if (isset($data["dni"])) $this->dni = $data["dni"];
        if (isset($data["email"])) $this->email = $data["email"];
        if (isset($data["last_name"])) $this->last_name = $data["last_name"];
        if (isset($data["phone"])) $this->phone = $data["phone"];
        if (isset($data["address"])) $this->address = $data["address"];
        if (isset($data["country"])) $this->country = $data["country"];
        if (isset($data["state-city"])) $this->state_city = $data["state-city"];
        if (isset($data["city-district"])) $this->city_district = $data["city-district"];
        if (isset($data["zipcode"])) $this->zipcode = $data["zipcode"];
        if (isset($data["created"])) $this->created = $data["created"];
        if (isset($data["username"])) $this->username = $data["username"];
        $added = $this->updateUser();
        if (!$added) return $Responses->error_500();
        $response = $Responses->response;
        $response["result"] = array(
            "id-users" => $this->id_users
        );
        return $response;
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
            ' WHERE `id-users` = '".$this->id_users."'";
        $added = parent::nonQuery($query);
        if ($added > 0) return $added;
        return 0;
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
        return 0;
    }
    // Metodo para buscar token
    private function searchToken() {
        $query = "SELECT `id-token`, `unique-id`, `state` FROM `users-token` WHERE `token` = '".$this->token."' AND `state` = 1";
        $result = parent::getData($query);
        if ($result) return $result;
        return 0;
    }
    // Metodo que actualiza token
    private function updateToken($id_token) {
        $date = date("Y-m-d H:i");
        $query = "UPDATE `users-token` SET `date` = '".$date."' WHERE `id-token` = '".$id_token."'";
        $updated = parent::nonQuery($query);
        if ($updated > 0) return $updated;
        return 0;
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
}
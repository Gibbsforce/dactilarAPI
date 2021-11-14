<?php

require_once "Connection/Connection.php";
require_once "Responses.class.php";

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
    private $created = "";
    private $username = "";
    private $token = "";
    private $image = "";
    private $status = "";
    private $check_password = "";
    private $unique_id = "";

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

        if (empty($data["name"])) return $Responses->error_200("The name is empty");
        if (empty($data["last_name"])) return $Responses->error_200("The last name is empty");
        if (empty($data["dni"])) return $Responses->error_200("DNI number is empty");
        if (empty($data["phone"])) return $Responses->error_200("The phone number is empty");
        if (empty($data["email"])) return $Responses->error_200("The email is empty");
        if (empty($data["password"])) return $Responses->error_200("The password is empty");
        if (empty($data["check-password"])) return $Responses->error_200("The password is empty");
        if (empty($data["address"])) return $Responses->error_200("The address is empty");
        if (empty($data["country"])) return $Responses->error_200("The country is empty");
        if (empty($data["state-city"])) return $Responses->error_200("The state/city is empty");
        if (empty($data["city-district"])) return $Responses->error_200("The city/district is empty");
        if (empty($data["zipcode"])) return $Responses->error_200("The zipcode is empty");
        if (empty($data["username"])) return $Responses->error_200("The username is empty");

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

        if (isset($data["image"])) $this->image = $data["image"];

        if (strlen($this->dni) <= 8 || !is_numeric($this->dni)) return $Responses->error_200("Please, add a valid DNI number");
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) return $Responses->error_200("Please, add a valid email");
        if (strlen($this->username) < 7) return $Responses->error_200("Username too small");
        if (strlen($this->username) > 32) return $Responses->error_200("Username too large");
        if (strlen($this->phone) < 9 || !is_numeric($this->phone)) return $Responses->error_200("Please, add a valid phone number");
        if (strlen($this->zipcode) < 4 || !is_numeric($this->zipcode)) return $Responses->error_200("Please, add a valid zipcode");
        if (strlen($this->password) < 8 || strlen($this->check_password) < 8) return $Responses->error_200("Password should be min 8 characteres");
        if (strlen($this->password) > 32 || strlen($this->check_password) > 32) return $Responses->error_200("Password too large");

        if ($this->password != $this->check_password) return $Responses->error_200("The passwords don't match");

        $result_user_exist = $this->existingUser($this->dni, $this->email, $this->username);
        
        if ($result_user_exist[0]["dni"] === $this->dni) return $Responses->error_200("DNI number already exists");
        if ($result_user_exist[0]["email"] === $this->email) return $Responses->error_200("The email already exists");
        if ($result_user_exist[0]["username"] === $this->username) return $Responses->error_200("The username already exists");

        $val = true;
        $this->token = bin2hex(openssl_random_pseudo_bytes(16, $val));
        
        $this->unique_id = parent::encrypt($this->dni.$this->username);
        $uid = $this->unique_id;

        $result_add_user = $this->addUser();
        if (!$result_add_user) return $Responses->error_500();

        $result_add_user_auth = $this->addUserAuth($result_add_user, $uid);
        if (!$result_add_user_auth) return $Responses->error_500();

        $sent_email_validation = $this->sendEmailValidation($result_add_user, $uid, $this->token, $this->name, $this->email);
        if (!$sent_email_validation) return $Responses->error_500();

        $response = $Responses->response;
        $response["result"] = array(
            "id-users" => $result_add_user,
            "token" => $this->token,
            "state" => "User created succesfully",
            "validation" => false,
            "email" => "Sent, validate account"
        );
        return $response;
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
        $result = parent::nonQueryId($query);
        if ($result) return $result;
        return false;
    }

    private function addUserAuth($id_users, $uid) {
        $password = parent::encrypt($this->password);
        $query = "INSERT INTO `users-auth` (
            `id-users`,
            `username`,
            `password`,
            `dni`,
            `unique-id`,
            `state`,
            `date`,
            `email`,
            `token`,
            `status`,
            `validate`
        )VALUES(
            '".$id_users."',
            '".$this->username."',
            '".$password."',
            '".$this->dni."',
            '".$uid."',
            0,
            '".$this->created."',
            '".$this->email."',
            '".$this->token."',
            '".$this->status."',
            0
        )";
        $result = parent::nonQuery($query);
        if (!$result) return false;
        return true;
    }

    private function sendEmailValidation($id_users, $unique_id, $token, $name, $email) {
        $url = "http://".$_SERVER["SERVER_NAME"]."/auth?id=".$id_users."&uid=".$unique_id."&token=".$token."";
        $subject = "Account Validation - Dactilar";
        $body = "
            <html>
                <head>
                    <title>Email validation</title>
                </head>
                <body>
                <h1>Hi ".$name."! Welcome to Dactilar</h1>
                    <p>
                        To validate your account, please click on the link below:
                    </p>
                <a href='".$url."'>".$url."</a>
                </body>
            </html>
        ";
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        // Additional headers
        $headers[] = 'From: Dactilar <servicioalcliente@dactilar.com.pe>';
        $mail = mail($email, $subject, $body, implode("\r\n", $headers));
        if (!$mail) return false;
        return true;
    }
}
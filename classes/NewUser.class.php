<?php
// Accessing classes Connection and Responses
require_once "Connection/Connection.php";
require_once "Responses.class.php";
// Building the NewUser class and inheriting from Connection
class NewUser extends Connection {
    // Variables
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
    // signUp method
    public function signUp($json) {
        // Responses
        $Responses = new Responses();
        $data = json_decode($json, true);
        // Validating data
        if (
            !isset($data["name"]) ||
            !isset($data["last_name"]) ||
            !isset($data["dni"]) ||
            !isset($data["phone"]) ||
            !isset($data["email"]) ||
            !isset($data["password"]) ||
            !isset($data["check_password"]) ||
            !isset($data["address"]) ||
            !isset($data["country"]) ||
            !isset($data["state_city"]) ||
            !isset($data["city_district"]) ||
            !isset($data["zipcode"]) ||
            !isset($data["username"])
        ) return $Responses->error_400();
        // Not empty
        if (empty($data["name"])) return $Responses->error_200("The name is empty");
        if (empty($data["last_name"])) return $Responses->error_200("The last name is empty");
        if (empty($data["dni"])) return $Responses->error_200("DNI number is empty");
        if (empty($data["phone"])) return $Responses->error_200("The phone number is empty");
        if (empty($data["email"])) return $Responses->error_200("The email is empty");
        if (empty($data["password"])) return $Responses->error_200("The password is empty");
        if (empty($data["check_password"])) return $Responses->error_200("The password is empty");
        if (empty($data["address"])) return $Responses->error_200("The address is empty");
        if (empty($data["country"])) return $Responses->error_200("The country is empty");
        if (empty($data["state_city"])) return $Responses->error_200("The state/city is empty");
        if (empty($data["city_district"])) return $Responses->error_200("The city/district is empty");
        if (empty($data["zipcode"])) return $Responses->error_200("The zipcode is empty");
        if (empty($data["username"])) return $Responses->error_200("The username is empty");
        // Assigning data to the variables
        $this->name = $data["name"];
        $this->last_name = $data["last_name"];
        $this->dni = $data["dni"];
        $this->phone = $data["phone"];
        $this->email = $data["email"];
        $this->password = $data["password"];
        $this->check_password = $data["check_password"];
        $this->address = $data["address"];
        $this->country = $data["country"];
        $this->state_city = $data["state_city"];
        $this->city_district = $data["city_district"];
        $this->zipcode = $data["zipcode"];
        $this->username = $data["username"];
        $this->created = date("Y-m-d H:i");
        $this->status = "user";
        // Image **missing the setting**
        if (isset($data["image"])) $this->image = $data["image"];
        // Validating the data
        if (strlen($this->dni) < 8 || !is_numeric($this->dni)) return $Responses->error_200("Please, add a valid DNI number");
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) return $Responses->error_200("Please, add a valid email");
        if (strlen($this->username) < 7) return $Responses->error_200("Username too small");
        if (strlen($this->username) > 32) return $Responses->error_200("Username too large");
        if (strlen($this->phone) < 9 || !is_numeric($this->phone)) return $Responses->error_200("Please, add a valid phone number");
        if (strlen($this->zipcode) < 4 || !is_numeric($this->zipcode)) return $Responses->error_200("Please, add a valid zipcode");
        if (strlen($this->password) < 8 || strlen($this->check_password) < 8) return $Responses->error_200("Passwords should be min 8 characteres");
        if (strlen($this->password) > 32 || strlen($this->check_password) > 32) return $Responses->error_200("Passwords too large");
        // Password match
        if ($this->password != $this->check_password) return $Responses->error_200("The passwords don't match");
        // Checking if the user is already registered
        $result_user_exist = $this->existingUser($this->dni, $this->email, $this->username);
        if ($result_user_exist[0]["dni"] === $this->dni) return $Responses->error_200("DNI number already exists");
        if ($result_user_exist[0]["email"] === $this->email) return $Responses->error_200("The email already exists");
        if ($result_user_exist[0]["username"] === $this->username) return $Responses->error_200("The username already exists");
        // Generating the token
        $val = true;
        $this->token = bin2hex(openssl_random_pseudo_bytes(16, $val));
        // Generating and encrypting the unique id
        $this->unique_id = parent::encrypt($this->dni.$this->username);
        $uid = $this->unique_id;
        // Inserting the user into the database
        $result_add_user = $this->addUser();
        if (!$result_add_user) return $Responses->error_500();
        // Inserting the user into the authentication table
        $result_add_user_auth = $this->addUserAuth($result_add_user, $uid);
        if (!$result_add_user_auth) return $Responses->error_500();
        // Sending the email for validation
        $sent_email_validation = $this->sendEmailValidation($result_add_user, $uid, $this->token, $this->name, $this->email);
        if (!$sent_email_validation) return $Responses->error_500();
        // Returning the response
        $response = $Responses->response;
        $response["result"] = array(
            "id-users" => $result_add_user,
            "uid" => $uid,
            "token" => $this->token,
            "state" => "User created succesfully",
            "validation" => false,
            "email" => "Sent, validate account"
        );
        return $response;
    }
    // Method for existing user
    private function existingUser($dni, $email, $username) {
        $query = "SELECT `dni`, `email`, `username` FROM `users` WHERE `dni` = '".$dni."' OR `email` = '".$email."' OR `username` = '".$username."'";
        $result = parent::getData($query);
        if ($result) return $result;
        return false;
    }
    // Method for adding user
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
    // Method for adding user authentication
    private function addUserAuth($id_users, $uid) {
        $password = parent::encrypt($this->password);
        $status = "user";
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
            '".$status."',
            0
        )";
        $result = parent::nonQuery($query);
        if (!$result) return false;
        return true;
    }
    // Method that send the email for validarion
    private function sendEmailValidation($id_users, $unique_id, $token, $name, $email) {
        $url = "https://".$_SERVER["SERVER_NAME"]."/auth?id=".$id_users."&uid=".$unique_id."&token=".$token."";
        $subject = "Account Validation - Dactilar";
        $body = "
            <html>
                <head>
                    <title>Email validation</title>
                    <style>
                        * {
                            box-sizing: border-box;
                            user-select: none;
                            font-family: 'AvenirLTW01-95BlackObli', sans-serif;
                            text-align: center;
                        }
                        h1 {
                            color: #032d28;
                        }
                        p {
                            color: #667c7e;
                        }
                        a {
                            text-decoration: none;
                            color: #667c7e;
                            transition: .5s ease-in-out;
                        }
                        a:hover {
                            color: black;
                            transition: .5s ease-in-out;
                        }
                        .container {
                            max-width: 100%;
                            width: 100%;
                            height: 100vh;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            flex-direction: column;
                            background-color: rgba(0, 0, 0, 0.05);
                        }
                        .logo-cover img {
                            max-width: 100%;
                            width: 220px;
                            height: 150px;
                        }
                        .logo-font img {
                            max-width: 100%;
                            width: 504px;
                            height: 150px;
                        }
                        button {
                            display: block;
                            background: white;
                            color: #667c7e;
                            text-transform: uppercase;
                            border: 1px solid #667c7e;
                            font-size: 1.2rem;
                            outline: none;
                            margin: 20px auto;
                            padding: 10px 20px;
                            cursor: pointer;
                            transition: .5s ease-in-out;
                        }
                        button:hover {
                            opacity: .9;
                            background: #667c7e;
                            transition: .5s ease-in-out;
                            border: 1px solid white;
                            color: white;
                        }
                
                        @media screen and (max-width: 520px) {
                            .logo-cover img {
                                max-width: 100%;
                                width: 147px;
                                height: 100px;
                            }
                
                            .logo-font img {
                                max-width: 100%;
                                width: 336px;
                                height: 100px;
                            }
                        }
                    </style>
                </head>
                <body>
                <div class='container'>
                    <div class='logo-cover'>
                        <img src='https://".$_SERVER["SERVER_NAME"]."/public/logo/logo_cover_gris.png' alt='logo_cover_gris'>
                    </div>
                    <h1>Email Address Verification</h1>
                    <div class='logo-font'>
                        <img src='https://".$_SERVER["SERVER_NAME"]."/public/logo/logo_font_horizontal_black.png'
                            alt='logo_font_horizontal_black'>
                    </div>
            
                    <h1>Hi ".$name."! Welcome to Dactilar</h1>
                    <p>
                        To validate your account, please click on the link below:
                    </p>
                    <div class='btn'>
                        <button class='btn-url'>
                            Verify Email
                        </button>
                    </div>
                    <p>
                        This link will expire in 24 hours. To request a new verification link, please <a href=''>log in</a> to
                        prompt a re-send link.
                    </p>
                    <p>
                        If you did not request this email, please ignore it.
                    </p>
                </div>
                <script>
                    const btn = document.querySelector('.btn-url');
                    btn.addEventListener('click', () => {
                        window.location.href = '".$url."';
                    });
                </script>
                </body>
            </html>
        ";
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';

        $headers[] = 'From: Dactilar <servicioalcliente@dactilar.com.pe>';
        $mail = mail($email, $subject, $body, implode("\r\n", $headers));
        if (!$mail) return false;
        return true;
    }
}
<?php
// Building the Connection class in order to connect to a database
class Connection {
    private $server;
    private $user;
    private $password;
    private $database;
    private $port;
    private $connection;
    // Initializing the constructor
    function __construct() {
        // Storing the data config extensionless
        $data_connection = $this->dataConfig();
        // Looping trough the data config
        foreach(array($data_connection["connection"]) as $key => $value) {
            $this->server = $value["server"];
            $this->user = $value["user"];
            $this->password = $value["password"];
            $this->database = $value["database"];
            $this->port = $value["port"];
        }
        // Initializing the connection
        $this->connection = new mysqli(
            $this->server,
            $this->user,
            $this->password,
            $this->database,
            $this->port
        );
        // Handling the connection error
        if ($this->connection->connect_errno) {
            echo "Couldn't connect";
            die();
        }
    }
    // Method that gets a json encode for config connection data
    private function dataConfig() {
        // $dir = dirname(__FILE__);
        $dir = $_SERVER["HOME"];
        $json_data = file_get_contents($dir."/"."config"."/"."config"."."."json");
        return json_decode($json_data, true);
    }
    // Method that converts the charset into UTF8
    private function toUTF8($arr) {
        array_walk_recursive($arr, function(&$item, $key) {
            if (!mb_detect_encoding($item, "utf-8", true)) return $item = utf8_encode($item);
        });
        return $arr;
    }
    //  Getting the data of a table of the database
    public function getData($query) {
        $result =  $this->connection->query($query);
        $arr_result = array();
        foreach ($result as $key) {
            $arr_result[] = $key;
        }
        return $this->toUTF8($arr_result);
    }
    // Inserting data into a table and returning the affected rows
    public function nonQuery($query) {
        $this->connection->set_charset("utf8");
        $result =  $this->connection->query($query);
        return $this->connection->affected_rows;
    }
    // Inserting the data en return the id of added row
    public function nonQueryId($query) {
        $this->connection->set_charset("utf8");
        $result =  $this->connection->query($query);
        $rows = $this->connection->affected_rows;
        if ($rows > 0) return $this->connection->insert_id;
        return 0;
    }
    // Method that encrypts data by md5 (password ie)
    protected function encrypt ($str) {
        return md5($str);
    }
    // Methods that encrypts and decrypts data by openssl
    protected function encryptOpenSSL ($str) {
        $data_credentials = $this->dataConfig();
        foreach(array($data_credentials["credentials"]) as $key => $value) {
            $passkey = $value["key"];
            $method = $value["method"];
            $iv = $value["iv"];
        }
        $encrypt = openssl_encrypt($str, $method, $passkey, 0, $iv);
        return $encrypt;
    }
    protected function decryptOpenSSL ($str) {
        $data_credentials = $this->dataConfig();
        foreach(array($data_credentials["credentials"]) as $key => $value) {
            $passkey = $value["key"];
            $method = $value["method"];
            $iv = $value["iv"];
        }
        $decrypt = openssl_decrypt($str, $method, $key, 0, $iv);
        return $decrypt;
    }
}
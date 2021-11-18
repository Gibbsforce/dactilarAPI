<?php
// Creando la clase Connection para conectarse a la base de datos
class Connection {
    private $server;
    private $user;
    private $password;
    private $database;
    private $port;
    private $connection;
    // Iniciando el constructor
    function __construct() {
        // Almacenando los datos para la configuracion en la base de datos de la funcion en una variable
        $data_list = $this->dataConnection();
        // Recorriendo los datos
        foreach($data_list as $key => $value) {
            $this->server = $value["server"];
            $this->user = $value["user"];
            $this->password = $value["password"];
            $this->database = $value["database"];
            $this->port = $value["port"];
        }
        // Estableciendo la conexion
        $this->connection = new mysqli(
            $this->server,
            $this->user,
            $this->password,
            $this->database,
            $this->port
        );
        // Manejando los errores de conexion
        if ($this->connection->connect_errno) {
            echo "No se ha podido establecer una conexion";
            die();
        }
    }
    // Funcion que obtiene un json encode de los datos para la configuracion en la base de datos
    private function dataConnection() {
        $dir = dirname(__FILE__);
        $json_data = file_get_contents($dir."/"."config");
        return json_decode($json_data, true);
    }
    // Funcion que convierte los caracteres a la codificacion de UTF8
    private function toUTF8($arr) {
        array_walk_recursive($arr, function(&$item, $key) {
            if (!mb_detect_encoding($item, "utf-8", true)) return $item = utf8_encode($item);
        });
        return $arr;
    }
    // Obtener los datos de una tabla de la base de datos
    public function getData($query) {
        $result =  $this->connection->query($query);
        $arr_result = array();
        foreach ($result as $key) {
            $arr_result[] = $key;
        }
        return $this->toUTF8($arr_result);
    }
    // Agregar datos a una tabla y devolviendo la cantidad de filas afectadas
    public function nonQuery($query) {
        $this->connection->set_charset("utf8");
        $result =  $this->connection->query($query);
        return $this->connection->affected_rows;
    }
    // Agrega datos y devuelve el id la fila que se agrego
    public function nonQueryId($query) {
        $this->connection->set_charset("utf8");
        $result =  $this->connection->query($query);
        $rows = $this->connection->affected_rows;
        if ($rows > 0) return $this->connection->insert_id;
        return 0;
    }
    // Metodo que encripta por md5 los datos (contrasena)
    protected function encrypt ($str) {
        return md5($str);
    }
}
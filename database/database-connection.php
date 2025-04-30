<?php

class Database{

    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct()
    {
        if($_SERVER['SERVER_NAME'] === "localhost" || $_SERVER['SERVER_ADDR'] === "127.0.0.1"){
            $this->host = "localhost";
            $this->db_name = "bank-acc-ms";
            $this->username = "root";
            $this->password = "";
        }else{
            $this->host = "localhost";
            $this->db_name = "";
            $this->username = "";
            $this->password = "";
        }
    }

    public function dbConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=". $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
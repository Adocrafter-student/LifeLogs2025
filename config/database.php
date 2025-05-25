<?php
class Database {
    private $host = "localhost";
    private $db_name = "lifelogs";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->connectionection = null;

        try {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            $this->connection->set_charset("utf8");
        } catch(Exception $e) {
            echo "Connection error: " . $e->getMessage();
        }

        return $this->connection;
    }
}
?> 
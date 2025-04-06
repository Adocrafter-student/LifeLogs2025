<?php

class BaseDao {
    protected $conn;
    protected $table_name;

    /**
     * Constructor that sets up database connection
     */
    public function __construct($table_name) {
        $this->table_name = $table_name;
        $servername = "localhost";
        $username = "root";
        $password = "";
        $schema = "lifelogs";
        
        try {
            $this->conn = new PDO("mysql:host=$servername;dbname=$schema", $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    /**
     * Get all records from a table
     */
    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single record by ID
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new record
     */
    public function add($params) {
        $columns = implode(',', array_keys($params));
        $values = implode(',', array_fill(0, count($params), '?'));
        
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " (" . $columns . ") VALUES (" . $values . ")");
        $stmt->execute(array_values($params));
        return $this->conn->lastInsertId();
    }

    /**
     * Update a record
     */
    public function update($id, $params) {
        $set = implode(',', array_map(function($key) {
            return "$key = :$key";
        }, array_keys($params)));
        
        $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET " . $set . " WHERE id = :id");
        $stmt->bindParam(':id', $id);
        
        foreach ($params as $key => $value) {
            $stmt->bindParam(':' . $key, $value);
        }
        
        return $stmt->execute();
    }

    /**
     * Delete a record
     */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?> 
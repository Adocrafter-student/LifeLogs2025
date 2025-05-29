<?php
// require_once __DIR__ . "/../Config.php"; // Uključeno u index.php ili autoloader

class BaseDao {
    protected $connection;
    protected $table_name;

    /**
     * Constructor that sets up database connection
     */
    public function __construct($table_name) {
        $this->table_name = $table_name;
        try {
            $this->connection = new PDO(
                "mysql:host=" . Config::DB_HOST() . ";dbname=" . Config::DB_NAME() . ";port=" . Config::DB_PORT() . ";charset=utf8mb4",
                Config::DB_USER(),
                Config::DB_PASSWORD(),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error_log("BaseDao PDO Connection Error: " . $e->getMessage());
            throw $e;
        }
    }

    protected function query($query, $params = []) {
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function query_unique($query, $params = []) {
        $results = $this->query($query, $params);
        return reset($results);
    }

    /**
     * Get all records from a table
     */
    public function getAll() {
        $stmt = $this->connection->prepare("SELECT * FROM `" . $this->table_name . "`");
        $stmt->execute();
        return $stmt->fetchAll(); // Default fetch mode je PDO::FETCH_ASSOC
    }

    /**
     * Get a single record by ID
     */
    public function getById($id) {
        $stmt = $this->connection->prepare("SELECT * FROM `" . $this->table_name . "` WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(); // Default fetch mode
    }

    /**
     * Create a new record
     */
    public function add($entity) {
        $query = "INSERT INTO `" . $this->table_name . "` (";
        foreach (array_keys($entity) as $column) { // Koristimo array_keys da ne zavisimo od vrijednosti
            $query .= "`" . $column . "`" . ', '; // Dodajemo backticks oko imena kolona
        }
        $query = substr($query, 0, -2); // Ukloni posljednji zarez i razmak
        $query .= ") VALUES (";
        foreach (array_keys($entity) as $column) {
            $query .= ":" . $column . ', ';
        }
        $query = substr($query, 0, -2); // Ukloni posljednji zarez i razmak
        $query .= ")";

        $stmt = $this->connection->prepare($query);
        $stmt->execute($entity); // PDO će mapirati :column na $entity[column]
        $entity['id'] = $this->connection->lastInsertId();
        return $entity; // Vraća originalni entitet dopunjen sa ID-jem
    }

    /**
     * Update a record
     */
    public function update($entity, $id, $id_column = "id") { // Njen update ima $id_column
        $query = "UPDATE `" . $this->table_name . "` SET ";
        foreach (array_keys($entity) as $column) {
            $query .= "`" . $column . "`" . "=:" . $column . ", ";
        }
        $query = substr($query, 0, -2);
        $query .= " WHERE `" . $id_column . "` = :" . $id_column . "_param"; // Koristimo drugačiji placeholder za ID u WHERE
        
        $stmt = $this->connection->prepare($query);
        $entity_for_execute = $entity; // Kopiraj da ne mijenjamo originalni $entity ako se koristi kasnije
        $entity_for_execute[$id_column . '_param'] = $id; // Dodaj ID za WHERE klauzulu
        $stmt->execute($entity_for_execute);
        return $entity; // Vraća originalni entitet (možda bi trebalo vratiti broj afektiranih redova ili potvrdu)
    }

    /**
     * Delete a record
     */
    public function delete($id) {
        $stmt = $this->connection->prepare("DELETE FROM `" . $this->table_name . "` WHERE id = :id");
        // $stmt->bindValue(':id', $id); // bindParam je generalno sigurniji ako $id može da se mijenja
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?> 
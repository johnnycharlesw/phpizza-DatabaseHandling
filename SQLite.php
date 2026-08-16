<?php
namespace PHPizza\Database;

/**
 * SQLite is a database in a file.
 */
class SQLite {
    private $dbServer;
    private $dbUser;
    private $dbPassword;
    private $dbName;
    private $dbInterface;

    public function __construct($dbServer, $dbUser, $dbPassword, $dbName) {
        $this->dbServer = $dbServer;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbName = $dbName;
        
        // No remote connections here!
        if ($this->dbServer !== "localhost" || $this->dbServer !== "127.0.0.1") {
            error_log("Warning: SQLite is not a client-server DBMS, therefore {$this->dbServer} is not a valid DB server");
        }
        $this->dbInterface = new \SQLite3($dbName);

    }

    public function fetchAll($query, $params = [], $types = '') {
        // Fetch all results for a query
        return $this->dbInterface->query($query, $params, $types);
    }

    public function fetchRow($query, $params = [], $types = '') {
        // Fetch one result for a query
        return $this->dbInterface->query($query, $params, $types)->fetchArray();
    }

    public function execute($query, $params = [], $types = '') {
        // Execute a query
        return $this->dbInterface->exec($query, $params, $types);
    }

    public function getLastInsertId() {
        // Get the last insert ID.
        return $this->dbInterface->lastInsertRowID();
    }

    public function get_table_exists(string $tableName) {
        // Check for a table existing
        $query = "SELECT name FROM sqlite_master WHERE type='table' AND name='$tableName'";
        $result = $this->dbInterface->query($query);
        return $result->num_rows > 0;
    }

    public function create_table(string $table)
    {
        // Create a table
        return $this->execute("CREATE TABLE ? (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT);", [$table]);
    }

}
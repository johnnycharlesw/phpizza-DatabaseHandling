<?php
namespace PHPizza\Database;

final class Database {

    private $dbServer;
    private $dbUser;
    private $dbPassword;
    private $dbName;
    private $dbType;
    private $dbDriver;

    # type-to-class map
    private array $dbMap = [
        'mariadb' => MariaDB::class,
        'mysql' => MySQL::class,
        'sqlite'  => SQLite::class,
        'postgres' => PostgreSQL::class,
        'metasql' => MyRocks::class,
        'myrocks' => MyRocks::class,
        'percona' => PerconaServer::class,
    ];

    public function __construct($dbServer, $dbUser, $dbPassword, $dbName, $dbType){
        $this->dbServer = $dbServer;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbName = $dbName;
        $key = strtolower(trim($dbType));
        if (!isset($this->dbMap[$key])) {
            if (!$this->create_sqlite_fallback($dbServer, $dbUser, $dbPassword, $dbName, $dbType)) {
                throw new \InvalidArgumentException("Unsupported database type: {$dbType}");
            } else {
                global $dbServer, $dbUser, $dbPassword, $dbName, $dbType;
                $this->dbServer = $dbServer;
                $this->dbUser = $dbUser;
                $this->dbPassword = $dbPassword;
                $this->dbName = $dbName;
                $key = $dbType;
            }
            
        }
        $this->dbType = $key;
        $driverClass = $this->dbMap[$key];
        $this->dbDriver = new $driverClass($this->dbServer, $this->dbUser, $this->dbPassword, $this->dbName);
    }

    public function create_sqlite_fallback($dbServer, $dbUser, $dbPassword, $dbName, $dbType): bool {
        if (empty($dbServer) && empty($dbUser) && empty($dbPassword) && empty($dbName) && empty($dbType)) {
            global $dbServer, $dbUser, $dbPassword, $dbName, $dbType;
            $dbServer = "localhost";
            $dbUser="www-data";
            $dbPassword="phpizza";
            $dbName="private/sqlite3/db.sqlite3";
            $dbType="sqlite";
            return true;
        }
        return false;
    }

    public function fetchAll($query, $params = [], $types = ''){
        return $this->dbDriver->fetchAll($query,$params,$types);
    }

    public function fetchRow($query, $params = [], $types = ''){
        return $this->dbDriver->fetchRow($query,$params,$types);
    }

    public function execute($query, $params = [], $types = ''){
        return $this->dbDriver->execute($query,$params,$types);
    }
    
    public function get_table_exists(string $tableName){
        return $this->dbDriver->get_table_exists($tableName);
    }

    public function getLastInsertId(){
        return $this->dbDriver->getLastInsertId();
    }

    public function create_table(string $table){
        return $this->dbDriver->create_table($table);
    }
}
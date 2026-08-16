<?php
namespace PHPizza\Database;

/**
 * So, this is the database class.
 * Think of the internal database of PHPizza like a giant Microsoft Excel/LibreOffice Calc spreadsheet.
 * You have different sheets in the spreadsheet file, like `users`, `pages`, and `gandi_api_keychain`.
 * When you sign up, a row gets added to `users`. When you create a page, a row gets added to `pages`. And so on and so forth.
 * Maybe that's a little bit simplified, but you get the idea.
 */
final class Database {

    # Database credentials
    private $dbServer;
    private $dbUser;
    private $dbPassword;
    private $dbName;
    private $dbType;

    # Database (non-kernel) driver
    private $dbDriver;

    # Database to driver map
    private array $dbMap = [
        'mariadb' => MariaDB::class,
        'mysql' => MySQL::class,
        'sqlite'  => SQLite::class,
        'postgres' => PostgreSQL::class,
        'metasql' => MyRocks::class,
        'myrocks' => MyRocks::class,
        'percona' => PerconaServer::class,
    ];

    # Connect to the server
    public function __construct($dbServer, $dbUser, $dbPassword, $dbName, $dbType){
        # store main credentials and initialize database driver
        $this->dbServer = $dbServer;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbName = $dbName;
        $key = strtolower(trim($dbType));
        if (!isset($this->dbMap[$key])) {
            // Try to create the SQLite fallback database if an invalid dbType was provided
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
        // Are there no database credentials?
        if (empty($dbServer) && empty($dbUser) && empty($dbPassword) && empty($dbName) && empty($dbType)) {
            // If so, create a SQLite database to install PHPizza into
            global $dbServer, $dbUser, $dbPassword, $dbName, $dbType;
            // SQLite is a file
            $dbServer = "localhost";
            // SQLite runs as `www-data` on Debian GNU/Linux and derivatives
            $dbUser="www-data";
            // No password for SQLite, but I guess a plug makes sense
            $dbPassword="phpizza";
            // Store it in the private folder so that it only gets shared if an admin posts or torrents it
            $dbName="private/sqlite3/db.sqlite3";
            // It is SQLite
            $dbType="sqlite";
            return true;
        }
        // Yes, there are.
        return false;
    }

    // The methods below depend on the database
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
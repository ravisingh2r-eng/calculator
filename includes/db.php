<?php
/**
 * Database Connection Class
 *
 * Yeh file PDO connection handle karti hai.
 * Singleton pattern use kar rahe hain taaki ek hi connection rahe.
 *
 * Usage:
 *   $db = Database::getInstance();
 *   $pdo = $db->getConnection();
 *   $stmt = $pdo->prepare("SELECT * FROM calculators");
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    die('Direct access not allowed');
}

/**
 * Database Connection Class (Singleton Pattern)
 *
 * Singleton isliye use kar rahe hain kyunki:
 * - Multiple connections se server load badh jata hai
 * - Shared hosting par connection limit hoti hai
 * - Ek connection reuse karna efficient hai
 */
class Database
{
    /**
     * Single instance of this class
     * @var Database|null
     */
    private static $instance = null;

    /**
     * PDO connection object
     * @var PDO|null
     */
    private $connection = null;

    /**
     * Database configuration array
     * @var array
     */
    private $config = [];

    /**
     * Private Constructor (Singleton pattern)
     *
     * Bahar se new Database() nahi kar sakte
     * getInstance() use karna padega
     */
    private function __construct()
    {
        // Load database configuration
        $this->config = require BASE_PATH . '/config/database.php';

        // Establish connection
        $this->connect();
    }

    /**
     * Prevent cloning of instance (Singleton pattern)
     */
    private function __clone() {}

    /**
     * Prevent unserialization (Singleton pattern)
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Get Database Instance (Singleton)
     *
     * Yeh method ek hi instance return karta hai
     * Pehli baar call par new instance banta hai
     * Baad me same instance return hota hai
     *
     * @return Database
     *
     * Usage:
     *   $db = Database::getInstance();
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Establish Database Connection
     *
     * PDO connection banata hai with error handling
     * Connection fail hone par exception throw hoti hai
     *
     * @throws PDOException
     */
    private function connect(): void
    {
        // Agar already connected hai toh skip karo
        if ($this->connection !== null) {
            return;
        }

        try {
            // DSN (Data Source Name) build karo
            // Format: mysql:host=localhost;port=3306;dbname=mydb;charset=utf8mb4
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            // PDO connection create karo
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );

            // Set SQL mode for strict data handling
            $this->connection->exec("SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");

        } catch (PDOException $e) {
            // Connection error handling
            // Production me detailed error mat dikhao (security risk)

            // Log the actual error (file me)
            $this->logError($e->getMessage());

            // User ko generic message dikhao
            // Development me detailed error dekhne ke liye neeche wali line uncomment karo
            // throw new PDOException("Database connection failed: " . $e->getMessage());

            throw new PDOException("Database connection failed. Please try again later.");
        }
    }

    /**
     * Get PDO Connection Object
     *
     * Direct PDO object return karta hai queries ke liye
     *
     * @return PDO
     *
     * Usage:
     *   $pdo = Database::getInstance()->getConnection();
     *   $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
     *   $stmt->execute([1]);
     */
    public function getConnection(): PDO
    {
        // Reconnect agar connection lost ho gaya
        if ($this->connection === null) {
            $this->connect();
        }

        return $this->connection;
    }

    /**
     * Execute a Simple Query
     *
     * Shortcut method for simple SELECT queries
     * Prepared statements use karta hai (SQL injection safe)
     *
     * @param string $sql    SQL query with placeholders
     * @param array  $params Parameters to bind
     * @return array         Fetched results
     *
     * Usage:
     *   $users = $db->query("SELECT * FROM users WHERE status = ?", ['active']);
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute INSERT/UPDATE/DELETE Query
     *
     * Returns number of affected rows
     *
     * @param string $sql    SQL query with placeholders
     * @param array  $params Parameters to bind
     * @return int           Number of affected rows
     *
     * Usage:
     *   $affected = $db->execute("UPDATE users SET status = ? WHERE id = ?", ['inactive', 5]);
     */
    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();

        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw $e;
        }
    }

    /**
     * Get Last Inserted ID
     *
     * INSERT ke baad auto-increment ID return karta hai
     *
     * @return string Last insert ID
     *
     * Usage:
     *   $db->execute("INSERT INTO users (name) VALUES (?)", ['John']);
     *   $newId = $db->lastInsertId();
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Begin Transaction
     *
     * Multiple queries ko atomic banane ke liye
     *
     * Usage:
     *   $db->beginTransaction();
     *   try {
     *       $db->execute(...);
     *       $db->execute(...);
     *       $db->commit();
     *   } catch (Exception $e) {
     *       $db->rollback();
     *   }
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit Transaction
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback Transaction
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Log Database Errors
     *
     * Errors ko file me log karta hai
     * Production me yeh important hai debugging ke liye
     *
     * @param string $message Error message
     */
    private function logError(string $message): void
    {
        $logFile = BASE_PATH . '/storage/logs/database.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] Database Error: {$message}" . PHP_EOL;

        // Log directory exists check karo
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Append to log file
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Close Database Connection
     *
     * Connection explicitly close karne ke liye
     * Usually zaroorat nahi padti (PHP auto close karta hai)
     */
    public function close(): void
    {
        $this->connection = null;
    }

    /**
     * Check if Connected
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connection !== null;
    }
}

/**
 * Helper Function: Get Database Instance
 *
 * Shortcut function for quick access
 *
 * Usage:
 *   $db = db();
 *   $users = $db->query("SELECT * FROM users");
 *
 * @return Database
 */
function db(): Database
{
    return Database::getInstance();
}

// ============================================================================
// GLOBAL HELPER FUNCTIONS
// ============================================================================
// Yeh functions direct use ho sakte hain bina Database class ke
// Sab me prepared statements aur exception handling hai

/**
 * Get PDO Connection Instance
 *
 * Direct PDO object return karta hai
 * Use for complex queries or when you need raw PDO
 *
 * @return PDO
 *
 * Usage:
 *   $pdo = get_db();
 *   $stmt = $pdo->prepare("SELECT * FROM users");
 */
function get_db(): PDO
{
    return Database::getInstance()->getConnection();
}

/**
 * Fetch All Rows
 *
 * SELECT query execute karta hai aur saari rows return karta hai
 * Empty array return hota hai agar koi result nahi mila
 *
 * @param string $sql    SQL query with placeholders (? or :name)
 * @param array  $params Parameters to bind
 * @return array         Array of associative arrays
 *
 * Usage:
 *   // With ? placeholders
 *   $users = db_fetch_all("SELECT * FROM users WHERE status = ?", ['active']);
 *
 *   // With named placeholders
 *   $users = db_fetch_all(
 *       "SELECT * FROM users WHERE role = :role AND status = :status",
 *       ['role' => 'admin', 'status' => 'active']
 *   );
 *
 *   // Loop through results
 *   foreach ($users as $user) {
 *       echo $user['name'];
 *   }
 */
function db_fetch_all(string $sql, array $params = []): array
{
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // Log error
        db_log_error($e->getMessage(), $sql, $params);
        throw $e;
    }
}

/**
 * Fetch One Row
 *
 * SELECT query execute karta hai aur single row return karta hai
 * False return hota hai agar koi result nahi mila
 *
 * @param string $sql    SQL query with placeholders
 * @param array  $params Parameters to bind
 * @return array|false   Associative array or false
 *
 * Usage:
 *   // Get single user
 *   $user = db_fetch_one("SELECT * FROM users WHERE id = ?", [5]);
 *   if ($user) {
 *       echo $user['name'];
 *   }
 *
 *   // Get calculator by slug
 *   $calc = db_fetch_one(
 *       "SELECT * FROM calculators WHERE slug = ? AND is_active = 1",
 *       ['bmi']
 *   );
 */
function db_fetch_one(string $sql, array $params = [])
{
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        db_log_error($e->getMessage(), $sql, $params);
        throw $e;
    }
}

/**
 * Fetch Single Value (Scalar)
 *
 * Single value return karta hai (first column of first row)
 * Useful for COUNT, SUM, MAX, etc.
 *
 * @param string $sql    SQL query with placeholders
 * @param array  $params Parameters to bind
 * @return mixed         Single value or false
 *
 * Usage:
 *   // Count users
 *   $count = db_fetch_value("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
 *
 *   // Get single field
 *   $name = db_fetch_value("SELECT name FROM users WHERE id = ?", [5]);
 *
 *   // Check if exists
 *   $exists = db_fetch_value("SELECT 1 FROM users WHERE email = ?", ['test@example.com']);
 */
function db_fetch_value(string $sql, array $params = [])
{
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();

    } catch (PDOException $e) {
        db_log_error($e->getMessage(), $sql, $params);
        throw $e;
    }
}

/**
 * Fetch Column
 *
 * Single column ki saari values return karta hai as array
 *
 * @param string $sql    SQL query with placeholders
 * @param array  $params Parameters to bind
 * @return array         Array of values
 *
 * Usage:
 *   // Get all user IDs
 *   $ids = db_fetch_column("SELECT id FROM users WHERE status = ?", ['active']);
 *   // Returns: [1, 2, 3, 5, 8]
 *
 *   // Get all slugs
 *   $slugs = db_fetch_column("SELECT slug FROM calculators WHERE is_active = 1");
 */
function db_fetch_column(string $sql, array $params = []): array
{
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);

    } catch (PDOException $e) {
        db_log_error($e->getMessage(), $sql, $params);
        throw $e;
    }
}

/**
 * Execute Query (INSERT/UPDATE/DELETE)
 *
 * Data modification queries ke liye
 * Affected rows count return karta hai
 *
 * @param string $sql    SQL query with placeholders
 * @param array  $params Parameters to bind
 * @return int           Number of affected rows
 *
 * Usage:
 *   // INSERT
 *   $affected = db_execute(
 *       "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)",
 *       ['John', 'john@example.com', password_hash('secret', PASSWORD_DEFAULT)]
 *   );
 *
 *   // UPDATE
 *   $affected = db_execute(
 *       "UPDATE users SET status = ? WHERE id = ?",
 *       ['inactive', 5]
 *   );
 *
 *   // DELETE
 *   $affected = db_execute("DELETE FROM users WHERE id = ?", [10]);
 *
 *   // Check if successful
 *   if ($affected > 0) {
 *       echo "Operation successful";
 *   }
 */
function db_execute(string $sql, array $params = []): int
{
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();

    } catch (PDOException $e) {
        db_log_error($e->getMessage(), $sql, $params);
        throw $e;
    }
}

/**
 * Insert Row and Get ID
 *
 * INSERT karta hai aur naya auto-increment ID return karta hai
 *
 * @param string $sql    INSERT query with placeholders
 * @param array  $params Parameters to bind
 * @return int           New record ID
 *
 * Usage:
 *   $newId = db_insert(
 *       "INSERT INTO users (name, email) VALUES (?, ?)",
 *       ['John', 'john@example.com']
 *   );
 *   echo "New user ID: " . $newId;
 */
function db_insert(string $sql, array $params = []): int
{
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $pdo->lastInsertId();

    } catch (PDOException $e) {
        db_log_error($e->getMessage(), $sql, $params);
        throw $e;
    }
}

/**
 * Insert Row from Array
 *
 * Associative array se directly INSERT karta hai
 * Column names array keys se aate hain
 *
 * @param string $table  Table name
 * @param array  $data   Associative array [column => value]
 * @return int           New record ID
 *
 * Usage:
 *   $newId = db_insert_array('users', [
 *       'name' => 'John Doe',
 *       'email' => 'john@example.com',
 *       'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
 *       'role' => 'editor'
 *   ]);
 */
function db_insert_array(string $table, array $data): int
{
    // Build column and placeholder lists
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');

    $sql = sprintf(
        "INSERT INTO `%s` (`%s`) VALUES (%s)",
        $table,
        implode('`, `', $columns),
        implode(', ', $placeholders)
    );

    return db_insert($sql, array_values($data));
}

/**
 * Update Row from Array
 *
 * Associative array se UPDATE karta hai
 *
 * @param string $table      Table name
 * @param array  $data       Associative array [column => value]
 * @param string $where      WHERE clause (without WHERE keyword)
 * @param array  $whereParams Parameters for WHERE clause
 * @return int               Number of affected rows
 *
 * Usage:
 *   $affected = db_update_array('users',
 *       ['name' => 'John Updated', 'status' => 'active'],
 *       'id = ?',
 *       [5]
 *   );
 */
function db_update_array(string $table, array $data, string $where, array $whereParams = []): int
{
    // Build SET clause
    $setParts = [];
    foreach (array_keys($data) as $column) {
        $setParts[] = "`{$column}` = ?";
    }

    $sql = sprintf(
        "UPDATE `%s` SET %s WHERE %s",
        $table,
        implode(', ', $setParts),
        $where
    );

    // Merge data values with where params
    $params = array_merge(array_values($data), $whereParams);

    return db_execute($sql, $params);
}

/**
 * Check if Record Exists
 *
 * Quickly check if a record exists
 *
 * @param string $table  Table name
 * @param string $where  WHERE clause
 * @param array  $params Parameters
 * @return bool
 *
 * Usage:
 *   // Check if email exists
 *   if (db_exists('users', 'email = ?', ['john@example.com'])) {
 *       echo "Email already taken";
 *   }
 *
 *   // Check if slug exists
 *   if (db_exists('calculators', 'slug = ? AND id != ?', ['bmi', 5])) {
 *       echo "Slug already in use";
 *   }
 */
function db_exists(string $table, string $where, array $params = []): bool
{
    $sql = "SELECT 1 FROM `{$table}` WHERE {$where} LIMIT 1";
    return db_fetch_value($sql, $params) !== false;
}

/**
 * Count Records
 *
 * Count rows matching condition
 *
 * @param string $table  Table name
 * @param string $where  WHERE clause (optional)
 * @param array  $params Parameters (optional)
 * @return int           Count
 *
 * Usage:
 *   // Count all users
 *   $total = db_count('users');
 *
 *   // Count active calculators
 *   $active = db_count('calculators', 'is_active = ?', [1]);
 *
 *   // Count by category
 *   $count = db_count('calculators', 'category_id = ?', [2]);
 */
function db_count(string $table, string $where = '1=1', array $params = []): int
{
    $sql = "SELECT COUNT(*) FROM `{$table}` WHERE {$where}";
    return (int) db_fetch_value($sql, $params);
}

/**
 * Begin Transaction
 *
 * Start database transaction
 *
 * @return bool
 *
 * Usage:
 *   db_begin();
 *   try {
 *       db_execute(...);
 *       db_execute(...);
 *       db_commit();
 *   } catch (Exception $e) {
 *       db_rollback();
 *       throw $e;
 *   }
 */
function db_begin(): bool
{
    return get_db()->beginTransaction();
}

/**
 * Commit Transaction
 *
 * @return bool
 */
function db_commit(): bool
{
    return get_db()->commit();
}

/**
 * Rollback Transaction
 *
 * @return bool
 */
function db_rollback(): bool
{
    return get_db()->rollBack();
}

/**
 * Log Database Error
 *
 * Internal function to log errors with context
 *
 * @param string $message Error message
 * @param string $sql     SQL query
 * @param array  $params  Query parameters
 */
function db_log_error(string $message, string $sql = '', array $params = []): void
{
    $logFile = BASE_PATH . '/storage/logs/database.log';
    $timestamp = date('Y-m-d H:i:s');

    $logMessage = "[{$timestamp}] Database Error: {$message}";

    if ($sql) {
        $logMessage .= "\nQuery: {$sql}";
    }

    if (!empty($params)) {
        $logMessage .= "\nParams: " . json_encode($params);
    }

    $logMessage .= "\n" . str_repeat('-', 80) . "\n";

    // Ensure log directory exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // Append to log file
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * USAGE EXAMPLES:
 *
 * // Get instance
 * $db = Database::getInstance();
 * // OR
 * $db = db();
 *
 * // Simple SELECT
 * $calculators = $db->query("SELECT * FROM calculators WHERE status = ?", ['active']);
 *
 * // INSERT
 * $db->execute(
 *     "INSERT INTO calculators (name, slug, category_id) VALUES (?, ?, ?)",
 *     ['BMI Calculator', 'bmi', 1]
 * );
 * $newId = $db->lastInsertId();
 *
 * // UPDATE
 * $affected = $db->execute(
 *     "UPDATE calculators SET views = views + 1 WHERE id = ?",
 *     [5]
 * );
 *
 * // DELETE
 * $db->execute("DELETE FROM calculators WHERE id = ?", [10]);
 *
 * // Transaction
 * $db->beginTransaction();
 * try {
 *     $db->execute(...);
 *     $db->execute(...);
 *     $db->commit();
 * } catch (Exception $e) {
 *     $db->rollback();
 *     throw $e;
 * }
 *
 * // Direct PDO access (for complex queries)
 * $pdo = $db->getConnection();
 * $stmt = $pdo->prepare("SELECT * FROM calculators WHERE name LIKE ?");
 * $stmt->execute(['%loan%']);
 * $results = $stmt->fetchAll();
 */

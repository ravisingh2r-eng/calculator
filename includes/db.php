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

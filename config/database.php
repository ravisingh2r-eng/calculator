<?php
/**
 * Database Configuration File
 *
 * Yeh file database credentials store karti hai.
 * IMPORTANT: Is file ko kabhi git me commit mat karo!
 * Production me .gitignore me add karo.
 *
 * Hostinger Shared Hosting Settings:
 * - Host usually 'localhost' hota hai
 * - Database name aur user hPanel se milega
 * - Password wahi jo database create karte waqt set kiya
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Prevent direct access to this file
if (!defined('BASE_PATH')) {
    // Agar directly access kare toh error
    die('Direct access not allowed');
}

/**
 * Database Configuration Array
 *
 * Hostinger hPanel se yeh details milenge:
 * 1. Login to hPanel
 * 2. Go to Databases > MySQL Databases
 * 3. Create database & user
 * 4. Note down the credentials
 */
return [

    // Database Host
    // Hostinger par usually 'localhost' hota hai
    // Agar remote database hai toh IP/hostname dalo
    'host' => 'localhost',

    // Database Port
    // MySQL default port 3306 hai
    // Hostinger par usually change nahi karna padta
    'port' => 3306,

    // Database Name
    // Format usually: username_dbname
    // Example: u123456789_calculator
    'database' => 'YOUR_DATABASE_NAME',

    // Database Username
    // Format usually: username_dbuser
    // Example: u123456789_admin
    'username' => 'YOUR_DATABASE_USERNAME',

    // Database Password
    // Strong password use karo (mix of letters, numbers, symbols)
    // Minimum 12 characters recommended
    'password' => 'YOUR_DATABASE_PASSWORD',

    // Character Set
    // utf8mb4 best hai for emoji support bhi
    'charset' => 'utf8mb4',

    // Collation
    // utf8mb4_unicode_ci recommended for proper sorting
    'collation' => 'utf8mb4_unicode_ci',

    // PDO Options
    // Yeh settings PDO connection ke liye hain
    'options' => [
        // Error mode: Exception throw karo errors par
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        // Default fetch mode: Associative array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        // Emulated prepares OFF (better security)
        PDO::ATTR_EMULATE_PREPARES => false,

        // Persistent connection OFF (shared hosting ke liye better)
        PDO::ATTR_PERSISTENT => false,
    ],

];

/**
 * SECURITY NOTES:
 *
 * 1. Is file ko web se accessible mat rakho
 *    - .htaccess me block karo
 *    - Ya config folder ko document root se bahar rakho
 *
 * 2. Production credentials development se alag rakho
 *    - database.local.php for development
 *    - database.php for production
 *
 * 3. File permissions check karo
 *    - This file: 644 (rw-r--r--)
 *    - Config folder: 755 (rwxr-xr-x)
 *
 * 4. Backup credentials securely store karo
 *    - Password manager use karo
 *    - Plain text me mat rakho
 */

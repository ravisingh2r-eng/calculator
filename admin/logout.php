<?php
/**
 * Admin Logout
 *
 * Destroys session and redirects to login page
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include auth
require_once BASE_PATH . '/admin/auth.php';

// Logout user
logout();

// Redirect to login page
redirect('/admin/');

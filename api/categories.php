<?php
/**
 * Categories API Endpoint
 *
 * Returns list of active categories for navigation
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Error reporting (disable in production)
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON response header
header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Include database
require_once BASE_PATH . '/includes/db.php';

// Get limit parameter (default 10)
$limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 20) : 10;

try {
    $pdo = get_db_connection();

    // Get active categories ordered by sort_order
    $stmt = $pdo->prepare("
        SELECT
            id,
            name,
            slug,
            icon,
            short_desc
        FROM categories
        WHERE is_active = 1
        ORDER BY sort_order ASC, name ASC
        LIMIT ?
    ");

    $stmt->execute([$limit]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return results
    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'count' => count($categories)
    ]);

} catch (PDOException $e) {
    error_log('Categories API error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

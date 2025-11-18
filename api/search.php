<?php
/**
 * Search API Endpoint
 *
 * Searches calculators by name, category, or keywords
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

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Validate query
if (strlen($query) < 2) {
    echo json_encode([
        'success' => true,
        'results' => [],
        'count' => 0
    ]);
    exit;
}

// Include database
require_once BASE_PATH . '/includes/db.php';

try {
    $pdo = get_db_connection();

    // Prepare search term
    $searchTerm = '%' . $query . '%';

    // Search calculators
    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.name,
            c.slug,
            c.short_desc,
            cat.name AS category_name,
            cat.icon AS category_icon
        FROM calculators c
        LEFT JOIN categories cat ON c.category_id = cat.id
        WHERE c.is_active = 1
        AND (
            c.name LIKE ?
            OR c.short_desc LIKE ?
            OR c.primary_keyword LIKE ?
            OR c.secondary_keywords LIKE ?
            OR cat.name LIKE ?
        )
        ORDER BY
            CASE
                WHEN c.name LIKE ? THEN 1
                WHEN c.primary_keyword LIKE ? THEN 2
                WHEN cat.name LIKE ? THEN 3
                ELSE 4
            END,
            c.name ASC
        LIMIT 10
    ");

    $stmt->execute([
        $searchTerm, // name
        $searchTerm, // short_desc
        $searchTerm, // primary_keyword
        $searchTerm, // secondary_keywords
        $searchTerm, // category name
        $searchTerm, // ORDER BY name
        $searchTerm, // ORDER BY primary_keyword
        $searchTerm  // ORDER BY category
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format results
    $formatted = [];
    foreach ($results as $row) {
        $formatted[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'short_desc' => $row['short_desc'],
            'category' => $row['category_name'],
            'icon' => $row['category_icon'] ?: '🧮'
        ];
    }

    // Return results
    echo json_encode([
        'success' => true,
        'results' => $formatted,
        'count' => count($formatted),
        'query' => $query
    ]);

} catch (PDOException $e) {
    // Log error
    error_log('Search error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

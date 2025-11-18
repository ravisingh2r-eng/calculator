<?php
/**
 * Log Event API Endpoint
 *
 * Receives analytics events from frontend and stores them in database
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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!$data || !isset($data['event_type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Include database
require_once BASE_PATH . '/includes/db.php';

// Extract data
$calculatorId = isset($data['calculator_id']) ? (int)$data['calculator_id'] : 0;
$eventType = trim($data['event_type']);
$eventData = isset($data['data']) ? $data['data'] : [];

// Validate event type
$validEventTypes = ['view', 'calculate', 'search', 'click', 'share'];
if (!in_array($eventType, $validEventTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid event type']);
    exit;
}

// Get client info
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$referrer = $_SERVER['HTTP_REFERER'] ?? '';

// Anonymize IP (keep first 3 octets for IPv4)
if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    $parts = explode('.', $ipAddress);
    $ipAddress = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.0';
}

try {
    $pdo = get_db_connection();

    // Check if analytics_events table exists, create if not
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'analytics_events'");
    if ($tableCheck->rowCount() === 0) {
        // Create table
        $pdo->exec("
            CREATE TABLE analytics_events (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                calculator_id INT UNSIGNED DEFAULT 0,
                event_type VARCHAR(50) NOT NULL,
                event_data JSON,
                ip_address VARCHAR(45),
                user_agent VARCHAR(500),
                referrer VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_calculator (calculator_id),
                INDEX idx_event_type (event_type),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // Insert event
    $stmt = $pdo->prepare("
        INSERT INTO analytics_events
        (calculator_id, event_type, event_data, ip_address, user_agent, referrer)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $calculatorId,
        $eventType,
        json_encode($eventData),
        $ipAddress,
        substr($userAgent, 0, 500),
        substr($referrer, 0, 500)
    ]);

    // Return success
    echo json_encode([
        'success' => true,
        'event_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    // Log error (in production, log to file instead)
    error_log('Analytics error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

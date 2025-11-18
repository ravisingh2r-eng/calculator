<?php
/**
 * Admin Dashboard
 *
 * Main admin panel landing page
 * Shows overview stats and quick actions
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Error reporting (development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Include auth and require login
require_once BASE_PATH . '/admin/auth.php';
require_login();

// Get current user
$user = current_user();

// Include models for stats
require_once BASE_PATH . '/admin/models/categories.php';
require_once BASE_PATH . '/admin/models/calculators.php';

// Get stats (will work after database is connected)
// For now, using dummy data
$stats = [
    'total_calculators' => 6,
    'total_categories' => 6,
    'views_7_days' => 1284
];

// Try to get real stats if database is connected
// try {
//     $stats['total_calculators'] = get_calculator_count(null, false);
//     $stats['total_categories'] = db_count('calculator_categories');
//     $stats['views_7_days'] = db_fetch_value(
//         "SELECT COALESCE(SUM(view_count), 0) FROM calculator_stats
//          WHERE recorded_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
//     );
// } catch (Exception $e) {
//     // Database not connected yet
// }

// Page title for header
$pageTitle = 'Dashboard';

// Include admin header (contains DOCTYPE, head, topbar, sidebar)
include BASE_PATH . '/admin/includes/header.php';
?>

            <!-- Page Header -->
            <div class="admin-page-header">
                <h1 class="admin-page-title">Dashboard</h1>
                <p class="admin-page-subtitle">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
            </div>

            <!-- Stats Cards -->
            <div class="admin-stats">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon blue">📁</div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-value"><?php echo $stats['total_categories']; ?></div>
                        <div class="admin-stat-label">Total Categories</div>
                    </div>
                </div>

                <div class="admin-stat-card">
                    <div class="admin-stat-icon green">🧮</div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-value"><?php echo $stats['total_calculators']; ?></div>
                        <div class="admin-stat-label">Total Calculators</div>
                    </div>
                </div>

                <div class="admin-stat-card">
                    <div class="admin-stat-icon purple">👁️</div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-value"><?php echo number_format($stats['views_7_days']); ?></div>
                        <div class="admin-stat-label">Views (Last 7 Days)</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <h2 style="margin-bottom: var(--space-md);">Quick Actions</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-md);">
                <a href="/admin/calculators/add.php" style="background: var(--card); border-radius: var(--radius-lg); padding: var(--space-lg); box-shadow: var(--shadow-soft); text-decoration: none; color: inherit; transition: all var(--transition-normal); display: block;">
                    <div style="font-size: 1.5rem; margin-bottom: var(--space-sm);">➕</div>
                    <div style="font-weight: 600; margin-bottom: var(--space-xs);">Add Calculator</div>
                    <p style="font-size: var(--text-sm); color: var(--muted); margin: 0;">Create a new calculator</p>
                </a>

                <a href="/admin/calculators/" style="background: var(--card); border-radius: var(--radius-lg); padding: var(--space-lg); box-shadow: var(--shadow-soft); text-decoration: none; color: inherit; transition: all var(--transition-normal); display: block;">
                    <div style="font-size: 1.5rem; margin-bottom: var(--space-sm);">📋</div>
                    <div style="font-weight: 600; margin-bottom: var(--space-xs);">Manage Calculators</div>
                    <p style="font-size: var(--text-sm); color: var(--muted); margin: 0;">View and edit all</p>
                </a>

                <a href="/admin/categories/" style="background: var(--card); border-radius: var(--radius-lg); padding: var(--space-lg); box-shadow: var(--shadow-soft); text-decoration: none; color: inherit; transition: all var(--transition-normal); display: block;">
                    <div style="font-size: 1.5rem; margin-bottom: var(--space-sm);">📁</div>
                    <div style="font-weight: 600; margin-bottom: var(--space-xs);">Categories</div>
                    <p style="font-size: var(--text-sm); color: var(--muted); margin: 0;">Manage categories</p>
                </a>

                <a href="/admin/analytics/" style="background: var(--card); border-radius: var(--radius-lg); padding: var(--space-lg); box-shadow: var(--shadow-soft); text-decoration: none; color: inherit; transition: all var(--transition-normal); display: block;">
                    <div style="font-size: 1.5rem; margin-bottom: var(--space-sm);">📈</div>
                    <div style="font-weight: 600; margin-bottom: var(--space-xs);">Analytics</div>
                    <p style="font-size: var(--text-sm); color: var(--muted); margin: 0;">View site statistics</p>
                </a>
            </div>

<?php
// Include admin footer (contains closing tags and JS)
include BASE_PATH . '/admin/includes/footer.php';
?>

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
    'total_views' => 0,
    'active_calculators' => 6
];

// Try to get real stats if database is connected
// try {
//     $stats['total_calculators'] = get_calculator_count(null, false);
//     $stats['total_categories'] = db_count('calculator_categories');
//     $stats['active_calculators'] = get_calculator_count(null, true);
// } catch (Exception $e) {
//     // Database not connected yet
// }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Dashboard - Admin Panel</title>

    <!-- Prevent indexing -->
    <meta name="robots" content="noindex, nofollow">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">

    <!-- Global Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Admin Specific Styles -->
    <style>
        /* Admin Layout */
        .admin-layout {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Admin Header */
        .admin-header {
            background: var(--primary);
            color: white;
            padding: var(--space-md) 0;
            position: sticky;
            top: 0;
            z-index: var(--z-sticky);
        }

        .admin-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .admin-logo {
            font-size: var(--text-xl);
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }

        .admin-user-name {
            font-size: var(--text-sm);
            display: none;
        }

        .admin-logout {
            color: white;
            text-decoration: none;
            font-size: var(--text-sm);
            opacity: 0.8;
        }

        .admin-logout:hover {
            opacity: 1;
            color: white;
        }

        /* Admin Content */
        .admin-content {
            flex: 1;
            padding: var(--space-lg) 0;
        }

        /* Page Header */
        .page-header {
            margin-bottom: var(--space-xl);
        }

        .page-title {
            font-size: var(--text-2xl);
            margin-bottom: var(--space-xs);
        }

        .page-subtitle {
            color: var(--muted);
            margin: 0;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-md);
            margin-bottom: var(--space-xl);
        }

        .stat-card {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-soft);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: var(--space-sm);
        }

        .stat-value {
            font-size: var(--text-3xl);
            font-weight: 700;
            color: var(--text);
            margin-bottom: var(--space-xs);
        }

        .stat-label {
            font-size: var(--text-sm);
            color: var(--muted);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-md);
        }

        .action-card {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-soft);
            text-decoration: none;
            color: inherit;
            transition: all var(--transition-normal);
            display: block;
        }

        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: inherit;
        }

        .action-icon {
            font-size: 1.5rem;
            margin-bottom: var(--space-sm);
        }

        .action-title {
            font-weight: 600;
            margin-bottom: var(--space-xs);
        }

        .action-desc {
            font-size: var(--text-sm);
            color: var(--muted);
            margin: 0;
        }

        /* Flash Messages */
        .flash-message {
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            border-left: 4px solid;
        }

        .flash-success {
            background: var(--success-soft);
            border-color: var(--success);
            color: #155724;
        }

        .flash-error {
            background: var(--danger-soft);
            border-color: var(--danger);
            color: #721c24;
        }

        /* Responsive */
        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .quick-actions {
                grid-template-columns: repeat(4, 1fr);
            }

            .admin-user-name {
                display: block;
            }
        }
    </style>
</head>
<body class="admin-layout">

    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container">
            <div class="admin-header-inner">
                <a href="/admin/dashboard.php" class="admin-logo">
                    🧮 CalcHub Admin
                </a>

                <div class="admin-user">
                    <span class="admin-user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="/admin/logout.php" class="admin-logout">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="admin-content">
        <div class="container">

            <!-- Flash Message -->
            <?php if ($msg = flash()): ?>
            <div class="flash-message flash-<?php echo $msg['type']; ?>">
                <?php echo htmlspecialchars($msg['text']); ?>
            </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🧮</div>
                    <div class="stat-value"><?php echo $stats['total_calculators']; ?></div>
                    <div class="stat-label">Total Calculators</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📁</div>
                    <div class="stat-value"><?php echo $stats['total_categories']; ?></div>
                    <div class="stat-label">Categories</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?php echo $stats['active_calculators']; ?></div>
                    <div class="stat-label">Active</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">👁️</div>
                    <div class="stat-value"><?php echo number_format($stats['total_views']); ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <h2 class="mb-md">Quick Actions</h2>
            <div class="quick-actions">
                <a href="/admin/calculators/add.php" class="action-card">
                    <div class="action-icon">➕</div>
                    <div class="action-title">Add Calculator</div>
                    <p class="action-desc">Create a new calculator</p>
                </a>

                <a href="/admin/calculators/" class="action-card">
                    <div class="action-icon">📋</div>
                    <div class="action-title">Manage Calculators</div>
                    <p class="action-desc">View and edit all calculators</p>
                </a>

                <a href="/admin/categories/" class="action-card">
                    <div class="action-icon">📁</div>
                    <div class="action-title">Categories</div>
                    <p class="action-desc">Manage categories</p>
                </a>

                <a href="/admin/settings/" class="action-card">
                    <div class="action-icon">⚙️</div>
                    <div class="action-title">Settings</div>
                    <p class="action-desc">Site configuration</p>
                </a>
            </div>

        </div>
    </main>

</body>
</html>

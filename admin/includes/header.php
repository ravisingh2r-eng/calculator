<?php
/**
 * Admin Header Include
 *
 * Top navigation bar for admin panel
 * Include this at the top of admin pages after require_login()
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Get current user if not already set
if (!isset($user)) {
    $user = current_user();
}

// Get current page for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Admin Panel</title>

    <!-- Prevent indexing -->
    <meta name="robots" content="noindex, nofollow">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">

    <!-- Global Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Admin Layout Styles -->
    <style>
        /* Admin Layout Structure */
        .admin-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navigation Bar */
        .admin-topbar {
            background: var(--primary);
            color: white;
            height: 60px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: var(--z-fixed);
            display: flex;
            align-items: center;
            padding: 0 var(--space-md);
            box-shadow: var(--shadow-medium);
        }

        .admin-topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        /* Logo */
        .admin-logo {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: var(--text-lg);
        }

        .admin-logo:hover {
            color: white;
        }

        .admin-logo-icon {
            font-size: 1.5rem;
        }

        /* Mobile Menu Toggle */
        .admin-menu-toggle {
            display: flex;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: var(--space-sm);
            margin-right: var(--space-sm);
        }

        /* User Menu */
        .admin-user-menu {
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }

        .admin-user-info {
            display: none;
            align-items: center;
            gap: var(--space-sm);
        }

        .admin-user-name {
            font-size: var(--text-sm);
            font-weight: 500;
        }

        .admin-user-role {
            font-size: var(--text-xs);
            opacity: 0.7;
            text-transform: capitalize;
        }

        .admin-logout-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: var(--radius-md);
            font-size: var(--text-sm);
            transition: background var(--transition-fast);
        }

        .admin-logout-btn:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        /* Main Layout Container */
        .admin-layout {
            display: flex;
            margin-top: 60px;
            min-height: calc(100vh - 60px);
        }

        /* Sidebar */
        .admin-sidebar {
            width: 250px;
            background: var(--card);
            border-right: 1px solid var(--border-light);
            position: fixed;
            top: 60px;
            left: -250px;
            bottom: 0;
            overflow-y: auto;
            transition: left var(--transition-normal);
            z-index: var(--z-fixed);
        }

        .admin-sidebar.active {
            left: 0;
        }

        /* Sidebar Overlay */
        .admin-sidebar-overlay {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: calc(var(--z-fixed) - 1);
            display: none;
        }

        .admin-sidebar-overlay.active {
            display: block;
        }

        /* Sidebar Menu */
        .admin-menu {
            list-style: none;
            padding: var(--space-md) 0;
            margin: 0;
        }

        .admin-menu-item {
            margin: 0;
        }

        .admin-menu-link {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            padding: 12px var(--space-lg);
            color: var(--text-light);
            text-decoration: none;
            font-size: var(--text-sm);
            transition: all var(--transition-fast);
            border-left: 3px solid transparent;
        }

        .admin-menu-link:hover {
            background: var(--bg);
            color: var(--accent);
        }

        .admin-menu-link.active {
            background: var(--accent-soft);
            color: var(--accent);
            border-left-color: var(--accent);
            font-weight: 500;
        }

        .admin-menu-icon {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .admin-menu-divider {
            height: 1px;
            background: var(--border-light);
            margin: var(--space-md) var(--space-lg);
        }

        .admin-menu-label {
            padding: var(--space-sm) var(--space-lg);
            font-size: var(--text-xs);
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* Main Content Area */
        .admin-main {
            flex: 1;
            padding: var(--space-lg);
            background: var(--bg);
            margin-left: 0;
            transition: margin-left var(--transition-normal);
        }

        /* Desktop Styles */
        @media (min-width: 992px) {
            .admin-menu-toggle {
                display: none;
            }

            .admin-user-info {
                display: flex;
            }

            .admin-sidebar {
                left: 0;
            }

            .admin-main {
                margin-left: 250px;
            }
        }

        /* Page Header */
        .admin-page-header {
            margin-bottom: var(--space-xl);
        }

        .admin-page-title {
            font-size: var(--text-2xl);
            margin-bottom: var(--space-xs);
        }

        .admin-page-subtitle {
            color: var(--muted);
            margin: 0;
        }

        /* Stats Cards */
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: var(--space-md);
            margin-bottom: var(--space-xl);
        }

        .admin-stat-card {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-soft);
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }

        .admin-stat-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .admin-stat-icon.blue {
            background: var(--accent-soft);
        }

        .admin-stat-icon.green {
            background: var(--success-soft);
        }

        .admin-stat-icon.purple {
            background: #f3e8ff;
        }

        .admin-stat-icon.orange {
            background: var(--warning-soft);
        }

        .admin-stat-content {
            flex: 1;
        }

        .admin-stat-value {
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--text);
            line-height: 1;
            margin-bottom: 4px;
        }

        .admin-stat-label {
            font-size: var(--text-sm);
            color: var(--muted);
        }

        @media (min-width: 576px) {
            .admin-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 992px) {
            .admin-stats {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1200px) {
            .admin-stats {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Flash Messages */
        .admin-flash {
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            border-left: 4px solid;
        }

        .admin-flash-success {
            background: var(--success-soft);
            border-color: var(--success);
            color: #155724;
        }

        .admin-flash-error {
            background: var(--danger-soft);
            border-color: var(--danger);
            color: #721c24;
        }

        .admin-flash-warning {
            background: var(--warning-soft);
            border-color: var(--warning);
            color: #856404;
        }

        .admin-flash-info {
            background: var(--info-soft);
            border-color: var(--info);
            color: #0c5460;
        }
    </style>

    <?php if (isset($extraStyles)): ?>
    <style><?php echo $extraStyles; ?></style>
    <?php endif; ?>
</head>
<body>

<div class="admin-wrapper">

    <!-- Top Navigation Bar -->
    <header class="admin-topbar">
        <div class="admin-topbar-inner">
            <!-- Mobile Menu Toggle -->
            <button class="admin-menu-toggle" id="sidebarToggle" aria-label="Toggle menu">
                ☰
            </button>

            <!-- Logo -->
            <a href="/admin/dashboard.php" class="admin-logo">
                <span class="admin-logo-icon">🧮</span>
                <span>CalcHub</span>
            </a>

            <!-- User Menu -->
            <div class="admin-user-menu">
                <div class="admin-user-info">
                    <div>
                        <div class="admin-user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                        <div class="admin-user-role"><?php echo htmlspecialchars($user['role']); ?></div>
                    </div>
                </div>
                <a href="/admin/logout.php" class="admin-logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <!-- Layout Container -->
    <div class="admin-layout">

        <!-- Sidebar Overlay (mobile) -->
        <div class="admin-sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <?php include BASE_PATH . '/admin/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-main">

            <!-- Flash Messages -->
            <?php if ($msg = flash()): ?>
            <div class="admin-flash admin-flash-<?php echo $msg['type']; ?>">
                <?php echo htmlspecialchars($msg['text']); ?>
            </div>
            <?php endif; ?>

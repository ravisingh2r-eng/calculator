<?php
/**
 * Admin Sidebar Include
 *
 * Left navigation menu for admin panel
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Determine active menu item
$currentPage = $currentPage ?? basename($_SERVER['PHP_SELF'], '.php');
$currentDir = $currentDir ?? basename(dirname($_SERVER['PHP_SELF']));

// Helper function to check if menu item is active
function isMenuActive($page, $dir = null) {
    global $currentPage, $currentDir;

    if ($dir !== null) {
        return $currentDir === $dir;
    }

    return $currentPage === $page;
}
?>

<aside class="admin-sidebar" id="adminSidebar">
    <nav>
        <ul class="admin-menu">
            <!-- Main Menu -->
            <li class="admin-menu-label">Main</li>

            <li class="admin-menu-item">
                <a href="/admin/dashboard.php" class="admin-menu-link <?php echo isMenuActive('dashboard') ? 'active' : ''; ?>">
                    <span class="admin-menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Content Management -->
            <li class="admin-menu-label">Content</li>

            <li class="admin-menu-item">
                <a href="/admin/calculators/" class="admin-menu-link <?php echo isMenuActive('', 'calculators') ? 'active' : ''; ?>">
                    <span class="admin-menu-icon">🧮</span>
                    <span>Calculators</span>
                </a>
            </li>

            <li class="admin-menu-item">
                <a href="/admin/categories/" class="admin-menu-link <?php echo isMenuActive('', 'categories') ? 'active' : ''; ?>">
                    <span class="admin-menu-icon">📁</span>
                    <span>Categories</span>
                </a>
            </li>

            <!-- Analytics -->
            <li class="admin-menu-label">Insights</li>

            <li class="admin-menu-item">
                <a href="/admin/analytics/" class="admin-menu-link <?php echo isMenuActive('', 'analytics') ? 'active' : ''; ?>">
                    <span class="admin-menu-icon">📈</span>
                    <span>Analytics</span>
                </a>
            </li>

            <!-- Settings -->
            <li class="admin-menu-label">System</li>

            <li class="admin-menu-item">
                <a href="/admin/settings/" class="admin-menu-link <?php echo isMenuActive('', 'settings') ? 'active' : ''; ?>">
                    <span class="admin-menu-icon">⚙️</span>
                    <span>Settings</span>
                </a>
            </li>

            <?php if (is_admin()): ?>
            <li class="admin-menu-item">
                <a href="/admin/users/" class="admin-menu-link <?php echo isMenuActive('', 'users') ? 'active' : ''; ?>">
                    <span class="admin-menu-icon">👥</span>
                    <span>Users</span>
                </a>
            </li>
            <?php endif; ?>

            <!-- Divider -->
            <div class="admin-menu-divider"></div>

            <!-- Quick Links -->
            <li class="admin-menu-item">
                <a href="/" target="_blank" class="admin-menu-link">
                    <span class="admin-menu-icon">🌐</span>
                    <span>View Site</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

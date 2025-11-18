<?php
/**
 * Admin Calculators Management
 *
 * List, filter, and manage calculators
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Define base path
define('BASE_PATH', dirname(dirname(__DIR__)));

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

// Include models
require_once BASE_PATH . '/admin/models/calculators.php';
require_once BASE_PATH . '/admin/models/categories.php';

// Initialize variables
$error = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        // Handle Toggle Active
        if ($action === 'toggle_active') {
            $calculatorId = (int) ($_POST['calculator_id'] ?? 0);
            $isActive = (int) ($_POST['is_active'] ?? 0);

            try {
                toggle_calculator_active($calculatorId, (bool) $isActive);
                flash('Calculator status updated!', 'success');

                // Preserve filters in redirect
                $redirect = '/admin/calculators/';
                $params = [];
                if (!empty($_POST['filter_category'])) {
                    $params[] = 'category=' . urlencode($_POST['filter_category']);
                }
                if (!empty($_POST['filter_search'])) {
                    $params[] = 'search=' . urlencode($_POST['filter_search']);
                }
                if (!empty($params)) {
                    $redirect .= '?' . implode('&', $params);
                }

                header('Location: ' . $redirect);
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        // Handle Delete
        if ($action === 'delete') {
            $calculatorId = (int) ($_POST['calculator_id'] ?? 0);

            try {
                // Soft delete (set is_active = 0)
                toggle_calculator_active($calculatorId, false);
                flash('Calculator deleted successfully!', 'success');
                header('Location: /admin/calculators/');
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Get filter parameters
$filterCategory = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$filterSearch = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get all categories for filter dropdown
$categories = get_all_categories(false);

// Build query for calculators with category names
$whereConditions = [];
$whereParams = [];

if ($filterCategory > 0) {
    $whereConditions[] = 'c.category_id = ?';
    $whereParams[] = $filterCategory;
}

if (!empty($filterSearch)) {
    $whereConditions[] = '(c.name LIKE ? OR c.slug LIKE ?)';
    $searchTerm = '%' . $filterSearch . '%';
    $whereParams[] = $searchTerm;
    $whereParams[] = $searchTerm;
}

$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

// Fetch calculators with category names
$sql = "SELECT c.*, cat.name as category_name, cat.icon as category_icon
        FROM calculators c
        LEFT JOIN calculator_categories cat ON c.category_id = cat.id
        {$whereClause}
        ORDER BY c.category_id ASC, c.sort_order ASC, c.name ASC";

$calculators = db_fetch_all($sql, $whereParams);

// Page title
$pageTitle = 'Calculators';

// Extra styles for this page
$extraStyles = '
/* Table Styles */
.admin-table-wrapper {
    background: var(--card);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-soft);
    overflow: hidden;
    margin-bottom: var(--space-xl);
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th,
.admin-table td {
    padding: var(--space-md);
    text-align: left;
    border-bottom: 1px solid var(--border-light);
}

.admin-table th {
    background: var(--bg);
    font-weight: 600;
    font-size: var(--text-sm);
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.admin-table tr:last-child td {
    border-bottom: none;
}

.admin-table tr:hover {
    background: var(--bg);
}

/* Table Actions */
.table-actions {
    display: flex;
    gap: var(--space-xs);
}

.btn-sm {
    padding: 6px 12px;
    font-size: var(--text-xs);
    border-radius: var(--radius-sm);
    text-decoration: none;
    display: inline-block;
}

.btn-edit {
    background: var(--accent-soft);
    color: var(--accent);
}

.btn-delete {
    background: var(--danger-soft);
    color: var(--danger);
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 26px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: var(--success);
}

input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

/* Toolbar */
.admin-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-lg);
    flex-wrap: wrap;
    gap: var(--space-md);
}

/* Filters */
.admin-filters {
    display: flex;
    gap: var(--space-md);
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: var(--space-lg);
    padding: var(--space-md);
    background: var(--card);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-soft);
}

.filter-group {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.filter-label {
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--muted);
}

.filter-select,
.filter-input {
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    min-width: 150px;
}

.filter-select:focus,
.filter-input:focus {
    outline: none;
    border-color: var(--accent);
}

.btn-filter {
    padding: 8px 16px;
    background: var(--accent);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    cursor: pointer;
}

.btn-clear {
    padding: 8px 16px;
    background: var(--bg);
    color: var(--text);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    cursor: pointer;
    text-decoration: none;
}

/* Category Badge */
.category-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    background: var(--bg);
    border-radius: var(--radius-sm);
    font-size: var(--text-xs);
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    font-size: var(--text-xs);
    font-weight: 500;
}

.status-yes {
    background: var(--success-soft);
    color: var(--success);
}

.status-no {
    background: var(--danger-soft);
    color: var(--danger);
}

/* Text Truncate */
.text-truncate {
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.text-code {
    font-family: monospace;
    font-size: var(--text-xs);
    background: var(--bg);
    padding: 2px 6px;
    border-radius: var(--radius-sm);
}

/* Responsive Table */
@media (max-width: 1200px) {
    .admin-table-wrapper {
        overflow-x: auto;
    }

    .admin-table {
        min-width: 1000px;
    }
}

@media (max-width: 768px) {
    .admin-filters {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-group {
        width: 100%;
    }

    .filter-select,
    .filter-input {
        flex: 1;
    }
}

/* Error Message */
.error-message {
    background: var(--danger-soft);
    border: 1px solid var(--danger);
    color: #721c24;
    padding: var(--space-md);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-lg);
}
';

// Include admin header
include BASE_PATH . '/admin/includes/header.php';
?>

            <!-- Error Message -->
            <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="admin-page-header">
                <h1 class="admin-page-title">Calculators</h1>
                <p class="admin-page-subtitle">Manage all calculators</p>
            </div>

            <!-- Filters -->
            <form method="GET" class="admin-filters">
                <div class="filter-group">
                    <label class="filter-label" for="category">Category:</label>
                    <select name="category" id="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filterCategory == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['icon'] . ' ' . $cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="search">Search:</label>
                    <input type="text" name="search" id="search" class="filter-input"
                           placeholder="Name or slug..."
                           value="<?php echo htmlspecialchars($filterSearch); ?>">
                </div>

                <button type="submit" class="btn-filter">Filter</button>
                <?php if ($filterCategory || $filterSearch): ?>
                <a href="/admin/calculators/" class="btn-clear">Clear</a>
                <?php endif; ?>
            </form>

            <!-- Toolbar -->
            <div class="admin-toolbar">
                <div>
                    <strong><?php echo count($calculators); ?></strong> calculators
                    <?php if ($filterCategory || $filterSearch): ?>
                    (filtered)
                    <?php endif; ?>
                </div>
                <a href="/admin/calculators/add.php" class="btn btn-primary">
                    + Add Calculator
                </a>
            </div>

            <!-- Calculators Table -->
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Slug</th>
                            <th>Active</th>
                            <th>Indexed</th>
                            <th>Ad Layout</th>
                            <th>JS File</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($calculators)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: var(--space-xl); color: var(--muted);">
                                No calculators found.
                                <?php if ($filterCategory || $filterSearch): ?>
                                Try adjusting your filters.
                                <?php else: ?>
                                Click "Add Calculator" to create one.
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($calculators as $calc): ?>
                        <tr>
                            <td><?php echo $calc['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($calc['name']); ?></strong>
                            </td>
                            <td>
                                <?php if ($calc['category_name']): ?>
                                <span class="category-badge">
                                    <?php echo htmlspecialchars($calc['category_icon']); ?>
                                    <?php echo htmlspecialchars($calc['category_name']); ?>
                                </span>
                                <?php else: ?>
                                <span style="color: var(--muted);">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code class="text-code"><?php echo htmlspecialchars($calc['slug']); ?></code>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="calculator_id" value="<?php echo $calc['id']; ?>">
                                    <input type="hidden" name="is_active" value="<?php echo $calc['is_active'] ? 0 : 1; ?>">
                                    <input type="hidden" name="filter_category" value="<?php echo $filterCategory; ?>">
                                    <input type="hidden" name="filter_search" value="<?php echo htmlspecialchars($filterSearch); ?>">
                                    <label class="toggle-switch">
                                        <input type="checkbox" <?php echo $calc['is_active'] ? 'checked' : ''; ?>
                                               onchange="this.form.submit()">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $calc['is_indexed'] ? 'status-yes' : 'status-no'; ?>">
                                    <?php echo $calc['is_indexed'] ? 'Yes' : 'No'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-code"><?php echo htmlspecialchars($calc['ad_layout'] ?? 'default'); ?></span>
                            </td>
                            <td class="text-truncate" title="<?php echo htmlspecialchars($calc['js_file'] ?? ''); ?>">
                                <?php if ($calc['js_file']): ?>
                                <code class="text-code"><?php echo htmlspecialchars(basename($calc['js_file'])); ?></code>
                                <?php else: ?>
                                <span style="color: var(--muted);">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="/admin/calculators/edit.php?id=<?php echo $calc['id']; ?>"
                                       class="btn btn-sm btn-edit">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Quick Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: var(--space-md); margin-top: var(--space-lg);">
                <div style="background: var(--card); padding: var(--space-md); border-radius: var(--radius-md); text-align: center;">
                    <div style="font-size: var(--text-2xl); font-weight: 700;">
                        <?php echo count(array_filter($calculators, fn($c) => $c['is_active'])); ?>
                    </div>
                    <div style="font-size: var(--text-sm); color: var(--muted);">Active</div>
                </div>
                <div style="background: var(--card); padding: var(--space-md); border-radius: var(--radius-md); text-align: center;">
                    <div style="font-size: var(--text-2xl); font-weight: 700;">
                        <?php echo count(array_filter($calculators, fn($c) => !$c['is_active'])); ?>
                    </div>
                    <div style="font-size: var(--text-sm); color: var(--muted);">Inactive</div>
                </div>
                <div style="background: var(--card); padding: var(--space-md); border-radius: var(--radius-md); text-align: center;">
                    <div style="font-size: var(--text-2xl); font-weight: 700;">
                        <?php echo count(array_filter($calculators, fn($c) => $c['is_indexed'])); ?>
                    </div>
                    <div style="font-size: var(--text-sm); color: var(--muted);">Indexed</div>
                </div>
            </div>

<?php
// Include admin footer
include BASE_PATH . '/admin/includes/footer.php';
?>

<?php
/**
 * Admin Users Management
 *
 * Manage admin panel users
 * Only accessible by superadmin role
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

// Check for superadmin role
if ($user['role'] !== 'superadmin') {
    http_response_code(403);
    die('Access denied. Only superadmin can manage users.');
}

// Include user model
require_once BASE_PATH . '/admin/models/users.php';

// Initialize variables
$error = '';
$success = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        // Handle Create User
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'viewer';

            // Validation
            $errors = [];

            if (empty($name)) {
                $errors[] = 'Name is required.';
            }

            if (empty($email)) {
                $errors[] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email address.';
            } elseif (user_email_exists($email)) {
                $errors[] = 'Email already exists.';
            }

            if (empty($password)) {
                $errors[] = 'Password is required.';
            } elseif (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters.';
            }

            $allowedRoles = ['superadmin', 'admin', 'editor', 'viewer'];
            if (!in_array($role, $allowedRoles)) {
                $role = 'viewer';
            }

            if (empty($errors)) {
                try {
                    // Create user with hashed password
                    $newUserId = create_user($name, $email, $password, $role);
                    flash('User created successfully!', 'success');
                    header('Location: /admin/users/');
                    exit;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            } else {
                $error = implode(' ', $errors);
            }
        }

        // Handle Delete User
        if ($action === 'delete') {
            $userId = (int) ($_POST['user_id'] ?? 0);

            // Prevent self-deletion
            if ($userId === $user['id']) {
                $error = 'You cannot delete your own account.';
            } else {
                try {
                    hard_delete_user($userId);
                    flash('User deleted successfully!', 'success');
                    header('Location: /admin/users/');
                    exit;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        // Handle Toggle Active
        if ($action === 'toggle_active') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            $isActive = (int) ($_POST['is_active'] ?? 0);

            // Prevent self-deactivation
            if ($userId === $user['id'] && $isActive === 0) {
                $error = 'You cannot deactivate your own account.';
            } else {
                try {
                    update_user($userId, ['is_active' => $isActive]);
                    flash('User status updated!', 'success');
                    header('Location: /admin/users/');
                    exit;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }
    }
}

// Get all users (including inactive)
$users = get_all_users(false);

// Page title
$pageTitle = 'Users';

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

/* Role Badge */
.role-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    font-size: var(--text-xs);
    font-weight: 500;
    text-transform: capitalize;
}

.role-superadmin {
    background: #f3e8ff;
    color: #7c3aed;
}

.role-admin {
    background: var(--danger-soft);
    color: var(--danger);
}

.role-editor {
    background: var(--warning-soft);
    color: #b45309;
}

.role-viewer {
    background: var(--accent-soft);
    color: var(--accent);
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: var(--z-modal);
    padding: var(--space-md);
}

.modal-overlay.active {
    display: flex;
}

.modal-content {
    background: var(--card);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--shadow-large);
}

.modal-header {
    padding: var(--space-lg);
    border-bottom: 1px solid var(--border-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: var(--text-lg);
    font-weight: 600;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--muted);
    padding: 0;
    line-height: 1;
}

.modal-close:hover {
    color: var(--text);
}

.modal-body {
    padding: var(--space-lg);
}

.modal-footer {
    padding: var(--space-lg);
    border-top: 1px solid var(--border-light);
    display: flex;
    justify-content: flex-end;
    gap: var(--space-sm);
}

/* Form Styles */
.form-group {
    margin-bottom: var(--space-md);
}

.form-label {
    display: block;
    margin-bottom: var(--space-xs);
    font-weight: 500;
    font-size: var(--text-sm);
}

.form-input,
.form-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    transition: border-color var(--transition-fast);
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: var(--accent);
}

.form-hint {
    font-size: var(--text-xs);
    color: var(--muted);
    margin-top: 4px;
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

/* Current User Indicator */
.current-user {
    background: var(--accent-soft);
    font-weight: 500;
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

/* Responsive Table */
@media (max-width: 768px) {
    .admin-table-wrapper {
        overflow-x: auto;
    }

    .admin-table {
        min-width: 700px;
    }
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
                <h1 class="admin-page-title">Users</h1>
                <p class="admin-page-subtitle">Manage admin panel users</p>
            </div>

            <!-- Toolbar -->
            <div class="admin-toolbar">
                <div>
                    <strong><?php echo count($users); ?></strong> users total
                </div>
                <button type="button" class="btn btn-primary" onclick="openModal()">
                    + Add User
                </button>
            </div>

            <!-- Users Table -->
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: var(--space-xl); color: var(--muted);">
                                No users found.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($users as $u): ?>
                        <tr class="<?php echo $u['id'] === $user['id'] ? 'current-user' : ''; ?>">
                            <td><?php echo $u['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($u['name']); ?></strong>
                                <?php if ($u['id'] === $user['id']): ?>
                                <span style="font-size: var(--text-xs); color: var(--muted);">(you)</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $u['role']; ?>">
                                    <?php echo htmlspecialchars($u['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <?php if ($u['id'] === $user['id']): ?>
                                <span style="color: var(--muted);">—</span>
                                <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <input type="hidden" name="is_active" value="<?php echo $u['is_active'] ? 0 : 1; ?>">
                                    <label class="toggle-switch">
                                        <input type="checkbox" <?php echo $u['is_active'] ? 'checked' : ''; ?>
                                               onchange="this.form.submit()">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </form>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['id'] !== $user['id']): ?>
                                <div class="table-actions">
                                    <form method="POST" style="display: inline;"
                                          onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-delete">Delete</button>
                                    </form>
                                </div>
                                <?php else: ?>
                                <span style="color: var(--muted);">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

<!-- Add User Modal -->
<div class="modal-overlay" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add User</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>

        <form method="POST" id="userForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="create">

            <div class="modal-body">
                <!-- Name -->
                <div class="form-group">
                    <label class="form-label" for="name">Full Name *</label>
                    <input type="text" class="form-input" id="name" name="name"
                           required maxlength="100"
                           placeholder="John Doe">
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">Email Address *</label>
                    <input type="email" class="form-input" id="email" name="email"
                           required maxlength="255"
                           placeholder="john@example.com">
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">Password *</label>
                    <input type="password" class="form-input" id="password" name="password"
                           required minlength="8"
                           placeholder="Minimum 8 characters">
                    <div class="form-hint">Password will be securely hashed</div>
                </div>

                <!-- Role -->
                <div class="form-group">
                    <label class="form-label" for="role">Role *</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="viewer">Viewer (read-only)</option>
                        <option value="editor">Editor (can edit content)</option>
                        <option value="admin">Admin (full access)</option>
                        <option value="superadmin">Superadmin (manage users)</option>
                    </select>
                    <div class="form-hint">
                        Viewer &lt; Editor &lt; Admin &lt; Superadmin
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" style="background: var(--bg);" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<?php
// Extra scripts for this page
$extraScripts = "
// Modal Functions
function openModal() {
    document.getElementById('userModal').classList.add('active');
    document.getElementById('name').focus();
}

function closeModal() {
    document.getElementById('userModal').classList.remove('active');
    document.getElementById('userForm').reset();
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Close modal when clicking outside
document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
";

// Include admin footer
include BASE_PATH . '/admin/includes/footer.php';
?>

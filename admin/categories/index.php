<?php
/**
 * Admin Categories Management
 *
 * List, add, edit, and manage calculator categories
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

// Include category model
require_once BASE_PATH . '/admin/models/categories.php';

// Initialize variables
$error = '';
$success = '';
$editCategory = null;
$formData = [
    'name' => '',
    'slug' => '',
    'icon' => '',
    'short_desc' => '',
    'sort_order' => 0,
    'is_active' => 1
];

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        // Handle Create/Update
        if ($action === 'save') {
            $formData = [
                'name' => trim($_POST['name'] ?? ''),
                'slug' => trim($_POST['slug'] ?? ''),
                'icon' => trim($_POST['icon'] ?? ''),
                'short_desc' => trim($_POST['short_desc'] ?? ''),
                'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $categoryId = (int) ($_POST['category_id'] ?? 0);

            try {
                if ($categoryId > 0) {
                    // Update existing category
                    update_category($categoryId, $formData);
                    flash('Category updated successfully!', 'success');
                } else {
                    // Create new category
                    create_category($formData);
                    flash('Category created successfully!', 'success');
                }

                // Redirect to prevent form resubmission
                header('Location: /admin/categories/');
                exit;

            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        // Handle Toggle Active
        if ($action === 'toggle_active') {
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $isActive = (int) ($_POST['is_active'] ?? 0);

            try {
                update_category($categoryId, ['is_active' => $isActive]);
                flash('Category status updated!', 'success');
                header('Location: /admin/categories/');
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        // Handle Delete
        if ($action === 'delete') {
            $categoryId = (int) ($_POST['category_id'] ?? 0);

            try {
                delete_category($categoryId);
                flash('Category deleted successfully!', 'success');
                header('Location: /admin/categories/');
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Handle Edit mode (GET parameter)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editCategory = get_category_by_id((int) $_GET['edit']);
    if ($editCategory) {
        $formData = [
            'name' => $editCategory['name'],
            'slug' => $editCategory['slug'],
            'icon' => $editCategory['icon'],
            'short_desc' => $editCategory['short_desc'],
            'sort_order' => $editCategory['sort_order'],
            'is_active' => $editCategory['is_active']
        ];
    }
}

// Get all categories (including inactive)
$categories = get_all_categories(false);

// Page title
$pageTitle = 'Categories';

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

.form-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    transition: border-color var(--transition-fast);
}

.form-input:focus {
    outline: none;
    border-color: var(--accent);
}

.form-hint {
    font-size: var(--text-xs);
    color: var(--muted);
    margin-top: 4px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-md);
}

.form-checkbox {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.form-checkbox input {
    width: 18px;
    height: 18px;
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

/* Icon Preview */
.icon-preview {
    font-size: 1.5rem;
    margin-right: var(--space-sm);
}

/* Category Icon in Table */
.category-icon {
    font-size: 1.2rem;
}

/* Truncate Text */
.text-truncate {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Responsive Table */
@media (max-width: 768px) {
    .admin-table-wrapper {
        overflow-x: auto;
    }

    .admin-table {
        min-width: 800px;
    }

    .form-row {
        grid-template-columns: 1fr;
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
                <h1 class="admin-page-title">Categories</h1>
                <p class="admin-page-subtitle">Manage calculator categories</p>
            </div>

            <!-- Toolbar -->
            <div class="admin-toolbar">
                <div>
                    <strong><?php echo count($categories); ?></strong> categories total
                </div>
                <button type="button" class="btn btn-primary" onclick="openModal()">
                    + Add Category
                </button>
            </div>

            <!-- Categories Table -->
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Icon</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Order</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: var(--space-xl); color: var(--muted);">
                                No categories found. Click "Add Category" to create one.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td class="category-icon"><?php echo htmlspecialchars($category['icon']); ?></td>
                            <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                            <td><code><?php echo htmlspecialchars($category['slug']); ?></code></td>
                            <td class="text-truncate" title="<?php echo htmlspecialchars($category['short_desc']); ?>">
                                <?php echo htmlspecialchars($category['short_desc']); ?>
                            </td>
                            <td><?php echo $category['sort_order']; ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                    <input type="hidden" name="is_active" value="<?php echo $category['is_active'] ? 0 : 1; ?>">
                                    <label class="toggle-switch">
                                        <input type="checkbox" <?php echo $category['is_active'] ? 'checked' : ''; ?>
                                               onchange="this.form.submit()">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="?edit=<?php echo $category['id']; ?>"
                                       class="btn btn-sm btn-edit"
                                       onclick="event.preventDefault(); editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                        Edit
                                    </a>
                                    <form method="POST" style="display: inline;"
                                          onsubmit="return confirm('Are you sure you want to delete this category?')">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

<!-- Category Modal -->
<div class="modal-overlay" id="categoryModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Add Category</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>

        <form method="POST" id="categoryForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="category_id" id="categoryId" value="">

            <div class="modal-body">
                <!-- Name -->
                <div class="form-group">
                    <label class="form-label" for="name">Category Name *</label>
                    <input type="text" class="form-input" id="name" name="name"
                           required maxlength="100"
                           value="<?php echo htmlspecialchars($formData['name']); ?>"
                           oninput="autoGenerateSlug(this.value)">
                </div>

                <!-- Slug -->
                <div class="form-group">
                    <label class="form-label" for="slug">
                        URL Slug *
                        <button type="button" style="font-size: var(--text-xs); margin-left: var(--space-sm); padding: 2px 6px; cursor: pointer;"
                                onclick="generateSlugFromName()">Auto-generate</button>
                    </label>
                    <input type="text" class="form-input" id="slug" name="slug"
                           required maxlength="100" pattern="[a-z0-9-]+"
                           value="<?php echo htmlspecialchars($formData['slug']); ?>">
                    <div class="form-hint">Lowercase letters, numbers, and hyphens only</div>
                </div>

                <!-- Icon & Sort Order -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="icon">Icon (Emoji)</label>
                        <input type="text" class="form-input" id="icon" name="icon"
                               maxlength="10"
                               value="<?php echo htmlspecialchars($formData['icon']); ?>"
                               placeholder="e.g., &#128200;">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="sort_order">Sort Order</label>
                        <input type="number" class="form-input" id="sort_order" name="sort_order"
                               min="0" max="999"
                               value="<?php echo $formData['sort_order']; ?>">
                    </div>
                </div>

                <!-- Short Description -->
                <div class="form-group">
                    <label class="form-label" for="short_desc">Short Description</label>
                    <textarea class="form-input" id="short_desc" name="short_desc"
                              rows="3" maxlength="255"
                              placeholder="Brief description of this category"><?php echo htmlspecialchars($formData['short_desc']); ?></textarea>
                </div>

                <!-- Is Active -->
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="is_active" id="is_active"
                               <?php echo $formData['is_active'] ? 'checked' : ''; ?>>
                        <span>Active (visible on site)</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" style="background: var(--bg);" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Save Category</button>
            </div>
        </form>
    </div>
</div>

<?php
// Extra scripts for this page
$extraScripts = "
// Modal Functions
function openModal() {
    document.getElementById('categoryModal').classList.add('active');
    document.getElementById('name').focus();
}

function closeModal() {
    document.getElementById('categoryModal').classList.remove('active');
    resetForm();
}

function resetForm() {
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('submitBtn').textContent = 'Save Category';
    document.getElementById('is_active').checked = true;
}

// Edit Category
function editCategory(category) {
    document.getElementById('categoryId').value = category.id;
    document.getElementById('name').value = category.name;
    document.getElementById('slug').value = category.slug;
    document.getElementById('icon').value = category.icon || '';
    document.getElementById('short_desc').value = category.short_desc || '';
    document.getElementById('sort_order').value = category.sort_order || 0;
    document.getElementById('is_active').checked = category.is_active == 1;

    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('submitBtn').textContent = 'Update Category';

    openModal();
}

// Auto-generate slug from name
var autoSlug = true;

function autoGenerateSlug(name) {
    if (!autoSlug) return;

    var slug = name
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\\s-]/g, '')
        .replace(/[\\s_]+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '');

    document.getElementById('slug').value = slug;
}

function generateSlugFromName() {
    var name = document.getElementById('name').value;
    autoSlug = true;
    autoGenerateSlug(name);
}

// Disable auto-slug when user manually edits slug
document.getElementById('slug').addEventListener('input', function() {
    autoSlug = false;
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Close modal when clicking outside
document.getElementById('categoryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Open modal if editing
" . ($editCategory ? "editCategory(" . json_encode($editCategory) . ");" : "") . "
";

// Include admin footer
include BASE_PATH . '/admin/includes/footer.php';
?>

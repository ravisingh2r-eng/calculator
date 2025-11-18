<?php
/**
 * Admin Calculator Add/Edit
 *
 * Create new calculator or edit existing one
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
$errors = [];
$isEditMode = false;
$calculatorId = 0;
$calculator = null;

// Default form data
$formData = [
    'category_id' => '',
    'name' => '',
    'slug' => '',
    'short_desc' => '',
    'js_file' => '',
    'is_active' => 1,
    'is_indexed' => 1,
    'show_in_sitemap' => 1,
    'ad_layout' => 'medium',
    'disable_ads' => 0
];

// Check if edit mode (id in query string)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $calculatorId = (int) $_GET['id'];
    $calculator = get_calculator_by_id($calculatorId);

    if ($calculator) {
        $isEditMode = true;
        // Prefill form data
        $formData = [
            'category_id' => $calculator['category_id'],
            'name' => $calculator['name'],
            'slug' => $calculator['slug'],
            'short_desc' => $calculator['short_desc'] ?? '',
            'js_file' => $calculator['js_file'] ?? '',
            'is_active' => $calculator['is_active'],
            'is_indexed' => $calculator['is_indexed'],
            'show_in_sitemap' => $calculator['show_in_sitemap'],
            'ad_layout' => $calculator['ad_layout'] ?? 'medium',
            'disable_ads' => $calculator['disable_ads'] ?? 0
        ];
    } else {
        flash('Calculator not found!', 'error');
        header('Location: /admin/calculators/');
        exit;
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Get form data
        $formData = [
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'short_desc' => trim($_POST['short_desc'] ?? ''),
            'js_file' => trim($_POST['js_file'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_indexed' => isset($_POST['is_indexed']) ? 1 : 0,
            'show_in_sitemap' => isset($_POST['show_in_sitemap']) ? 1 : 0,
            'ad_layout' => $_POST['ad_layout'] ?? 'medium',
            'disable_ads' => isset($_POST['disable_ads']) ? 1 : 0
        ];

        $calculatorId = (int) ($_POST['calculator_id'] ?? 0);
        $isEditMode = $calculatorId > 0;

        // Validation
        if (empty($formData['category_id'])) {
            $errors[] = 'Please select a category.';
        }

        if (empty($formData['name'])) {
            $errors[] = 'Calculator name is required.';
        } elseif (strlen($formData['name']) > 100) {
            $errors[] = 'Name must be less than 100 characters.';
        }

        if (empty($formData['slug'])) {
            $errors[] = 'URL slug is required.';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $formData['slug'])) {
            $errors[] = 'Slug can only contain lowercase letters, numbers, and hyphens.';
        } elseif (strlen($formData['slug']) > 100) {
            $errors[] = 'Slug must be less than 100 characters.';
        }

        if (strlen($formData['short_desc']) > 255) {
            $errors[] = 'Short description must be less than 255 characters.';
        }

        // Validate ad_layout
        $allowedLayouts = ['low', 'medium', 'high'];
        if (!in_array($formData['ad_layout'], $allowedLayouts)) {
            $formData['ad_layout'] = 'medium';
        }

        // If no errors, save
        if (empty($errors)) {
            try {
                if ($isEditMode) {
                    // Update existing calculator
                    update_calculator($calculatorId, $formData);
                    flash('Calculator updated successfully!', 'success');
                } else {
                    // Create new calculator
                    $newId = create_calculator($formData);
                    flash('Calculator created successfully!', 'success');
                }

                // Redirect to list
                header('Location: /admin/calculators/');
                exit;

            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
    }
}

// Get all categories for dropdown
$categories = get_all_categories(false);

// Page title
$pageTitle = $isEditMode ? 'Edit Calculator' : 'Add Calculator';

// Extra styles for this page
$extraStyles = '
/* Form Card */
.form-card {
    background: var(--card);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-soft);
    margin-bottom: var(--space-lg);
}

.form-card-header {
    padding: var(--space-lg);
    border-bottom: 1px solid var(--border-light);
}

.form-card-title {
    font-size: var(--text-lg);
    font-weight: 600;
    margin: 0;
}

.form-card-body {
    padding: var(--space-lg);
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

.form-label .required {
    color: var(--danger);
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    transition: border-color var(--transition-fast);
    background: var(--card);
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
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

/* Toggle Switch */
.form-switch {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    margin-bottom: var(--space-sm);
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
    flex-shrink: 0;
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

.switch-label {
    font-size: var(--text-sm);
}

/* Checkbox */
.form-checkbox {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.form-checkbox input {
    width: 18px;
    height: 18px;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: var(--space-md);
    padding: var(--space-lg);
    border-top: 1px solid var(--border-light);
    background: var(--bg);
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
}

/* Error List */
.error-list {
    background: var(--danger-soft);
    border: 1px solid var(--danger);
    color: #721c24;
    padding: var(--space-md);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-lg);
}

.error-list ul {
    margin: 0;
    padding-left: var(--space-lg);
}

.error-list li {
    margin-bottom: var(--space-xs);
}

.error-list li:last-child {
    margin-bottom: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }
}
';

// Include admin header
include BASE_PATH . '/admin/includes/header.php';
?>

            <!-- Page Header -->
            <div class="admin-page-header">
                <h1 class="admin-page-title"><?php echo $isEditMode ? 'Edit Calculator' : 'Add Calculator'; ?></h1>
                <p class="admin-page-subtitle">
                    <?php echo $isEditMode ? 'Update calculator details' : 'Create a new calculator'; ?>
                </p>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
            <div class="error-list">
                <ul>
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Calculator Form -->
            <form method="POST" id="calculatorForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="calculator_id" value="<?php echo $calculatorId; ?>">

                <!-- Basic Info Section -->
                <div class="form-card">
                    <div class="form-card-header">
                        <h2 class="form-card-title">Basic Information</h2>
                    </div>
                    <div class="form-card-body">
                        <!-- Category -->
                        <div class="form-group">
                            <label class="form-label" for="category_id">
                                Category <span class="required">*</span>
                            </label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select a category...</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                        <?php echo $formData['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['icon'] . ' ' . $cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Name -->
                        <div class="form-group">
                            <label class="form-label" for="name">
                                Calculator Name <span class="required">*</span>
                            </label>
                            <input type="text" class="form-input" id="name" name="name"
                                   required maxlength="100"
                                   value="<?php echo htmlspecialchars($formData['name']); ?>"
                                   placeholder="e.g., BMI Calculator"
                                   oninput="autoGenerateSlug(this.value)">
                        </div>

                        <!-- Slug -->
                        <div class="form-group">
                            <label class="form-label" for="slug">
                                URL Slug <span class="required">*</span>
                                <button type="button" style="font-size: var(--text-xs); margin-left: var(--space-sm); padding: 2px 6px; cursor: pointer;"
                                        onclick="generateSlugFromName()">Auto-generate</button>
                            </label>
                            <input type="text" class="form-input" id="slug" name="slug"
                                   required maxlength="100" pattern="[a-z0-9-]+"
                                   value="<?php echo htmlspecialchars($formData['slug']); ?>"
                                   placeholder="e.g., bmi-calculator">
                            <div class="form-hint">Lowercase letters, numbers, and hyphens only. This will be the URL: /calculators/<strong id="slugPreview"><?php echo htmlspecialchars($formData['slug'] ?: 'your-slug'); ?></strong></div>
                        </div>

                        <!-- Short Description -->
                        <div class="form-group">
                            <label class="form-label" for="short_desc">Short Description</label>
                            <textarea class="form-textarea" id="short_desc" name="short_desc"
                                      rows="3" maxlength="255"
                                      placeholder="Brief description for listings and search results"><?php echo htmlspecialchars($formData['short_desc']); ?></textarea>
                            <div class="form-hint">Max 255 characters. Used in category listings.</div>
                        </div>

                        <!-- JS File Path -->
                        <div class="form-group">
                            <label class="form-label" for="js_file">JavaScript File Path</label>
                            <input type="text" class="form-input" id="js_file" name="js_file"
                                   value="<?php echo htmlspecialchars($formData['js_file']); ?>"
                                   placeholder="e.g., /assets/js/calculators/bmi.js">
                            <div class="form-hint">Path to the calculator's JavaScript file</div>
                        </div>
                    </div>
                </div>

                <!-- Status Section -->
                <div class="form-card">
                    <div class="form-card-header">
                        <h2 class="form-card-title">Status & Visibility</h2>
                    </div>
                    <div class="form-card-body">
                        <!-- Active -->
                        <div class="form-switch">
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_active" id="is_active"
                                       <?php echo $formData['is_active'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="switch-label">Active (visible on site)</span>
                        </div>

                        <!-- Indexed -->
                        <div class="form-switch">
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_indexed" id="is_indexed"
                                       <?php echo $formData['is_indexed'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="switch-label">Allow search engine indexing</span>
                        </div>

                        <!-- Show in Sitemap -->
                        <div class="form-switch">
                            <label class="toggle-switch">
                                <input type="checkbox" name="show_in_sitemap" id="show_in_sitemap"
                                       <?php echo $formData['show_in_sitemap'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="switch-label">Include in XML sitemap</span>
                        </div>
                    </div>
                </div>

                <!-- Ad Settings Section -->
                <div class="form-card">
                    <div class="form-card-header">
                        <h2 class="form-card-title">Ad Settings</h2>
                    </div>
                    <div class="form-card-body">
                        <!-- Ad Layout -->
                        <div class="form-group">
                            <label class="form-label" for="ad_layout">Ad Density</label>
                            <select class="form-select" id="ad_layout" name="ad_layout">
                                <option value="low" <?php echo $formData['ad_layout'] === 'low' ? 'selected' : ''; ?>>
                                    Low (fewer ads)
                                </option>
                                <option value="medium" <?php echo $formData['ad_layout'] === 'medium' ? 'selected' : ''; ?>>
                                    Medium (balanced)
                                </option>
                                <option value="high" <?php echo $formData['ad_layout'] === 'high' ? 'selected' : ''; ?>>
                                    High (more ads)
                                </option>
                            </select>
                            <div class="form-hint">Controls the number of ad slots on this calculator page</div>
                        </div>

                        <!-- Disable Ads -->
                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="disable_ads" id="disable_ads"
                                       <?php echo $formData['disable_ads'] ? 'checked' : ''; ?>>
                                <span>Disable all ads on this calculator</span>
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $isEditMode ? 'Update Calculator' : 'Create Calculator'; ?>
                        </button>
                        <a href="/admin/calculators/" class="btn" style="background: var(--bg);">Cancel</a>
                    </div>
                </div>
            </form>

<?php
// Extra scripts for this page
$extraScripts = "
// Auto-generate slug from name
var autoSlug = " . ($isEditMode ? 'false' : 'true') . ";

function autoGenerateSlug(name) {
    if (!autoSlug) return;

    var slug = name
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\\s-]/g, '')
        .replace(/[\\s_]+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+|-+\$/g, '');

    document.getElementById('slug').value = slug;
    updateSlugPreview(slug);
}

function generateSlugFromName() {
    var name = document.getElementById('name').value;
    autoSlug = true;
    autoGenerateSlug(name);
}

function updateSlugPreview(slug) {
    document.getElementById('slugPreview').textContent = slug || 'your-slug';
}

// Disable auto-slug when user manually edits slug
document.getElementById('slug').addEventListener('input', function() {
    autoSlug = false;
    updateSlugPreview(this.value);
});

// Update preview on page load
updateSlugPreview(document.getElementById('slug').value);
";

// Include admin footer
include BASE_PATH . '/admin/includes/footer.php';
?>

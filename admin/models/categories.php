<?php
/**
 * Categories Model
 *
 * Calculator categories ke liye CRUD operations
 * Yeh file db_ helper functions use karti hai
 *
 * Usage:
 *   require_once BASE_PATH . '/admin/models/categories.php';
 *   $categories = get_all_categories();
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    die('Direct access not allowed');
}

// Ensure db functions are available
require_once BASE_PATH . '/includes/db.php';


// ============================================================================
// READ FUNCTIONS
// ============================================================================

/**
 * Get All Active Categories
 *
 * Saari active categories return karta hai sorted by sort_order
 * Admin listing aur frontend dono ke liye use hota hai
 *
 * @param bool $activeOnly  Only return active categories (default: true)
 * @return array            Array of category records
 *
 * Usage:
 *   // Get only active categories
 *   $categories = get_all_categories();
 *
 *   // Get all categories including inactive (for admin)
 *   $categories = get_all_categories(false);
 */
function get_all_categories(bool $activeOnly = true): array
{
    if ($activeOnly) {
        return db_fetch_all(
            "SELECT * FROM calculator_categories
             WHERE is_active = 1
             ORDER BY sort_order ASC, name ASC"
        );
    }

    return db_fetch_all(
        "SELECT * FROM calculator_categories
         ORDER BY sort_order ASC, name ASC"
    );
}

/**
 * Get Category by ID
 *
 * Single category fetch karta hai by ID
 * Admin edit page ke liye
 *
 * @param int $id  Category ID
 * @return array|false  Category data or false if not found
 *
 * Usage:
 *   $category = get_category_by_id(5);
 *   if ($category) {
 *       echo $category['name'];
 *   }
 */
function get_category_by_id(int $id)
{
    return db_fetch_one(
        "SELECT * FROM calculator_categories WHERE id = ?",
        [$id]
    );
}

/**
 * Get Category by Slug
 *
 * Category fetch karta hai by slug
 * Public side URLs ke liye (e.g., /categories/finance)
 *
 * @param string $slug       Category slug
 * @param bool   $activeOnly Only return if active (default: true)
 * @return array|false       Category data or false if not found
 *
 * Usage:
 *   $category = get_category_by_slug('finance');
 *   if ($category) {
 *       echo $category['name'];
 *   }
 */
function get_category_by_slug(string $slug, bool $activeOnly = true)
{
    if ($activeOnly) {
        return db_fetch_one(
            "SELECT * FROM calculator_categories
             WHERE slug = ? AND is_active = 1",
            [$slug]
        );
    }

    return db_fetch_one(
        "SELECT * FROM calculator_categories WHERE slug = ?",
        [$slug]
    );
}

/**
 * Get Categories with Calculator Count
 *
 * Categories ke saath unke calculators ka count return karta hai
 * Homepage aur category listing ke liye
 *
 * @param bool $activeOnly  Only active categories
 * @return array            Categories with 'calculator_count' field
 *
 * Usage:
 *   $categories = get_categories_with_count();
 *   foreach ($categories as $cat) {
 *       echo $cat['name'] . ' (' . $cat['calculator_count'] . ')';
 *   }
 */
function get_categories_with_count(bool $activeOnly = true): array
{
    $whereClause = $activeOnly ? 'WHERE c.is_active = 1' : '';
    $calcWhere = $activeOnly ? 'AND calc.is_active = 1' : '';

    return db_fetch_all(
        "SELECT c.*,
                COUNT(calc.id) AS calculator_count
         FROM calculator_categories c
         LEFT JOIN calculators calc ON c.id = calc.category_id {$calcWhere}
         {$whereClause}
         GROUP BY c.id
         ORDER BY c.sort_order ASC, c.name ASC"
    );
}

/**
 * Check if Category Slug Exists
 *
 * Duplicate slug check karta hai
 * Create/update se pehle validation ke liye
 *
 * @param string   $slug       Slug to check
 * @param int|null $excludeId  Category ID to exclude (for updates)
 * @return bool                True if slug exists
 *
 * Usage:
 *   // Check for new category
 *   if (category_slug_exists('finance')) {
 *       echo "Slug already taken";
 *   }
 *
 *   // Check for update (exclude current category)
 *   if (category_slug_exists('finance', 5)) {
 *       echo "Slug already taken by another category";
 *   }
 */
function category_slug_exists(string $slug, ?int $excludeId = null): bool
{
    if ($excludeId) {
        return db_exists(
            'calculator_categories',
            'slug = ? AND id != ?',
            [$slug, $excludeId]
        );
    }

    return db_exists('calculator_categories', 'slug = ?', [$slug]);
}


// ============================================================================
// CREATE FUNCTION
// ============================================================================

/**
 * Create New Category
 *
 * Nayi category database me insert karta hai
 * Slug auto-generate hota hai agar nahi diya
 *
 * @param array $data  Category data [name, slug, icon, short_desc, sort_order, is_active]
 * @return int         New category ID
 * @throws Exception   If validation fails or slug exists
 *
 * Usage:
 *   $newId = create_category([
 *       'name' => 'Finance',
 *       'slug' => 'finance',
 *       'icon' => '💰',
 *       'short_desc' => 'Financial calculators',
 *       'sort_order' => 1,
 *       'is_active' => 1
 *   ]);
 */
function create_category(array $data): int
{
    // Validate required fields
    if (empty($data['name'])) {
        throw new Exception('Category name is required');
    }

    // Generate slug if not provided
    if (empty($data['slug'])) {
        $data['slug'] = generate_slug($data['name']);
    }

    // Clean slug
    $data['slug'] = sanitize_slug($data['slug']);

    // Check if slug already exists
    if (category_slug_exists($data['slug'])) {
        throw new Exception('Category slug already exists');
    }

    // Set defaults
    $insertData = [
        'name' => trim($data['name']),
        'slug' => $data['slug'],
        'icon' => $data['icon'] ?? null,
        'short_desc' => $data['short_desc'] ?? null,
        'sort_order' => $data['sort_order'] ?? 0,
        'is_active' => $data['is_active'] ?? 1
    ];

    return db_insert_array('calculator_categories', $insertData);
}


// ============================================================================
// UPDATE FUNCTION
// ============================================================================

/**
 * Update Category
 *
 * Existing category ko update karta hai
 *
 * @param int   $id    Category ID
 * @param array $data  Updated data
 * @return int         Number of affected rows (0 or 1)
 * @throws Exception   If validation fails or category not found
 *
 * Usage:
 *   $updated = update_category(5, [
 *       'name' => 'Updated Name',
 *       'icon' => '🆕',
 *       'is_active' => 1
 *   ]);
 */
function update_category(int $id, array $data): int
{
    // Check if category exists
    $existing = get_category_by_id($id);
    if (!$existing) {
        throw new Exception('Category not found');
    }

    // Validate name if provided
    if (isset($data['name']) && empty($data['name'])) {
        throw new Exception('Category name cannot be empty');
    }

    // Handle slug update
    if (isset($data['slug'])) {
        $data['slug'] = sanitize_slug($data['slug']);

        // Check if new slug already exists (excluding current category)
        if (category_slug_exists($data['slug'], $id)) {
            throw new Exception('Category slug already exists');
        }
    }

    // Build update data (only include provided fields)
    $updateData = [];

    if (isset($data['name'])) {
        $updateData['name'] = trim($data['name']);
    }

    if (isset($data['slug'])) {
        $updateData['slug'] = $data['slug'];
    }

    if (array_key_exists('icon', $data)) {
        $updateData['icon'] = $data['icon'];
    }

    if (array_key_exists('short_desc', $data)) {
        $updateData['short_desc'] = $data['short_desc'];
    }

    if (isset($data['sort_order'])) {
        $updateData['sort_order'] = (int) $data['sort_order'];
    }

    if (isset($data['is_active'])) {
        $updateData['is_active'] = (int) $data['is_active'];
    }

    // Nothing to update
    if (empty($updateData)) {
        return 0;
    }

    return db_update_array('calculator_categories', $updateData, 'id = ?', [$id]);
}


// ============================================================================
// DELETE FUNCTION
// ============================================================================

/**
 * Delete Category (Soft Delete)
 *
 * Category ko soft delete karta hai (is_active = 0)
 * Hard delete nahi karta kyunki calculators orphan ho jayenge
 *
 * @param int $id  Category ID
 * @return int     Number of affected rows
 * @throws Exception If category not found or has calculators
 *
 * Usage:
 *   // Soft delete
 *   $deleted = delete_category(5);
 *
 *   // Force delete (even with calculators)
 *   $deleted = delete_category(5, true);
 */
function delete_category(int $id, bool $force = false): int
{
    // Check if category exists
    $existing = get_category_by_id($id);
    if (!$existing) {
        throw new Exception('Category not found');
    }

    // Check for calculators in this category
    if (!$force) {
        $calculatorCount = db_count('calculators', 'category_id = ?', [$id]);
        if ($calculatorCount > 0) {
            throw new Exception("Cannot delete category: {$calculatorCount} calculators are linked to it");
        }
    }

    // Soft delete (set is_active = 0)
    return db_update_array(
        'calculator_categories',
        ['is_active' => 0],
        'id = ?',
        [$id]
    );
}

/**
 * Hard Delete Category
 *
 * Category ko permanently delete karta hai
 * WARNING: Use with caution, this cannot be undone!
 *
 * @param int $id  Category ID
 * @return int     Number of affected rows
 * @throws Exception If category not found or has calculators
 *
 * Usage:
 *   $deleted = hard_delete_category(5);
 */
function hard_delete_category(int $id): int
{
    // Check if category exists
    $existing = get_category_by_id($id);
    if (!$existing) {
        throw new Exception('Category not found');
    }

    // Check for calculators
    $calculatorCount = db_count('calculators', 'category_id = ?', [$id]);
    if ($calculatorCount > 0) {
        throw new Exception("Cannot delete category: {$calculatorCount} calculators are linked to it. Reassign or delete them first.");
    }

    return db_execute("DELETE FROM calculator_categories WHERE id = ?", [$id]);
}

/**
 * Restore Deleted Category
 *
 * Soft deleted category ko restore karta hai
 *
 * @param int $id  Category ID
 * @return int     Number of affected rows
 *
 * Usage:
 *   $restored = restore_category(5);
 */
function restore_category(int $id): int
{
    return db_update_array(
        'calculator_categories',
        ['is_active' => 1],
        'id = ?',
        [$id]
    );
}


// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Generate Slug from Name
 *
 * Name se URL-friendly slug banata hai
 *
 * @param string $name  Category name
 * @return string       URL-friendly slug
 *
 * Usage:
 *   $slug = generate_slug('Finance & Banking');
 *   // Returns: 'finance-banking'
 */
function generate_slug(string $name): string
{
    // Convert to lowercase
    $slug = strtolower($name);

    // Replace non-alphanumeric characters with hyphens
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

    // Remove leading/trailing hyphens
    $slug = trim($slug, '-');

    // Remove multiple consecutive hyphens
    $slug = preg_replace('/-+/', '-', $slug);

    return $slug;
}

/**
 * Sanitize Slug
 *
 * Slug ko clean karta hai
 *
 * @param string $slug  Input slug
 * @return string       Cleaned slug
 */
function sanitize_slug(string $slug): string
{
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Get Next Sort Order
 *
 * Next available sort_order value return karta hai
 * New category create karte waqt use hota hai
 *
 * @return int  Next sort order
 *
 * Usage:
 *   $nextOrder = get_next_category_sort_order();
 */
function get_next_category_sort_order(): int
{
    $maxOrder = db_fetch_value(
        "SELECT MAX(sort_order) FROM calculator_categories"
    );

    return ($maxOrder !== false && $maxOrder !== null) ? $maxOrder + 1 : 0;
}

/**
 * Update Category Sort Orders
 *
 * Multiple categories ka sort_order update karta hai
 * Drag-and-drop reordering ke liye
 *
 * @param array $orders  Array of [id => sort_order]
 * @return int           Number of updated categories
 *
 * Usage:
 *   $updated = update_category_sort_orders([
 *       1 => 0,
 *       3 => 1,
 *       2 => 2,
 *       5 => 3
 *   ]);
 */
function update_category_sort_orders(array $orders): int
{
    $updated = 0;

    db_begin();
    try {
        foreach ($orders as $id => $sortOrder) {
            $affected = db_execute(
                "UPDATE calculator_categories SET sort_order = ? WHERE id = ?",
                [(int) $sortOrder, (int) $id]
            );
            $updated += $affected;
        }
        db_commit();

    } catch (Exception $e) {
        db_rollback();
        throw $e;
    }

    return $updated;
}

/**
 * Get Category Options for Dropdown
 *
 * Dropdown select ke liye categories return karta hai
 * Format: [id => name]
 *
 * @param bool $activeOnly  Only active categories
 * @return array            [id => name] pairs
 *
 * Usage:
 *   $options = get_category_options();
 *   // Returns: [1 => 'Finance', 2 => 'Health', ...]
 *
 *   // Use in HTML:
 *   <select name="category_id">
 *       <?php foreach ($options as $id => $name): ?>
 *           <option value="<?= $id ?>"><?= $name ?></option>
 *       <?php endforeach; ?>
 *   </select>
 */
function get_category_options(bool $activeOnly = true): array
{
    $categories = get_all_categories($activeOnly);
    $options = [];

    foreach ($categories as $category) {
        $options[$category['id']] = $category['name'];
    }

    return $options;
}


// ============================================================================
// VALIDATION FUNCTIONS
// ============================================================================

/**
 * Validate Category Data
 *
 * Category data ko validate karta hai
 * Returns array of error messages (empty if valid)
 *
 * @param array    $data       Category data to validate
 * @param int|null $excludeId  Category ID to exclude (for updates)
 * @return array               Array of error messages
 *
 * Usage:
 *   $errors = validate_category_data($data);
 *   if (!empty($errors)) {
 *       foreach ($errors as $error) {
 *           echo $error;
 *       }
 *   }
 */
function validate_category_data(array $data, ?int $excludeId = null): array
{
    $errors = [];

    // Name validation
    if (empty($data['name'])) {
        $errors[] = 'Category name is required';
    } elseif (strlen($data['name']) > 100) {
        $errors[] = 'Category name must be less than 100 characters';
    }

    // Slug validation
    if (!empty($data['slug'])) {
        $slug = sanitize_slug($data['slug']);

        if (strlen($slug) > 100) {
            $errors[] = 'Slug must be less than 100 characters';
        }

        if (category_slug_exists($slug, $excludeId)) {
            $errors[] = 'This slug is already in use';
        }
    }

    // Icon validation
    if (isset($data['icon']) && strlen($data['icon']) > 50) {
        $errors[] = 'Icon must be less than 50 characters';
    }

    // Short description validation
    if (isset($data['short_desc']) && strlen($data['short_desc']) > 255) {
        $errors[] = 'Short description must be less than 255 characters';
    }

    // Sort order validation
    if (isset($data['sort_order']) && !is_numeric($data['sort_order'])) {
        $errors[] = 'Sort order must be a number';
    }

    return $errors;
}

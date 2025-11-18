<?php
/**
 * Calculators Model
 *
 * Calculator pages ke liye CRUD operations
 * Yeh file db_ helper functions use karti hai
 *
 * Usage:
 *   require_once BASE_PATH . '/admin/models/calculators.php';
 *   $calculator = get_calculator_by_slug('bmi');
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
 * Get Calculator by Slug
 *
 * Public pages ke liye - URL se calculator fetch karta hai
 * Category information bhi include hoti hai
 *
 * @param string $slug       Calculator slug
 * @param bool   $activeOnly Only return if active (default: true)
 * @return array|false       Calculator data with category info or false
 *
 * Usage:
 *   $calc = get_calculator_by_slug('bmi');
 *   if ($calc) {
 *       echo $calc['name'];
 *       echo $calc['category_name'];
 *   }
 */
function get_calculator_by_slug(string $slug, bool $activeOnly = true)
{
    $whereClause = $activeOnly ? 'AND c.is_active = 1' : '';

    return db_fetch_one(
        "SELECT c.*,
                cat.name AS category_name,
                cat.slug AS category_slug,
                cat.icon AS category_icon
         FROM calculators c
         LEFT JOIN calculator_categories cat ON c.category_id = cat.id
         WHERE c.slug = ? {$whereClause}",
        [$slug]
    );
}

/**
 * Get Calculator by ID
 *
 * Admin edit page ke liye
 *
 * @param int $id  Calculator ID
 * @return array|false  Calculator data or false
 *
 * Usage:
 *   $calc = get_calculator_by_id(5);
 *   if ($calc) {
 *       echo $calc['name'];
 *   }
 */
function get_calculator_by_id(int $id)
{
    return db_fetch_one(
        "SELECT c.*,
                cat.name AS category_name,
                cat.slug AS category_slug
         FROM calculators c
         LEFT JOIN calculator_categories cat ON c.category_id = cat.id
         WHERE c.id = ?",
        [$id]
    );
}

/**
 * Get All Calculators
 *
 * Admin listing ke liye saare calculators
 *
 * @param bool $activeOnly  Only active calculators
 * @param int  $limit       Limit results (0 = no limit)
 * @param int  $offset      Offset for pagination
 * @return array            Array of calculators
 *
 * Usage:
 *   // Get all active
 *   $calculators = get_all_calculators();
 *
 *   // Get with pagination
 *   $calculators = get_all_calculators(true, 20, 0);
 */
function get_all_calculators(bool $activeOnly = true, int $limit = 0, int $offset = 0): array
{
    $whereClause = $activeOnly ? 'WHERE c.is_active = 1' : '';
    $limitClause = $limit > 0 ? "LIMIT {$offset}, {$limit}" : '';

    return db_fetch_all(
        "SELECT c.*,
                cat.name AS category_name,
                cat.slug AS category_slug
         FROM calculators c
         LEFT JOIN calculator_categories cat ON c.category_id = cat.id
         {$whereClause}
         ORDER BY c.name ASC
         {$limitClause}"
    );
}

/**
 * Get Calculators by Category
 *
 * Category page ke liye - ek category ke saare calculators
 *
 * @param int  $categoryId  Category ID
 * @param bool $activeOnly  Only active calculators
 * @return array            Array of calculators
 *
 * Usage:
 *   $calculators = get_calculators_by_category(2);
 *   foreach ($calculators as $calc) {
 *       echo $calc['name'];
 *   }
 */
function get_calculators_by_category(int $categoryId, bool $activeOnly = true): array
{
    $whereClause = $activeOnly ? 'AND c.is_active = 1' : '';

    return db_fetch_all(
        "SELECT c.*,
                cat.name AS category_name,
                cat.slug AS category_slug
         FROM calculators c
         LEFT JOIN calculator_categories cat ON c.category_id = cat.id
         WHERE c.category_id = ? {$whereClause}
         ORDER BY c.name ASC",
        [$categoryId]
    );
}

/**
 * Get Calculators by Category Slug
 *
 * Public category pages ke liye
 *
 * @param string $categorySlug  Category slug
 * @param bool   $activeOnly    Only active
 * @return array                Array of calculators
 *
 * Usage:
 *   $calculators = get_calculators_by_category_slug('finance');
 */
function get_calculators_by_category_slug(string $categorySlug, bool $activeOnly = true): array
{
    $whereClause = $activeOnly ? 'AND c.is_active = 1 AND cat.is_active = 1' : '';

    return db_fetch_all(
        "SELECT c.*,
                cat.name AS category_name,
                cat.slug AS category_slug
         FROM calculators c
         INNER JOIN calculator_categories cat ON c.category_id = cat.id
         WHERE cat.slug = ? {$whereClause}
         ORDER BY c.name ASC",
        [$categorySlug]
    );
}

/**
 * Search Calculators
 *
 * Keyword se calculators search karta hai
 * Name, description, aur keywords me search hota hai
 *
 * @param string $keyword    Search keyword
 * @param bool   $activeOnly Only active calculators
 * @param int    $limit      Max results
 * @return array             Matching calculators
 *
 * Usage:
 *   $results = search_calculators('loan');
 *   $results = search_calculators('bmi calculator', true, 10);
 */
function search_calculators(string $keyword, bool $activeOnly = true, int $limit = 20): array
{
    $keyword = trim($keyword);
    if (empty($keyword)) {
        return [];
    }

    $whereClause = $activeOnly ? 'AND c.is_active = 1' : '';
    $searchTerm = '%' . $keyword . '%';

    return db_fetch_all(
        "SELECT c.*,
                cat.name AS category_name,
                cat.slug AS category_slug
         FROM calculators c
         LEFT JOIN calculator_categories cat ON c.category_id = cat.id
         WHERE (
             c.name LIKE ? OR
             c.short_desc LIKE ? OR
             c.primary_keyword LIKE ? OR
             c.secondary_keywords LIKE ? OR
             c.meta_title LIKE ?
         ) {$whereClause}
         ORDER BY
             CASE
                 WHEN c.name LIKE ? THEN 1
                 WHEN c.primary_keyword LIKE ? THEN 2
                 ELSE 3
             END,
             c.name ASC
         LIMIT ?",
        [
            $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm,
            $searchTerm, $searchTerm,
            $limit
        ]
    );
}

/**
 * Get Popular Calculators
 *
 * Most viewed/used calculators return karta hai
 * Abhi simple implementation - later stats table se join karenge
 *
 * @param int  $limit      Number of calculators
 * @param bool $activeOnly Only active
 * @return array           Popular calculators
 *
 * Usage:
 *   $popular = get_popular_calculators(6);
 */
function get_popular_calculators(int $limit = 10, bool $activeOnly = true): array
{
    $whereClause = $activeOnly ? 'WHERE c.is_active = 1' : '';

    // TODO: Later me stats table se join karke actual popularity calculate karenge
    // Abhi ke liye random ya first N calculators return kar rahe hain

    return db_fetch_all(
        "SELECT c.*,
                cat.name AS category_name,
                cat.slug AS category_slug,
                COALESCE(
                    (SELECT COUNT(*) FROM calculator_stats s
                     WHERE s.calculator_id = c.id AND s.event_type = 'view'),
                    0
                ) AS view_count
         FROM calculators c
         LEFT JOIN calculator_categories cat ON c.category_id = cat.id
         {$whereClause}
         ORDER BY view_count DESC, c.created_at DESC
         LIMIT ?",
        [$limit]
    );
}

/**
 * Get Recent Calculators
 *
 * Recently added calculators
 *
 * @param int  $limit      Number of calculators
 * @param bool $activeOnly Only active
 * @return array           Recent calculators
 *
 * Usage:
 *   $recent = get_recent_calculators(5);
 */
function get_recent_calculators(int $limit = 10, bool $activeOnly = true): array
{
    $whereClause = $activeOnly ? 'WHERE c.is_active = 1' : '';

    return db_fetch_all(
        "SELECT c.*,
                cat.name AS category_name,
                cat.slug AS category_slug
         FROM calculators c
         LEFT JOIN calculator_categories cat ON c.category_id = cat.id
         {$whereClause}
         ORDER BY c.created_at DESC
         LIMIT ?",
        [$limit]
    );
}

/**
 * Get Calculators for Sitemap
 *
 * XML sitemap generation ke liye
 *
 * @return array  Calculators with slug and updated_at
 *
 * Usage:
 *   $urls = get_calculators_for_sitemap();
 */
function get_calculators_for_sitemap(): array
{
    return db_fetch_all(
        "SELECT slug, updated_at
         FROM calculators
         WHERE is_active = 1 AND show_in_sitemap = 1
         ORDER BY updated_at DESC"
    );
}

/**
 * Check if Calculator Slug Exists
 *
 * @param string   $slug       Slug to check
 * @param int|null $excludeId  ID to exclude (for updates)
 * @return bool
 */
function calculator_slug_exists(string $slug, ?int $excludeId = null): bool
{
    if ($excludeId) {
        return db_exists(
            'calculators',
            'slug = ? AND id != ?',
            [$slug, $excludeId]
        );
    }

    return db_exists('calculators', 'slug = ?', [$slug]);
}

/**
 * Get Related Calculators
 *
 * Same category ke related calculators
 *
 * @param int $calculatorId  Current calculator ID
 * @param int $limit         Number of related
 * @return array             Related calculators
 *
 * Usage:
 *   $related = get_related_calculators(5, 4);
 */
function get_related_calculators(int $calculatorId, int $limit = 4): array
{
    // Get current calculator's category
    $calc = get_calculator_by_id($calculatorId);
    if (!$calc) {
        return [];
    }

    return db_fetch_all(
        "SELECT c.*,
                cat.name AS category_name,
                cat.slug AS category_slug
         FROM calculators c
         LEFT JOIN calculator_categories cat ON c.category_id = cat.id
         WHERE c.category_id = ? AND c.id != ? AND c.is_active = 1
         ORDER BY RAND()
         LIMIT ?",
        [$calc['category_id'], $calculatorId, $limit]
    );
}


// ============================================================================
// CREATE FUNCTION
// ============================================================================

/**
 * Create New Calculator
 *
 * @param array $data  Calculator data
 * @return int         New calculator ID
 * @throws Exception   If validation fails
 *
 * Usage:
 *   $newId = create_calculator([
 *       'category_id' => 1,
 *       'name' => 'BMI Calculator',
 *       'slug' => 'bmi',
 *       'short_desc' => 'Calculate Body Mass Index',
 *       'meta_title' => 'BMI Calculator - Free Online Tool',
 *       'meta_desc' => 'Calculate your BMI...',
 *       'js_file' => 'bmi.js',
 *       'is_active' => 1
 *   ]);
 */
function create_calculator(array $data): int
{
    // Validate required fields
    if (empty($data['name'])) {
        throw new Exception('Calculator name is required');
    }

    if (empty($data['category_id'])) {
        throw new Exception('Category is required');
    }

    // Generate slug if not provided
    if (empty($data['slug'])) {
        $data['slug'] = generate_calculator_slug($data['name']);
    }

    // Clean slug
    $data['slug'] = sanitize_calculator_slug($data['slug']);

    // Check if slug exists
    if (calculator_slug_exists($data['slug'])) {
        throw new Exception('Calculator slug already exists');
    }

    // Build insert data with defaults
    $insertData = [
        'category_id' => (int) $data['category_id'],
        'name' => trim($data['name']),
        'slug' => $data['slug'],
        'short_desc' => $data['short_desc'] ?? null,
        'meta_title' => $data['meta_title'] ?? null,
        'meta_desc' => $data['meta_desc'] ?? null,
        'h1_title' => $data['h1_title'] ?? null,
        'intro_html' => $data['intro_html'] ?? null,
        'content_html' => $data['content_html'] ?? null,
        'faq_json' => isset($data['faq_json']) ? json_encode($data['faq_json']) : null,
        'schema_json' => isset($data['schema_json']) ? json_encode($data['schema_json']) : null,
        'primary_keyword' => $data['primary_keyword'] ?? null,
        'secondary_keywords' => $data['secondary_keywords'] ?? null,
        'js_file' => $data['js_file'] ?? null,
        'ad_layout' => $data['ad_layout'] ?? 'default',
        'disable_ads' => $data['disable_ads'] ?? 0,
        'is_active' => $data['is_active'] ?? 1,
        'is_indexed' => $data['is_indexed'] ?? 1,
        'show_in_sitemap' => $data['show_in_sitemap'] ?? 1
    ];

    return db_insert_array('calculators', $insertData);
}


// ============================================================================
// UPDATE FUNCTION
// ============================================================================

/**
 * Update Calculator
 *
 * @param int   $id    Calculator ID
 * @param array $data  Updated data
 * @return int         Affected rows
 * @throws Exception   If validation fails
 *
 * Usage:
 *   $updated = update_calculator(5, [
 *       'name' => 'Updated Name',
 *       'meta_title' => 'New Title'
 *   ]);
 */
function update_calculator(int $id, array $data): int
{
    // Check if exists
    $existing = get_calculator_by_id($id);
    if (!$existing) {
        throw new Exception('Calculator not found');
    }

    // Validate name if provided
    if (isset($data['name']) && empty($data['name'])) {
        throw new Exception('Calculator name cannot be empty');
    }

    // Handle slug update
    if (isset($data['slug'])) {
        $data['slug'] = sanitize_calculator_slug($data['slug']);

        if (calculator_slug_exists($data['slug'], $id)) {
            throw new Exception('Calculator slug already exists');
        }
    }

    // Build update data
    $updateData = [];
    $allowedFields = [
        'category_id', 'name', 'slug', 'short_desc',
        'meta_title', 'meta_desc', 'h1_title',
        'intro_html', 'content_html',
        'primary_keyword', 'secondary_keywords',
        'js_file', 'ad_layout', 'disable_ads',
        'is_active', 'is_indexed', 'show_in_sitemap'
    ];

    foreach ($allowedFields as $field) {
        if (array_key_exists($field, $data)) {
            $updateData[$field] = $data[$field];
        }
    }

    // Handle JSON fields
    if (isset($data['faq_json'])) {
        $updateData['faq_json'] = is_array($data['faq_json'])
            ? json_encode($data['faq_json'])
            : $data['faq_json'];
    }

    if (isset($data['schema_json'])) {
        $updateData['schema_json'] = is_array($data['schema_json'])
            ? json_encode($data['schema_json'])
            : $data['schema_json'];
    }

    // Clean specific fields
    if (isset($updateData['name'])) {
        $updateData['name'] = trim($updateData['name']);
    }

    if (isset($updateData['category_id'])) {
        $updateData['category_id'] = (int) $updateData['category_id'];
    }

    // Nothing to update
    if (empty($updateData)) {
        return 0;
    }

    return db_update_array('calculators', $updateData, 'id = ?', [$id]);
}


// ============================================================================
// DELETE & TOGGLE FUNCTIONS
// ============================================================================

/**
 * Toggle Calculator Active Status
 *
 * Calculator ko active/inactive karta hai
 *
 * @param int  $id        Calculator ID
 * @param bool $isActive  New status
 * @return int            Affected rows
 *
 * Usage:
 *   // Deactivate
 *   toggle_calculator_active(5, false);
 *
 *   // Activate
 *   toggle_calculator_active(5, true);
 */
function toggle_calculator_active(int $id, bool $isActive): int
{
    return db_execute(
        "UPDATE calculators SET is_active = ? WHERE id = ?",
        [(int) $isActive, $id]
    );
}

/**
 * Delete Calculator (Soft)
 *
 * @param int $id  Calculator ID
 * @return int     Affected rows
 */
function delete_calculator(int $id): int
{
    return toggle_calculator_active($id, false);
}

/**
 * Hard Delete Calculator
 *
 * Permanently removes calculator
 *
 * @param int $id  Calculator ID
 * @return int     Affected rows
 * @throws Exception If calculator not found
 */
function hard_delete_calculator(int $id): int
{
    $existing = get_calculator_by_id($id);
    if (!$existing) {
        throw new Exception('Calculator not found');
    }

    // Delete stats first (foreign key)
    db_execute("DELETE FROM calculator_stats WHERE calculator_id = ?", [$id]);

    // Delete calculator
    return db_execute("DELETE FROM calculators WHERE id = ?", [$id]);
}

/**
 * Restore Calculator
 *
 * @param int $id  Calculator ID
 * @return int     Affected rows
 */
function restore_calculator(int $id): int
{
    return toggle_calculator_active($id, true);
}


// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Generate Slug from Name
 *
 * @param string $name  Calculator name
 * @return string       URL-friendly slug
 */
function generate_calculator_slug(string $name): string
{
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = preg_replace('/-+/', '-', $slug);
    return $slug;
}

/**
 * Sanitize Slug
 *
 * @param string $slug  Input slug
 * @return string       Cleaned slug
 */
function sanitize_calculator_slug(string $slug): string
{
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Get Calculator Count
 *
 * @param int|null $categoryId  Filter by category (optional)
 * @param bool     $activeOnly  Only active
 * @return int                  Count
 *
 * Usage:
 *   $total = get_calculator_count();
 *   $inCategory = get_calculator_count(2);
 */
function get_calculator_count(?int $categoryId = null, bool $activeOnly = true): int
{
    $where = '1=1';
    $params = [];

    if ($activeOnly) {
        $where .= ' AND is_active = 1';
    }

    if ($categoryId !== null) {
        $where .= ' AND category_id = ?';
        $params[] = $categoryId;
    }

    return db_count('calculators', $where, $params);
}

/**
 * Increment Calculator Views
 *
 * View count increase karta hai (stats table me)
 *
 * @param int    $calculatorId  Calculator ID
 * @param string $userAgent     Browser user agent
 * @param string $ipHash        Hashed IP address
 * @return int                  New stat ID
 *
 * Usage:
 *   increment_calculator_views(5, $_SERVER['HTTP_USER_AGENT'], hash('sha256', $_SERVER['REMOTE_ADDR']));
 */
function increment_calculator_views(int $calculatorId, string $userAgent = '', string $ipHash = ''): int
{
    return db_insert_array('calculator_stats', [
        'calculator_id' => $calculatorId,
        'event_type' => 'view',
        'user_agent' => substr($userAgent, 0, 500),
        'ip_hash' => $ipHash
    ]);
}

/**
 * Log Calculator Usage
 *
 * Calculation event log karta hai
 *
 * @param int    $calculatorId  Calculator ID
 * @param string $eventType     Event type (calculate, share, print)
 * @return int                  New stat ID
 */
function log_calculator_usage(int $calculatorId, string $eventType = 'calculate'): int
{
    $allowedEvents = ['view', 'calculate', 'share', 'print'];
    if (!in_array($eventType, $allowedEvents)) {
        $eventType = 'calculate';
    }

    return db_insert_array('calculator_stats', [
        'calculator_id' => $calculatorId,
        'event_type' => $eventType,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'ip_hash' => hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '')
    ]);
}

/**
 * Get Calculator Stats
 *
 * Calculator ke usage stats return karta hai
 *
 * @param int $calculatorId  Calculator ID
 * @param int $days          Number of days to look back
 * @return array             Stats data
 *
 * Usage:
 *   $stats = get_calculator_stats(5, 30);
 *   echo "Views: " . $stats['views'];
 */
function get_calculator_stats(int $calculatorId, int $days = 30): array
{
    $stats = db_fetch_one(
        "SELECT
            COUNT(*) AS total_events,
            SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) AS views,
            SUM(CASE WHEN event_type = 'calculate' THEN 1 ELSE 0 END) AS calculations,
            SUM(CASE WHEN event_type = 'share' THEN 1 ELSE 0 END) AS shares
         FROM calculator_stats
         WHERE calculator_id = ?
           AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
        [$calculatorId, $days]
    );

    return $stats ?: [
        'total_events' => 0,
        'views' => 0,
        'calculations' => 0,
        'shares' => 0
    ];
}

/**
 * Duplicate Calculator
 *
 * Existing calculator ki copy banata hai
 *
 * @param int    $id       Calculator ID to duplicate
 * @param string $newSlug  New slug for the copy
 * @return int             New calculator ID
 * @throws Exception       If calculator not found
 *
 * Usage:
 *   $newId = duplicate_calculator(5, 'bmi-v2');
 */
function duplicate_calculator(int $id, string $newSlug): int
{
    $original = get_calculator_by_id($id);
    if (!$original) {
        throw new Exception('Calculator not found');
    }

    // Check new slug
    if (calculator_slug_exists($newSlug)) {
        throw new Exception('New slug already exists');
    }

    // Prepare data for new calculator
    $newData = [
        'category_id' => $original['category_id'],
        'name' => $original['name'] . ' (Copy)',
        'slug' => $newSlug,
        'short_desc' => $original['short_desc'],
        'meta_title' => $original['meta_title'],
        'meta_desc' => $original['meta_desc'],
        'h1_title' => $original['h1_title'],
        'intro_html' => $original['intro_html'],
        'content_html' => $original['content_html'],
        'faq_json' => $original['faq_json'],
        'schema_json' => $original['schema_json'],
        'primary_keyword' => $original['primary_keyword'],
        'secondary_keywords' => $original['secondary_keywords'],
        'js_file' => $original['js_file'],
        'ad_layout' => $original['ad_layout'],
        'disable_ads' => $original['disable_ads'],
        'is_active' => 0, // Start as inactive
        'is_indexed' => $original['is_indexed'],
        'show_in_sitemap' => $original['show_in_sitemap']
    ];

    return db_insert_array('calculators', $newData);
}


// ============================================================================
// VALIDATION FUNCTIONS
// ============================================================================

/**
 * Validate Calculator Data
 *
 * @param array    $data       Calculator data
 * @param int|null $excludeId  ID to exclude (for updates)
 * @return array               Array of error messages
 *
 * Usage:
 *   $errors = validate_calculator_data($data);
 *   if (!empty($errors)) {
 *       // Show errors
 *   }
 */
function validate_calculator_data(array $data, ?int $excludeId = null): array
{
    $errors = [];

    // Name validation
    if (empty($data['name'])) {
        $errors[] = 'Calculator name is required';
    } elseif (strlen($data['name']) > 150) {
        $errors[] = 'Name must be less than 150 characters';
    }

    // Category validation
    if (empty($data['category_id'])) {
        $errors[] = 'Category is required';
    } elseif (!db_exists('calculator_categories', 'id = ?', [$data['category_id']])) {
        $errors[] = 'Invalid category selected';
    }

    // Slug validation
    if (!empty($data['slug'])) {
        $slug = sanitize_calculator_slug($data['slug']);

        if (strlen($slug) > 150) {
            $errors[] = 'Slug must be less than 150 characters';
        }

        if (calculator_slug_exists($slug, $excludeId)) {
            $errors[] = 'This slug is already in use';
        }
    }

    // Meta title validation
    if (isset($data['meta_title']) && strlen($data['meta_title']) > 70) {
        $errors[] = 'Meta title should be less than 70 characters for SEO';
    }

    // Meta description validation
    if (isset($data['meta_desc']) && strlen($data['meta_desc']) > 160) {
        $errors[] = 'Meta description should be less than 160 characters for SEO';
    }

    // JS file validation
    if (!empty($data['js_file'])) {
        if (!preg_match('/^[a-z0-9-]+\.js$/', $data['js_file'])) {
            $errors[] = 'JS file name should be lowercase with .js extension';
        }
    }

    return $errors;
}

/**
 * Get Calculators for Admin List
 *
 * Paginated list with filters for admin panel
 *
 * @param array $filters  [category_id, is_active, search]
 * @param int   $page     Current page
 * @param int   $perPage  Items per page
 * @return array          [data => [...], total => int, pages => int]
 *
 * Usage:
 *   $result = get_calculators_paginated(['category_id' => 2], 1, 20);
 *   $calculators = $result['data'];
 *   $totalPages = $result['pages'];
 */
function get_calculators_paginated(array $filters = [], int $page = 1, int $perPage = 20): array
{
    $where = '1=1';
    $params = [];

    // Apply filters
    if (!empty($filters['category_id'])) {
        $where .= ' AND c.category_id = ?';
        $params[] = $filters['category_id'];
    }

    if (isset($filters['is_active'])) {
        $where .= ' AND c.is_active = ?';
        $params[] = (int) $filters['is_active'];
    }

    if (!empty($filters['search'])) {
        $where .= ' AND (c.name LIKE ? OR c.slug LIKE ?)';
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Get total count
    $total = db_fetch_value(
        "SELECT COUNT(*) FROM calculators c WHERE {$where}",
        $params
    );

    // Calculate pagination
    $pages = ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;

    // Get data
    $data = db_fetch_all(
        "SELECT c.*,
                cat.name AS category_name
         FROM calculators c
         LEFT JOIN calculator_categories cat ON c.category_id = cat.id
         WHERE {$where}
         ORDER BY c.name ASC
         LIMIT {$offset}, {$perPage}",
        $params
    );

    return [
        'data' => $data,
        'total' => (int) $total,
        'pages' => (int) $pages,
        'current_page' => $page,
        'per_page' => $perPage
    ];
}

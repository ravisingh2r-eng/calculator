<?php
/**
 * Public Category Page
 *
 * Displays category information and list of calculators
 *
 * URL: /category.php?slug=finance
 * Or with URL rewrite: /category/finance
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Define base path
define('BASE_PATH', __DIR__);

// Error reporting (development - disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Include database and models
require_once BASE_PATH . '/includes/db.php';
require_once BASE_PATH . '/admin/models/categories.php';
require_once BASE_PATH . '/admin/models/calculators.php';

// Get slug from query string
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// Redirect to home if no slug
if (empty($slug)) {
    header('Location: /');
    exit;
}

// Fetch category by slug (active only)
$category = get_category_by_slug($slug, true);

// 404 if not found or inactive
if (!$category) {
    http_response_code(404);
    $errorTitle = 'Category Not Found';
    $errorMessage = 'The category you\'re looking for doesn\'t exist or has been removed.';
    include BASE_PATH . '/404.php';
    exit;
}

// Get calculators for this category
$calculators = get_calculators_by_category_slug($slug, true);

// SEO data
$pageTitle = $category['name'] . ' Calculators - CalcHub';
$metaDesc = !empty($category['short_desc'])
    ? $category['short_desc']
    : 'Free online ' . strtolower($category['name']) . ' calculators. Easy to use tools for all your calculation needs.';

// Site name for SEO
$siteName = 'CalcHub';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDesc); ?>">


    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">

    <!-- Global Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Page Specific Styles -->
    <style>
        /* Category Header */
        .category-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            padding: var(--space-2xl) 0;
            margin-bottom: var(--space-xl);
        }

        .category-icon {
            font-size: 3rem;
            margin-bottom: var(--space-md);
        }

        .category-title {
            font-size: var(--text-3xl);
            margin-bottom: var(--space-sm);
        }

        .category-desc {
            font-size: var(--text-lg);
            opacity: 0.9;
            max-width: 600px;
        }

        .category-count {
            margin-top: var(--space-md);
            font-size: var(--text-sm);
            opacity: 0.8;
        }

        /* Calculator Grid */
        .calc-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-md);
            margin-bottom: var(--space-xl);
        }

        @media (min-width: 576px) {
            .calc-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 992px) {
            .calc-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1200px) {
            .calc-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Calculator Card */
        .calc-card {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-soft);
            transition: all var(--transition-normal);
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .calc-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-medium);
            color: inherit;
        }

        .calc-card-title {
            font-size: var(--text-lg);
            font-weight: 600;
            margin-bottom: var(--space-sm);
            color: var(--text);
        }

        .calc-card-desc {
            font-size: var(--text-sm);
            color: var(--muted);
            margin: 0;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .calc-card-arrow {
            margin-top: var(--space-md);
            color: var(--accent);
            font-size: var(--text-sm);
            font-weight: 500;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: var(--space-2xl);
            color: var(--muted);
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: var(--space-md);
        }

        .empty-state-title {
            font-size: var(--text-xl);
            margin-bottom: var(--space-sm);
            color: var(--text);
        }

        /* 404 Page */
        .error-page {
            text-align: center;
            padding: var(--space-2xl) 0;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: var(--muted);
            opacity: 0.3;
            line-height: 1;
        }

        .error-title {
            font-size: var(--text-2xl);
            margin-bottom: var(--space-md);
        }

        .error-desc {
            color: var(--muted);
            margin-bottom: var(--space-lg);
        }

        /* Breadcrumb */
        .breadcrumb {
            padding: var(--space-md) 0;
            font-size: var(--text-sm);
        }

        .breadcrumb a {
            color: var(--muted);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: var(--accent);
        }

        .breadcrumb span {
            color: var(--muted);
            margin: 0 var(--space-xs);
        }

        .breadcrumb-current {
            color: var(--text);
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div data-include="/partials/header.html"></div>

    <!-- Category Header -->
    <header class="category-header">
        <div class="container">
            <?php if (!empty($category['icon'])): ?>
            <div class="category-icon"><?php echo htmlspecialchars($category['icon']); ?></div>
            <?php endif; ?>
            <h1 class="category-title"><?php echo htmlspecialchars($category['name']); ?> Calculators</h1>
            <?php if (!empty($category['short_desc'])): ?>
            <p class="category-desc"><?php echo htmlspecialchars($category['short_desc']); ?></p>
            <?php endif; ?>
            <div class="category-count">
                <?php echo count($calculators); ?> calculator<?php echo count($calculators) !== 1 ? 's' : ''; ?> available
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="/">Home</a>
                <span>/</span>
                <span class="breadcrumb-current"><?php echo htmlspecialchars($category['name']); ?></span>
            </nav>

            <?php if (empty($calculators)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">🧮</div>
                <h2 class="empty-state-title">No Calculators Yet</h2>
                <p>We're working on adding calculators to this category. Check back soon!</p>
            </div>

            <?php else: ?>
            <!-- Calculator Grid -->
            <div class="calc-grid">
                <?php foreach ($calculators as $calc): ?>
                <a href="/calculator.php?slug=<?php echo urlencode($calc['slug']); ?>" class="calc-card">
                    <h2 class="calc-card-title"><?php echo htmlspecialchars($calc['name']); ?></h2>
                    <?php if (!empty($calc['short_desc'])): ?>
                    <p class="calc-card-desc"><?php echo htmlspecialchars($calc['short_desc']); ?></p>
                    <?php endif; ?>
                    <div class="calc-card-arrow">Use Calculator →</div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <div data-include="/partials/footer.html"></div>

    <!-- Include Loader Script -->
    <script src="/assets/js/include.js"></script>

</body>
</html>

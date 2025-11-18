<?php
/**
 * Calculator Hub - Homepage
 *
 * Main entry point showing categories and popular calculators.
 * Data is currently hard-coded, will connect to database later.
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Error reporting ON for development (production me OFF karna)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone set karo (India ke liye)
date_default_timezone_set('Asia/Kolkata');

// Session start (future me login ke liye)
session_start();

// Define base path for includes
define('BASE_PATH', __DIR__);

// Config files include karenge (abhi commented hai, baad me enable)
// require_once BASE_PATH . '/config/database.php';
// require_once BASE_PATH . '/includes/db.php';
// require_once BASE_PATH . '/includes/functions.php';

/**
 * DUMMY DATA - Categories
 * Baad me yeh database se aayega
 */
$categories = [
    [
        'id' => 1,
        'name' => 'Finance',
        'slug' => 'finance',
        'icon' => '💰',
        'description' => 'Loan, EMI, Interest, Investment calculators',
        'count' => 15
    ],
    [
        'id' => 2,
        'name' => 'Health & Fitness',
        'slug' => 'health',
        'icon' => '❤️',
        'description' => 'BMI, Calories, Body Fat, Pregnancy calculators',
        'count' => 12
    ],
    [
        'id' => 3,
        'name' => 'Math',
        'slug' => 'math',
        'icon' => '📐',
        'description' => 'Scientific, Percentage, Fraction calculators',
        'count' => 20
    ],
    [
        'id' => 4,
        'name' => 'Date & Time',
        'slug' => 'date-time',
        'icon' => '📅',
        'description' => 'Age, Date Difference, Countdown calculators',
        'count' => 8
    ],
    [
        'id' => 5,
        'name' => 'Conversion',
        'slug' => 'conversion',
        'icon' => '🔄',
        'description' => 'Unit, Currency, Temperature converters',
        'count' => 25
    ],
    [
        'id' => 6,
        'name' => 'Everyday',
        'slug' => 'everyday',
        'icon' => '🏠',
        'description' => 'Tip, Discount, Fuel, Shopping calculators',
        'count' => 10
    ]
];

/**
 * DUMMY DATA - Popular Calculators
 * Baad me yeh database se aayega with view count sorting
 */
$popularCalculators = [
    [
        'id' => 1,
        'name' => 'BMI Calculator',
        'slug' => 'bmi',
        'icon' => '⚖️',
        'description' => 'Calculate your Body Mass Index',
        'category' => 'Health'
    ],
    [
        'id' => 2,
        'name' => 'Loan EMI Calculator',
        'slug' => 'loan-emi',
        'icon' => '🏦',
        'description' => 'Calculate monthly EMI for loans',
        'category' => 'Finance'
    ],
    [
        'id' => 3,
        'name' => 'Age Calculator',
        'slug' => 'age',
        'icon' => '🎂',
        'description' => 'Calculate exact age in years, months, days',
        'category' => 'Date & Time'
    ],
    [
        'id' => 4,
        'name' => 'Percentage Calculator',
        'slug' => 'percentage',
        'icon' => '%',
        'description' => 'Calculate percentages easily',
        'category' => 'Math'
    ],
    [
        'id' => 5,
        'name' => 'GST Calculator',
        'slug' => 'gst',
        'icon' => '🧾',
        'description' => 'Calculate GST amount and total',
        'category' => 'Finance'
    ],
    [
        'id' => 6,
        'name' => 'Calorie Calculator',
        'slug' => 'calorie',
        'icon' => '🍎',
        'description' => 'Calculate daily calorie needs',
        'category' => 'Health'
    ]
];

/**
 * Site Configuration
 */
$siteName = 'CalcHub';
$siteTagline = 'All-in-One Calculator Hub';
$siteDescription = '100+ free online calculators for math, finance, health, and everyday calculations. Fast, accurate, and mobile-friendly.';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($siteTagline); ?> - Free Online Calculators</title>
    <meta name="description" content="<?php echo htmlspecialchars($siteDescription); ?>">
    <meta name="keywords" content="calculator, online calculator, free calculator, BMI calculator, loan calculator, age calculator, percentage calculator">
    <meta name="author" content="<?php echo htmlspecialchars($siteName); ?>">

    <!-- Open Graph Tags (for social sharing) -->
    <meta property="og:title" content="<?php echo htmlspecialchars($siteTagline); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($siteDescription); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://yourdomain.com/">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://yourdomain.com/">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">

    <!-- Global Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Page-specific Styles -->
    <style>
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #ffffff;
            padding: var(--space-2xl) 0;
            text-align: center;
        }

        .hero-title {
            font-size: var(--text-3xl);
            font-weight: 700;
            margin-bottom: var(--space-md);
            color: #ffffff;
        }

        .hero-subtitle {
            font-size: var(--text-lg);
            opacity: 0.9;
            margin-bottom: var(--space-xl);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Hero Search Box */
        .hero-search {
            max-width: 500px;
            margin: 0 auto;
        }

        .hero-search-wrapper {
            display: flex;
            background: #ffffff;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-medium);
        }

        .hero-search-input {
            flex: 1;
            padding: 16px 20px;
            border: none;
            font-size: var(--text-base);
            outline: none;
        }

        .hero-search-input::placeholder {
            color: var(--muted);
        }

        .hero-search-btn {
            padding: 16px 24px;
            background: var(--accent);
            border: none;
            color: #ffffff;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background var(--transition-fast);
        }

        .hero-search-btn:hover {
            background: var(--accent-hover);
        }

        /* Search Results Dropdown */
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #ffffff;
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            box-shadow: var(--shadow-medium);
            display: none;
            max-height: 300px;
            overflow-y: auto;
            z-index: var(--z-dropdown);
        }

        .search-results.active {
            display: block;
        }

        .search-result-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: var(--text);
            border-bottom: 1px solid var(--border-light);
            transition: background var(--transition-fast);
        }

        .search-result-item:hover {
            background: var(--bg);
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-icon {
            margin-right: 12px;
            font-size: 1.2rem;
        }

        .search-result-name {
            font-weight: 500;
        }

        .search-result-category {
            font-size: var(--text-sm);
            color: var(--muted);
            margin-left: auto;
        }

        /* Category Card Specific */
        .category-card {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            text-align: center;
            transition: all var(--transition-normal);
            text-decoration: none;
            color: inherit;
            display: block;
            border: 1px solid var(--border-light);
        }

        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-medium);
            border-color: var(--accent);
            color: inherit;
        }

        .category-card-icon {
            font-size: 2.5rem;
            margin-bottom: var(--space-sm);
            display: block;
        }

        .category-card-title {
            font-size: var(--text-lg);
            font-weight: 600;
            margin-bottom: var(--space-xs);
            color: var(--text);
        }

        .category-card-desc {
            font-size: var(--text-sm);
            color: var(--muted);
            margin-bottom: var(--space-sm);
        }

        .category-card-count {
            font-size: var(--text-xs);
            color: var(--accent);
            font-weight: 600;
        }

        /* Stats Bar */
        .stats-bar {
            display: flex;
            justify-content: center;
            gap: var(--space-xl);
            margin-top: var(--space-xl);
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: var(--text-2xl);
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: var(--text-sm);
            opacity: 0.8;
        }

        /* Responsive */
        @media (min-width: 768px) {
            .hero {
                padding: var(--space-3xl) 0;
            }

            .hero-title {
                font-size: var(--text-4xl);
            }

            .hero-subtitle {
                font-size: var(--text-xl);
            }
        }
    </style>

    <!-- Google AdSense (replace with your code) -->
    <!-- <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-XXXXXXXX" crossorigin="anonymous"></script> -->

    <!-- Google Analytics (replace with your code) -->
    <!-- <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXX"></script> -->
</head>
<body class="page">

    <!-- Header Partial -->
    <div data-include="/partials/header.html"></div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1 class="hero-title"><?php echo htmlspecialchars($siteTagline); ?></h1>
            <p class="hero-subtitle"><?php echo htmlspecialchars($siteDescription); ?></p>

            <!-- Search Box -->
            <div class="hero-search">
                <div class="hero-search-wrapper" style="position: relative;">
                    <input type="search"
                           id="calculatorSearch"
                           class="hero-search-input"
                           placeholder="Search calculators... (e.g., BMI, Loan, Age)"
                           autocomplete="off"
                           aria-label="Search calculators">
                    <button class="hero-search-btn" id="searchBtn" aria-label="Search">
                        🔍
                    </button>

                    <!-- Search Results Dropdown -->
                    <div class="search-results" id="searchResults"></div>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-number">100+</span>
                    <span class="stat-label">Calculators</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">6</span>
                    <span class="stat-label">Categories</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">Free</span>
                    <span class="stat-label">Forever</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="page-content">

        <!-- Categories Section -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">Browse by Category</h2>
                <p class="section-subtitle">Find the right calculator for your needs</p>

                <div class="grid grid-3">
                    <?php foreach ($categories as $category): ?>
                    <a href="/categories/<?php echo htmlspecialchars($category['slug']); ?>" class="category-card">
                        <span class="category-card-icon"><?php echo $category['icon']; ?></span>
                        <h3 class="category-card-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="category-card-desc"><?php echo htmlspecialchars($category['description']); ?></p>
                        <span class="category-card-count"><?php echo $category['count']; ?> calculators</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Ad Slot -->
        <div class="container">
            <div class="ad-slot ad-slot-banner">
                <span class="ad-slot-label">Advertisement</span>
            </div>
        </div>

        <!-- Popular Calculators Section -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">Popular Calculators</h2>
                <p class="section-subtitle">Most used calculators by our visitors</p>

                <div class="grid grid-3">
                    <?php foreach ($popularCalculators as $calc): ?>
                    <a href="/calculator/<?php echo htmlspecialchars($calc['slug']); ?>" class="calc-card">
                        <span class="calc-card-icon"><?php echo $calc['icon']; ?></span>
                        <h3 class="calc-card-title"><?php echo htmlspecialchars($calc['name']); ?></h3>
                        <p class="calc-card-desc"><?php echo htmlspecialchars($calc['description']); ?></p>
                        <span class="badge badge-primary"><?php echo htmlspecialchars($calc['category']); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- View All Button -->
                <div class="text-center mt-lg">
                    <a href="/calculators" class="btn btn-primary btn-lg">
                        View All Calculators
                    </a>
                </div>
            </div>
        </section>

        <!-- Ad Slot -->
        <div class="container">
            <div class="ad-slot ad-slot-rectangle">
                <span class="ad-slot-label">Advertisement</span>
            </div>
        </div>

        <!-- Why Choose Us Section -->
        <section class="section" style="background: var(--bg-alt);">
            <div class="container">
                <h2 class="section-title">Why Choose CalcHub?</h2>

                <div class="grid grid-4">
                    <div class="card text-center">
                        <span style="font-size: 2rem; display: block; margin-bottom: var(--space-sm);">⚡</span>
                        <h4>Fast & Accurate</h4>
                        <p class="text-muted mb-0">Instant results with precise calculations</p>
                    </div>
                    <div class="card text-center">
                        <span style="font-size: 2rem; display: block; margin-bottom: var(--space-sm);">📱</span>
                        <h4>Mobile Friendly</h4>
                        <p class="text-muted mb-0">Works perfectly on all devices</p>
                    </div>
                    <div class="card text-center">
                        <span style="font-size: 2rem; display: block; margin-bottom: var(--space-sm);">🔒</span>
                        <h4>100% Private</h4>
                        <p class="text-muted mb-0">No data stored, calculations in browser</p>
                    </div>
                    <div class="card text-center">
                        <span style="font-size: 2rem; display: block; margin-bottom: var(--space-sm);">💯</span>
                        <h4>Always Free</h4>
                        <p class="text-muted mb-0">No registration or payment required</p>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer Partial -->
    <div data-include="/partials/footer.html"></div>

    <!-- JavaScript Files -->
    <script src="/assets/js/include.js"></script>
    <script src="/assets/js/app.js"></script>

    <!-- Pass PHP data to JavaScript for search -->
    <script>
        // Calculator data for search (from PHP)
        window.calculatorData = <?php echo json_encode(array_merge(
            array_map(function($cat) {
                return [
                    'name' => $cat['name'],
                    'slug' => 'categories/' . $cat['slug'],
                    'icon' => $cat['icon'],
                    'category' => 'Category'
                ];
            }, $categories),
            array_map(function($calc) {
                return [
                    'name' => $calc['name'],
                    'slug' => 'calculator/' . $calc['slug'],
                    'icon' => $calc['icon'],
                    'category' => $calc['category']
                ];
            }, $popularCalculators)
        )); ?>;
    </script>

</body>
</html>

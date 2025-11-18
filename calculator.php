<?php
/**
 * Public Calculator Page
 *
 * Displays individual calculator with all SEO content
 *
 * URL: /calculator.php?slug=bmi
 * Or with URL rewrite: /calculator/bmi
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
require_once BASE_PATH . '/admin/models/calculators.php';
require_once BASE_PATH . '/admin/models/categories.php';

// Get slug from query string
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// Redirect to home if no slug
if (empty($slug)) {
    header('Location: /');
    exit;
}

// Fetch calculator by slug (active only)
$calculator = get_calculator_by_slug($slug, true);

// 404 if not found or inactive
if (!$calculator) {
    http_response_code(404);
    $pageTitle = 'Calculator Not Found - CalcHub';
    $metaDesc = 'The requested calculator was not found.';
    $metaRobots = 'noindex, nofollow';
    $is404 = true;
} else {
    // SEO data
    $pageTitle = !empty($calculator['meta_title'])
        ? $calculator['meta_title']
        : $calculator['name'] . ' - Free Online Calculator | CalcHub';

    $metaDesc = !empty($calculator['meta_desc'])
        ? $calculator['meta_desc']
        : (!empty($calculator['short_desc'])
            ? $calculator['short_desc']
            : 'Use our free ' . $calculator['name'] . ' online. Easy to use, instant results.');

    $metaRobots = $calculator['is_indexed'] ? 'index, follow' : 'noindex, nofollow';

    // H1 title
    $h1Title = !empty($calculator['h1_title'])
        ? $calculator['h1_title']
        : $calculator['name'];

    // Ad settings
    $adLayout = $calculator['ad_layout'] ?? 'medium';
    $disableAds = (bool) ($calculator['disable_ads'] ?? false);

    // Parse FAQs
    $faqs = [];
    if (!empty($calculator['faq_json'])) {
        $faqs = json_decode($calculator['faq_json'], true) ?: [];
    }

    $is404 = false;
}

// Site name
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
    <meta name="robots" content="<?php echo $metaRobots; ?>">

    <!-- Canonical URL -->
    <?php if (!$is404): ?>
    <link rel="canonical" href="<?php echo 'https://' . ($_SERVER['HTTP_HOST'] ?? 'calchub.com') . '/calculator.php?slug=' . urlencode($calculator['slug']); ?>">
    <?php endif; ?>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">

    <!-- Global Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <?php if (!$is404 && !empty($calculator['schema_json'])): ?>
    <!-- Custom Schema JSON-LD -->
    <script type="application/ld+json">
    <?php echo $calculator['schema_json']; ?>
    </script>
    <?php endif; ?>

    <?php if (!$is404 && !empty($faqs)): ?>
    <!-- FAQ Schema JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            <?php
            $faqSchema = [];
            foreach ($faqs as $faq) {
                if (!empty($faq['question']) && !empty($faq['answer'])) {
                    $faqSchema[] = '{
                "@type": "Question",
                "name": ' . json_encode($faq['question']) . ',
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": ' . json_encode($faq['answer']) . '
                }
            }';
                }
            }
            echo implode(",\n            ", $faqSchema);
            ?>
        ]
    }
    </script>
    <?php endif; ?>

    <!-- Page Specific Styles -->
    <style>
        /* Calculator Page Layout */
        .calc-page {
            padding: var(--space-xl) 0;
        }

        /* Breadcrumb */
        .breadcrumb {
            margin-bottom: var(--space-lg);
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

        /* Calculator Header */
        .calc-header {
            margin-bottom: var(--space-xl);
        }

        .calc-title {
            font-size: var(--text-3xl);
            margin-bottom: var(--space-md);
            line-height: 1.2;
        }

        .calc-intro {
            font-size: var(--text-lg);
            color: var(--text-light);
            line-height: 1.6;
        }

        .calc-intro p {
            margin-bottom: var(--space-md);
        }

        .calc-intro p:last-child {
            margin-bottom: 0;
        }

        /* Calculator Widget Area */
        .calc-widget {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: var(--space-xl);
            box-shadow: var(--shadow-soft);
            margin-bottom: var(--space-xl);
        }

        #calculator-form {
            margin-bottom: var(--space-lg);
        }

        #calculator-result {
            padding: var(--space-lg);
            background: var(--bg);
            border-radius: var(--radius-md);
            display: none;
        }

        #calculator-result.active {
            display: block;
        }

        #calculator-chart {
            max-width: 100%;
            margin-top: var(--space-lg);
        }

        /* Content Section */
        .calc-content {
            margin-bottom: var(--space-xl);
        }

        .calc-content h2 {
            font-size: var(--text-xl);
            margin-top: var(--space-xl);
            margin-bottom: var(--space-md);
        }

        .calc-content h3 {
            font-size: var(--text-lg);
            margin-top: var(--space-lg);
            margin-bottom: var(--space-sm);
        }

        .calc-content p {
            margin-bottom: var(--space-md);
            line-height: 1.7;
        }

        .calc-content ul,
        .calc-content ol {
            margin-bottom: var(--space-md);
            padding-left: var(--space-lg);
        }

        .calc-content li {
            margin-bottom: var(--space-xs);
            line-height: 1.6;
        }

        .calc-content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: var(--space-md);
        }

        .calc-content th,
        .calc-content td {
            padding: var(--space-sm);
            border: 1px solid var(--border-light);
            text-align: left;
        }

        .calc-content th {
            background: var(--bg);
            font-weight: 600;
        }

        /* FAQ Section */
        .faq-section {
            margin-bottom: var(--space-xl);
        }

        .faq-title {
            font-size: var(--text-2xl);
            margin-bottom: var(--space-lg);
        }

        .faq-item {
            background: var(--card);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-md);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }

        .faq-question {
            padding: var(--space-md) var(--space-lg);
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background var(--transition-fast);
        }

        .faq-question:hover {
            background: var(--bg);
        }

        .faq-toggle {
            font-size: 1.2rem;
            transition: transform var(--transition-fast);
        }

        .faq-item.active .faq-toggle {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 var(--space-lg) var(--space-lg);
            display: none;
            color: var(--text-light);
            line-height: 1.6;
        }

        .faq-item.active .faq-answer {
            display: block;
        }

        /* Ad Slots */
        .ad-slot {
            background: var(--bg);
            border: 1px dashed var(--border);
            border-radius: var(--radius-md);
            padding: var(--space-lg);
            text-align: center;
            color: var(--muted);
            font-size: var(--text-sm);
            margin-bottom: var(--space-xl);
        }

        .ad-slot-top {
            margin-bottom: var(--space-lg);
        }

        .ad-slot-middle {
            margin: var(--space-xl) 0;
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

        /* Related Calculators */
        .related-section {
            margin-top: var(--space-2xl);
            padding-top: var(--space-xl);
            border-top: 1px solid var(--border-light);
        }

        .related-title {
            font-size: var(--text-xl);
            margin-bottom: var(--space-lg);
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: var(--space-md);
        }

        .related-card {
            background: var(--card);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            text-decoration: none;
            color: inherit;
            transition: all var(--transition-fast);
            box-shadow: var(--shadow-soft);
        }

        .related-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: inherit;
        }

        .related-card-title {
            font-weight: 600;
            font-size: var(--text-sm);
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div data-include="/partials/header.html"></div>

    <?php if ($is404): ?>
    <!-- 404 Error Page -->
    <main>
        <div class="container">
            <div class="error-page">
                <div class="error-code">404</div>
                <h1 class="error-title">Calculator Not Found</h1>
                <p class="error-desc">The calculator you're looking for doesn't exist or has been removed.</p>
                <a href="/" class="btn btn-primary">Back to Home</a>
            </div>
        </div>
    </main>

    <?php else: ?>
    <!-- Calculator Page -->
    <main class="calc-page">
        <div class="container">

            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="/">Home</a>
                <span>/</span>
                <?php if (!empty($calculator['category_slug'])): ?>
                <a href="/category.php?slug=<?php echo urlencode($calculator['category_slug']); ?>">
                    <?php echo htmlspecialchars($calculator['category_name']); ?>
                </a>
                <span>/</span>
                <?php endif; ?>
                <span class="breadcrumb-current"><?php echo htmlspecialchars($calculator['name']); ?></span>
            </nav>

            <!-- Top Ad Slot -->
            <?php if (!$disableAds && in_array($adLayout, ['medium', 'high'])): ?>
            <div class="ad-slot ad-slot-top">
                <!-- AdSense Code Here -->
                Ad Space (Top)
            </div>
            <?php endif; ?>

            <!-- Calculator Header -->
            <header class="calc-header">
                <h1 class="calc-title"><?php echo htmlspecialchars($h1Title); ?></h1>

                <?php if (!empty($calculator['intro_html'])): ?>
                <div class="calc-intro">
                    <?php echo $calculator['intro_html']; ?>
                </div>
                <?php endif; ?>
            </header>

            <!-- Calculator Widget -->
            <div class="calc-widget">
                <!-- Calculator Form (populated by JS) -->
                <section id="calculator-form">
                    <!-- Calculator inputs will be rendered here by JavaScript -->
                    <p style="color: var(--muted); text-align: center;">Loading calculator...</p>
                </section>

                <!-- Calculator Result -->
                <section id="calculator-result">
                    <!-- Results will be displayed here by JavaScript -->
                </section>

                <!-- Chart Canvas (hidden by default) -->
                <canvas id="calculator-chart" style="display:none;"></canvas>
            </div>

            <!-- Middle Ad Slot -->
            <?php if (!$disableAds && $adLayout === 'high'): ?>
            <div class="ad-slot ad-slot-middle">
                <!-- AdSense Code Here -->
                Ad Space (Middle)
            </div>
            <?php endif; ?>

            <!-- Content Section -->
            <?php if (!empty($calculator['content_html'])): ?>
            <section class="calc-content">
                <?php echo $calculator['content_html']; ?>
            </section>
            <?php endif; ?>

            <!-- FAQ Section -->
            <?php if (!empty($faqs)): ?>
            <section class="faq-section" id="calculator-faq">
                <h2 class="faq-title">Frequently Asked Questions</h2>

                <?php foreach ($faqs as $index => $faq): ?>
                <?php if (!empty($faq['question']) && !empty($faq['answer'])): ?>
                <div class="faq-item" id="faq-<?php echo $index; ?>">
                    <div class="faq-question" onclick="toggleFaq(<?php echo $index; ?>)">
                        <span><?php echo htmlspecialchars($faq['question']); ?></span>
                        <span class="faq-toggle">▼</span>
                    </div>
                    <div class="faq-answer">
                        <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>

            <!-- Bottom Ad Slot -->
            <?php if (!$disableAds): ?>
            <div class="ad-slot">
                <!-- AdSense Code Here -->
                Ad Space (Bottom)
            </div>
            <?php endif; ?>

        </div>
    </main>
    <?php endif; ?>

    <!-- Footer -->
    <div data-include="/partials/footer.html"></div>

    <!-- Include Loader Script -->
    <script src="/assets/js/include.js"></script>

    <?php if (!$is404): ?>
    <!-- Calculator Scripts -->
    <script>
        // Global calculator ID for analytics
        window.CALCULATOR_ID = <?php echo $calculator['id']; ?>;
        window.CALCULATOR_SLUG = '<?php echo addslashes($calculator['slug']); ?>';

        // FAQ Toggle
        function toggleFaq(index) {
            var item = document.getElementById('faq-' + index);
            if (item) {
                item.classList.toggle('active');
            }
        }

        // Open first FAQ by default
        <?php if (!empty($faqs)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var firstFaq = document.getElementById('faq-0');
            if (firstFaq) {
                firstFaq.classList.add('active');
            }
        });
        <?php endif; ?>
    </script>

    <?php if (!empty($calculator['js_file'])): ?>
    <!-- Calculator Specific JavaScript -->
    <script src="<?php echo htmlspecialchars($calculator['js_file']); ?>"></script>
    <?php endif; ?>
    <?php endif; ?>

</body>
</html>

<?php
/**
 * Calculator.net Clone - Homepage
 *
 * Yeh main entry point hai project ka.
 * Shared hosting (Hostinger) par yeh file public_html me rahegi.
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

// Config files include karenge (abhi commented hai, baad me enable)
// require_once __DIR__ . '/config/database.php';
// require_once __DIR__ . '/includes/db.php';
// require_once __DIR__ . '/includes/functions.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO Meta Tags (baad me dynamic honge database se) -->
    <title>Free Online Calculators - Math, Finance, Health & More</title>
    <meta name="description" content="100+ free online calculators for math, finance, health, conversion and more. Fast, accurate, and mobile-friendly.">
    <meta name="keywords" content="calculator, online calculator, free calculator, math calculator, BMI calculator">
    <meta name="author" content="Calculator Site">

    <!-- Canonical URL (SEO ke liye important) -->
    <link rel="canonical" href="https://yourdomain.com/">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">

    <!-- Mobile First CSS (inline for critical CSS, fast loading) -->
    <style>
        /* CSS Reset - Mobile First Approach */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            min-height: 100vh;
        }

        /* Container - Mobile first */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px;
        }

        /* Header Styles */
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        /* Main Content */
        .main-content {
            padding: 20px 0;
        }

        .welcome-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .welcome-box h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .welcome-box p {
            color: #666;
            margin-bottom: 20px;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 40px;
        }

        /* Tablet & Desktop Styles */
        @media (min-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }

            .container {
                padding: 20px;
            }

            .welcome-box {
                padding: 50px;
            }
        }
    </style>

    <!-- Google AdSense Code (replace with your AdSense code) -->
    <!-- <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-XXXXXXXX" crossorigin="anonymous"></script> -->

    <!-- Google Analytics (replace with your GA code) -->
    <!-- <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXX"></script> -->
</head>
<body>

    <!-- Header Section -->
    <!-- Baad me yeh partial se load hoga: <div data-include="/partials/header.html"></div> -->
    <header class="header">
        <div class="container">
            <h1>🧮 Calculator Hub</h1>
            <p>100+ Free Online Calculators</p>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container">

            <!-- Welcome/Test Box -->
            <div class="welcome-box">
                <h2>Hello Calculators! 👋</h2>
                <p>Project setup successful! Yeh test page hai.</p>

                <!-- PHP Info Display -->
                <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>

                <br>

                <!-- Status Badge -->
                <span class="status-badge">✅ Server Ready</span>

                <br><br>

                <!-- Next Steps Info -->
                <p style="font-size: 0.85rem; color: #888;">
                    Next: Database connection test karenge
                </p>
            </div>

            <!-- AdSense Ad Unit Placeholder -->
            <!-- <div class="ad-unit">
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="ca-pub-XXXXXXXX"
                     data-ad-slot="XXXXXXXX"
                     data-ad-format="auto"></ins>
                <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
            </div> -->

        </div>
    </main>

    <!-- Footer Section -->
    <!-- Baad me yeh partial se load hoga: <div data-include="/partials/footer.html"></div> -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Calculator Hub. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript Files -->
    <!-- Include.js for loading partials (baad me add karenge) -->
    <!-- <script src="/assets/js/include.js"></script> -->
    <!-- <script src="/assets/js/common.js"></script> -->

</body>
</html>

<?php
/**
 * 404 Error Page
 *
 * Pretty error page for not found resources
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Set 404 status if not already set
if (http_response_code() !== 404) {
    http_response_code(404);
}

// Default error message
$errorTitle = $errorTitle ?? 'Page Not Found';
$errorMessage = $errorMessage ?? 'The page you\'re looking for doesn\'t exist or has been moved.';
$siteName = 'CalcHub';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?php echo htmlspecialchars($errorTitle); ?> - <?php echo $siteName; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($errorMessage); ?>">
    <meta name="robots" content="noindex, nofollow">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">

    <!-- Global Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Page Specific Styles -->
    <style>
        .error-page {
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: var(--space-2xl) var(--space-md);
        }

        .error-content {
            max-width: 500px;
        }

        .error-icon {
            font-size: 5rem;
            margin-bottom: var(--space-lg);
            opacity: 0.6;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
            margin-bottom: var(--space-md);
            opacity: 0.15;
        }

        .error-title {
            font-size: var(--text-2xl);
            font-weight: 700;
            margin-bottom: var(--space-md);
            color: var(--text);
        }

        .error-message {
            font-size: var(--text-lg);
            color: var(--muted);
            margin-bottom: var(--space-xl);
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: var(--space-md);
            justify-content: center;
            flex-wrap: wrap;
        }

        .error-btn {
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
            padding: var(--space-sm) var(--space-lg);
            border-radius: var(--radius-md);
            font-weight: 500;
            text-decoration: none;
            transition: all var(--transition-fast);
        }

        .error-btn-primary {
            background: var(--primary);
            color: white;
        }

        .error-btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .error-btn-secondary {
            background: var(--bg);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .error-btn-secondary:hover {
            background: var(--border-light);
            transform: translateY(-2px);
        }

        .error-suggestions {
            margin-top: var(--space-2xl);
            padding-top: var(--space-xl);
            border-top: 1px solid var(--border-light);
        }

        .error-suggestions-title {
            font-size: var(--text-sm);
            color: var(--muted);
            margin-bottom: var(--space-md);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .suggestion-links {
            display: flex;
            gap: var(--space-md);
            justify-content: center;
            flex-wrap: wrap;
        }

        .suggestion-link {
            color: var(--accent);
            text-decoration: none;
            font-size: var(--text-sm);
        }

        .suggestion-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .error-code {
                font-size: 5rem;
            }

            .error-title {
                font-size: var(--text-xl);
            }

            .error-message {
                font-size: var(--text-base);
            }

            .error-actions {
                flex-direction: column;
            }

            .error-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div data-include="/partials/header.html"></div>

    <!-- Error Content -->
    <main class="error-page">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1 class="error-title"><?php echo htmlspecialchars($errorTitle); ?></h1>
            <p class="error-message"><?php echo htmlspecialchars($errorMessage); ?></p>

            <div class="error-actions">
                <a href="/" class="error-btn error-btn-primary">
                    Back to Home
                </a>
                <a href="javascript:history.back()" class="error-btn error-btn-secondary">
                    Go Back
                </a>
            </div>

            <div class="error-suggestions">
                <p class="error-suggestions-title">Popular Calculators</p>
                <div class="suggestion-links">
                    <a href="/calculator.php?slug=bmi" class="suggestion-link">BMI Calculator</a>
                    <a href="/calculator.php?slug=loan-emi" class="suggestion-link">Loan EMI</a>
                    <a href="/calculator.php?slug=percentage" class="suggestion-link">Percentage</a>
                    <a href="/calculator.php?slug=age" class="suggestion-link">Age Calculator</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <div data-include="/partials/footer.html"></div>

    <!-- Include Loader Script -->
    <script src="/assets/js/include.js"></script>

</body>
</html>

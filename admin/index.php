<?php
/**
 * Admin Login Page
 *
 * Login form for admin panel
 * Redirects to dashboard if already logged in
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Error reporting (development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Include auth (this also initializes session)
require_once BASE_PATH . '/admin/auth.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    redirect('/admin/dashboard.php');
}

// Initialize variables
$error = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Get form data
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate inputs
        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            // Attempt login
            if (login($email, $password)) {
                // Success - redirect to dashboard or intended page
                flash('Welcome back!', 'success');
                redirect(get_redirect_after_login());
            } else {
                // Failed login
                $error = 'Invalid email or password.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Admin Login - CalcHub</title>

    <!-- Prevent indexing of admin pages -->
    <meta name="robots" content="noindex, nofollow">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">

    <!-- Global Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Login Page Specific Styles -->
    <style>
        /* Login Page Layout */
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: var(--space-md);
        }

        /* Login Card */
        .login-card {
            background: var(--card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-strong);
            padding: var(--space-xl);
            width: 100%;
            max-width: 400px;
        }

        /* Logo/Header */
        .login-header {
            text-align: center;
            margin-bottom: var(--space-xl);
        }

        .login-logo {
            font-size: 3rem;
            margin-bottom: var(--space-sm);
        }

        .login-title {
            font-size: var(--text-2xl);
            color: var(--text);
            margin-bottom: var(--space-xs);
        }

        .login-subtitle {
            font-size: var(--text-sm);
            color: var(--muted);
            margin: 0;
        }

        /* Form */
        .login-form {
            margin-bottom: var(--space-lg);
        }

        /* Error Message */
        .login-error {
            background: var(--danger-soft);
            color: var(--danger);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-md);
            font-size: var(--text-sm);
            text-align: center;
            border-left: 4px solid var(--danger);
        }

        /* Footer */
        .login-footer {
            text-align: center;
            padding-top: var(--space-md);
            border-top: 1px solid var(--border-light);
        }

        .login-footer a {
            font-size: var(--text-sm);
            color: var(--muted);
        }

        .login-footer a:hover {
            color: var(--accent);
        }

        /* Password Toggle */
        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            padding: 4px;
            font-size: 1.1rem;
        }

        .password-toggle:hover {
            color: var(--text);
        }

        .password-wrapper .field-input {
            padding-right: 45px;
        }

        /* Remember Me */
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-md);
            font-size: var(--text-sm);
        }

        .remember-row label {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            cursor: pointer;
            color: var(--text-light);
        }

        .remember-row a {
            color: var(--accent);
        }

        /* Responsive */
        @media (min-width: 768px) {
            .login-card {
                padding: var(--space-2xl);
            }
        }
    </style>
</head>
<body>

    <div class="login-page">
        <div class="login-card">

            <!-- Header -->
            <div class="login-header">
                <div class="login-logo">🧮</div>
                <h1 class="login-title">Admin Panel</h1>
                <p class="login-subtitle">Sign in to manage your calculators</p>
            </div>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
            <div class="login-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="" class="login-form" autocomplete="off">
                <!-- CSRF Token -->
                <?php echo csrf_field(); ?>

                <!-- Email Field -->
                <div class="field">
                    <label for="email" class="field-label">Email Address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="field-input"
                           placeholder="admin@example.com"
                           value="<?php echo htmlspecialchars($email); ?>"
                           required
                           autofocus>
                </div>

                <!-- Password Field -->
                <div class="field">
                    <label for="password" class="field-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password"
                               id="password"
                               name="password"
                               class="field-input"
                               placeholder="Enter your password"
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                            <span id="toggleIcon">👁️</span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="remember-row">
                    <label>
                        <input type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                    <!-- <a href="/admin/forgot-password.php">Forgot password?</a> -->
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Sign In
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <a href="/">← Back to CalcHub</a>
            </div>

        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = '👁️';
            }
        }

        // Auto-hide error message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const errorBox = document.querySelector('.login-error');
            if (errorBox) {
                setTimeout(function() {
                    errorBox.style.transition = 'opacity 0.5s ease';
                    errorBox.style.opacity = '0';
                    setTimeout(function() {
                        errorBox.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });
    </script>

</body>
</html>

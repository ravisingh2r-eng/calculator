<?php
/**
 * Authentication Helpers
 *
 * Session-based authentication functions for admin panel
 * Secure session handling with regeneration
 *
 * Usage:
 *   require_once BASE_PATH . '/admin/auth.php';
 *   require_login();  // Redirect if not logged in
 *
 * @author  Your Name
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    die('Direct access not allowed');
}

// Include user model
require_once BASE_PATH . '/admin/models/users.php';


// ============================================================================
// SESSION CONFIGURATION & INITIALIZATION
// ============================================================================

/**
 * Initialize Secure Session
 *
 * Session ko secure settings ke saath start karta hai
 * Yeh function har admin page par call hona chahiye
 *
 * Security features:
 * - HTTP-only cookies (JS access nahi)
 * - Secure flag (HTTPS only in production)
 * - SameSite protection (CSRF prevention)
 * - Custom session name
 */
function init_session(): void
{
    // Agar session already started hai toh skip
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Session cookie parameters
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    $isLocalhost = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);

    // Session configuration
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    // Set secure flag only on HTTPS (not localhost)
    if ($isSecure && !$isLocalhost) {
        ini_set('session.cookie_secure', '1');
    }

    // Custom session name (don't use default PHPSESSID)
    session_name('CALCHUB_ADMIN');

    // Session lifetime (30 minutes)
    ini_set('session.gc_maxlifetime', '1800');

    // Cookie lifetime (0 = until browser closes)
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure && !$isLocalhost,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Start session
    session_start();

    // Check session timeout
    check_session_timeout();

    // Regenerate session ID periodically for security
    regenerate_session_if_needed();
}

/**
 * Check Session Timeout
 *
 * Session expiry check karta hai
 * 30 minute inactivity ke baad logout
 */
function check_session_timeout(): void
{
    $timeout = 1800; // 30 minutes in seconds

    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];

        if ($inactive > $timeout) {
            // Session expired
            logout();
            return;
        }
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();
}

/**
 * Regenerate Session ID Periodically
 *
 * Session fixation attacks se bachne ke liye
 * Har 5 minute me session ID regenerate hota hai
 */
function regenerate_session_if_needed(): void
{
    $regenerateInterval = 300; // 5 minutes

    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
        return;
    }

    if (time() - $_SESSION['last_regeneration'] > $regenerateInterval) {
        // Regenerate session ID
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}


// ============================================================================
// AUTHENTICATION FUNCTIONS
// ============================================================================

/**
 * Login User
 *
 * User ko authenticate karke session me store karta hai
 *
 * @param string $email     User email
 * @param string $password  Plain text password
 * @return bool             Login success or failure
 *
 * Usage:
 *   if (login('admin@example.com', 'password123')) {
 *       redirect('/admin/dashboard.php');
 *   } else {
 *       $error = 'Invalid credentials';
 *   }
 */
function login(string $email, string $password): bool
{
    // Initialize session if not started
    init_session();

    // Verify credentials
    $user = verify_user_credentials($email, $password);

    if (!$user) {
        return false;
    }

    // Regenerate session ID on login (security)
    session_regenerate_id(true);

    // Store user data in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['last_regeneration'] = time();

    // Store IP and user agent for additional security
    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

    return true;
}

/**
 * Logout User
 *
 * Session destroy karke user ko logout karta hai
 *
 * Usage:
 *   logout();
 *   redirect('/admin/login.php');
 */
function logout(): void
{
    // Initialize session if not started
    if (session_status() !== PHP_SESSION_ACTIVE) {
        init_session();
    }

    // Clear all session variables
    $_SESSION = [];

    // Delete session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // Destroy session
    session_destroy();
}

/**
 * Check if User is Logged In
 *
 * @return bool  True if logged in
 *
 * Usage:
 *   if (is_logged_in()) {
 *       // Show dashboard
 *   } else {
 *       // Show login form
 *   }
 */
function is_logged_in(): bool
{
    // Initialize session if not started
    if (session_status() !== PHP_SESSION_ACTIVE) {
        init_session();
    }

    // Check basic login flag
    if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
        return false;
    }

    // Optional: Verify IP hasn't changed (comment out if using mobile/changing networks)
    // if ($_SESSION['user_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
    //     logout();
    //     return false;
    // }

    return true;
}

/**
 * Require Login
 *
 * Protected pages ke liye - login nahi hai toh redirect
 * Har admin page ke top par call karo
 *
 * @param string $redirectTo  Redirect URL after login
 *
 * Usage:
 *   // At top of protected page
 *   require_login();
 *
 *   // With custom redirect
 *   require_login('/admin/special-page.php');
 */
function require_login(string $redirectTo = ''): void
{
    if (!is_logged_in()) {
        // Store intended URL for redirect after login
        if (!empty($redirectTo)) {
            $_SESSION['redirect_after_login'] = $redirectTo;
        } else {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/admin/';
        }

        // Redirect to login page
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Require Role
 *
 * Specific role require karta hai
 * Agar user ka role match nahi karta toh 403
 *
 * @param string $requiredRole  Required role (admin, editor, viewer)
 *
 * Usage:
 *   require_role('admin');  // Only admins can access
 */
function require_role(string $requiredRole): void
{
    require_login();

    if (!current_user_has_role($requiredRole)) {
        // 403 Forbidden
        http_response_code(403);
        die('Access denied. You do not have permission to view this page.');
    }
}

/**
 * Get Current User
 *
 * Session se current user data return karta hai
 *
 * @return array|null  User data or null
 *
 * Usage:
 *   $user = current_user();
 *   if ($user) {
 *       echo "Welcome, " . $user['name'];
 *   }
 */
function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}

/**
 * Get Current User ID
 *
 * @return int|null  User ID or null
 */
function current_user_id(): ?int
{
    return is_logged_in() ? (int) $_SESSION['user_id'] : null;
}

/**
 * Get Current User Role
 *
 * @return string|null  User role or null
 */
function current_user_role(): ?string
{
    return is_logged_in() ? $_SESSION['user_role'] : null;
}

/**
 * Check if Current User has Role
 *
 * Role hierarchy check karta hai
 *
 * @param string $requiredRole  Required role
 * @return bool                 Has role or not
 *
 * Usage:
 *   if (current_user_has_role('editor')) {
 *       // Can edit content
 *   }
 */
function current_user_has_role(string $requiredRole): bool
{
    if (!is_logged_in()) {
        return false;
    }

    $userRole = $_SESSION['user_role'];

    // Role hierarchy: admin > editor > viewer
    $roleHierarchy = [
        'viewer' => 1,
        'editor' => 2,
        'admin' => 3
    ];

    $userLevel = $roleHierarchy[$userRole] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;

    return $userLevel >= $requiredLevel;
}

/**
 * Check if Current User is Admin
 *
 * @return bool
 */
function is_admin(): bool
{
    return current_user_role() === 'admin';
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Redirect to URL
 *
 * @param string $url  Redirect URL
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Get Redirect URL After Login
 *
 * Login ke baad intended page par redirect karne ke liye
 *
 * @return string  Redirect URL
 */
function get_redirect_after_login(): string
{
    $redirect = $_SESSION['redirect_after_login'] ?? '/admin/';
    unset($_SESSION['redirect_after_login']);
    return $redirect;
}

/**
 * Flash Message
 *
 * Set or get flash message (one-time display)
 *
 * @param string|null $message  Message to set (null to get)
 * @param string      $type     Message type (success, error, warning, info)
 * @return string|null          Message or null
 *
 * Usage:
 *   // Set message
 *   flash('Profile updated successfully', 'success');
 *
 *   // Get and display message
 *   if ($message = flash()) {
 *       echo '<div class="alert alert-' . $message['type'] . '">' . $message['text'] . '</div>';
 *   }
 */
function flash(?string $message = null, string $type = 'info')
{
    if ($message !== null) {
        // Set message
        $_SESSION['flash_message'] = [
            'text' => $message,
            'type' => $type
        ];
        return null;
    }

    // Get message
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }

    return null;
}


// ============================================================================
// CSRF PROTECTION
// ============================================================================

/**
 * Generate CSRF Token
 *
 * Form submissions ke liye CSRF token generate karta hai
 *
 * @return string  CSRF token
 *
 * Usage:
 *   <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 *
 * Form submission me CSRF token verify karta hai
 *
 * @param string $token  Token from form
 * @return bool          Valid or not
 *
 * Usage:
 *   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 *       if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
 *           die('Invalid CSRF token');
 *       }
 *       // Process form
 *   }
 */
function verify_csrf_token(string $token): bool
{
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF Hidden Field
 *
 * CSRF token ke liye hidden input HTML return karta hai
 *
 * @return string  HTML input element
 *
 * Usage:
 *   <form method="POST">
 *       <?= csrf_field() ?>
 *       <!-- other fields -->
 *   </form>
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}


// ============================================================================
// AUTO-INITIALIZE SESSION
// ============================================================================

// Automatically initialize secure session when this file is included
init_session();


/**
 * USAGE EXAMPLES:
 *
 * // Login page (login.php)
 * require_once BASE_PATH . '/admin/auth.php';
 *
 * if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 *     if (login($_POST['email'], $_POST['password'])) {
 *         redirect(get_redirect_after_login());
 *     } else {
 *         $error = 'Invalid email or password';
 *     }
 * }
 *
 * // Protected page (dashboard.php)
 * require_once BASE_PATH . '/admin/auth.php';
 * require_login();
 *
 * $user = current_user();
 * echo "Welcome, " . $user['name'];
 *
 * // Admin-only page
 * require_once BASE_PATH . '/admin/auth.php';
 * require_role('admin');
 *
 * // Logout
 * require_once BASE_PATH . '/admin/auth.php';
 * logout();
 * redirect('/admin/login.php');
 *
 * // Check role in template
 * if (current_user_has_role('editor')) {
 *     echo '<a href="/admin/edit.php">Edit</a>';
 * }
 *
 * // Flash messages
 * flash('Settings saved!', 'success');
 * redirect('/admin/settings.php');
 *
 * // Display flash message
 * if ($msg = flash()) {
 *     echo '<div class="alert alert-' . $msg['type'] . '">' . htmlspecialchars($msg['text']) . '</div>';
 * }
 */

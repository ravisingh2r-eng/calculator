<?php
/**
 * Users Model
 *
 * Admin users ke liye CRUD operations
 * Authentication aur user management functions
 *
 * Usage:
 *   require_once BASE_PATH . '/admin/models/users.php';
 *   $user = get_user_by_email('admin@example.com');
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
 * Get User by Email
 *
 * Email se user fetch karta hai
 * Login aur duplicate check ke liye use hota hai
 *
 * @param string $email  User email
 * @return array|false   User data or false
 *
 * Usage:
 *   $user = get_user_by_email('admin@example.com');
 *   if ($user) {
 *       echo $user['name'];
 *   }
 */
function get_user_by_email(string $email)
{
    return db_fetch_one(
        "SELECT * FROM users WHERE email = ?",
        [strtolower(trim($email))]
    );
}

/**
 * Get User by ID
 *
 * @param int $id  User ID
 * @return array|false  User data or false
 *
 * Usage:
 *   $user = get_user_by_id(5);
 */
function get_user_by_id(int $id)
{
    return db_fetch_one(
        "SELECT * FROM users WHERE id = ?",
        [$id]
    );
}

/**
 * Get All Users
 *
 * Admin user management ke liye
 *
 * @param bool $activeOnly  Only active users
 * @return array            Array of users
 *
 * Usage:
 *   $users = get_all_users();
 */
function get_all_users(bool $activeOnly = true): array
{
    $whereClause = $activeOnly ? 'WHERE is_active = 1' : '';

    return db_fetch_all(
        "SELECT id, name, email, role, is_active, last_login, created_at
         FROM users
         {$whereClause}
         ORDER BY name ASC"
    );
}

/**
 * Check if Email Exists
 *
 * @param string   $email      Email to check
 * @param int|null $excludeId  User ID to exclude
 * @return bool
 */
function user_email_exists(string $email, ?int $excludeId = null): bool
{
    $email = strtolower(trim($email));

    if ($excludeId) {
        return db_exists('users', 'email = ? AND id != ?', [$email, $excludeId]);
    }

    return db_exists('users', 'email = ?', [$email]);
}


// ============================================================================
// AUTHENTICATION FUNCTIONS
// ============================================================================

/**
 * Verify User Credentials
 *
 * Email aur password check karta hai
 * Successful login par user data return karta hai
 * Invalid credentials par false return karta hai
 *
 * @param string $email          User email
 * @param string $passwordPlain  Plain text password
 * @return array|false           User data (without password) or false
 *
 * Usage:
 *   $user = verify_user_credentials('admin@example.com', 'password123');
 *   if ($user) {
 *       // Login successful
 *       $_SESSION['user_id'] = $user['id'];
 *   } else {
 *       // Invalid credentials
 *   }
 */
function verify_user_credentials(string $email, string $passwordPlain)
{
    // Get user by email
    $user = get_user_by_email($email);

    // User not found
    if (!$user) {
        return false;
    }

    // User is inactive
    if (!$user['is_active']) {
        return false;
    }

    // Verify password
    if (!password_verify($passwordPlain, $user['password_hash'])) {
        return false;
    }

    // Update last login time
    update_user_last_login($user['id']);

    // Check if password needs rehashing (PHP version upgrade, etc.)
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        update_user_password($user['id'], $passwordPlain);
    }

    // Remove password hash from returned data
    unset($user['password_hash']);

    return $user;
}

/**
 * Update Last Login Time
 *
 * @param int $userId  User ID
 * @return int         Affected rows
 */
function update_user_last_login(int $userId): int
{
    return db_execute(
        "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?",
        [$userId]
    );
}


// ============================================================================
// CREATE FUNCTION
// ============================================================================

/**
 * Create New User
 *
 * Naya admin user create karta hai
 * Password automatically hash hota hai
 *
 * @param string $name          User full name
 * @param string $email         User email (must be unique)
 * @param string $passwordPlain Plain text password
 * @param string $role          User role (admin, editor, viewer)
 * @return int                  New user ID
 * @throws Exception            If validation fails
 *
 * Usage:
 *   $newId = create_user(
 *       'John Doe',
 *       'john@example.com',
 *       'SecurePass123!',
 *       'editor'
 *   );
 */
function create_user(string $name, string $email, string $passwordPlain, string $role = 'viewer'): int
{
    // Validate inputs
    $errors = validate_user_data([
        'name' => $name,
        'email' => $email,
        'password' => $passwordPlain,
        'role' => $role
    ]);

    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }

    // Clean email
    $email = strtolower(trim($email));

    // Check if email exists
    if (user_email_exists($email)) {
        throw new Exception('Email address already exists');
    }

    // Hash password
    $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT, [
        'cost' => 12  // Higher cost = more secure but slower
    ]);

    // Validate role
    $allowedRoles = ['admin', 'editor', 'viewer'];
    if (!in_array($role, $allowedRoles)) {
        $role = 'viewer';
    }

    // Insert user
    return db_insert_array('users', [
        'name' => trim($name),
        'email' => $email,
        'password_hash' => $passwordHash,
        'role' => $role,
        'is_active' => 1
    ]);
}


// ============================================================================
// UPDATE FUNCTIONS
// ============================================================================

/**
 * Update User
 *
 * @param int   $id    User ID
 * @param array $data  Updated data [name, email, role, is_active]
 * @return int         Affected rows
 * @throws Exception   If validation fails
 *
 * Usage:
 *   update_user(5, [
 *       'name' => 'Updated Name',
 *       'role' => 'admin'
 *   ]);
 */
function update_user(int $id, array $data): int
{
    // Check if exists
    $existing = get_user_by_id($id);
    if (!$existing) {
        throw new Exception('User not found');
    }

    // Build update data
    $updateData = [];

    if (isset($data['name'])) {
        if (empty($data['name'])) {
            throw new Exception('Name cannot be empty');
        }
        $updateData['name'] = trim($data['name']);
    }

    if (isset($data['email'])) {
        $email = strtolower(trim($data['email']));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }

        if (user_email_exists($email, $id)) {
            throw new Exception('Email already in use');
        }

        $updateData['email'] = $email;
    }

    if (isset($data['role'])) {
        $allowedRoles = ['admin', 'editor', 'viewer'];
        if (!in_array($data['role'], $allowedRoles)) {
            throw new Exception('Invalid role');
        }
        $updateData['role'] = $data['role'];
    }

    if (isset($data['is_active'])) {
        $updateData['is_active'] = (int) $data['is_active'];
    }

    if (empty($updateData)) {
        return 0;
    }

    return db_update_array('users', $updateData, 'id = ?', [$id]);
}

/**
 * Update User Password
 *
 * @param int    $userId        User ID
 * @param string $passwordPlain New plain text password
 * @return int                  Affected rows
 * @throws Exception            If validation fails
 *
 * Usage:
 *   update_user_password(5, 'NewSecurePass123!');
 */
function update_user_password(int $userId, string $passwordPlain): int
{
    // Validate password
    if (strlen($passwordPlain) < 8) {
        throw new Exception('Password must be at least 8 characters');
    }

    // Hash password
    $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT, [
        'cost' => 12
    ]);

    return db_execute(
        "UPDATE users SET password_hash = ? WHERE id = ?",
        [$passwordHash, $userId]
    );
}

/**
 * Change Password (with old password verification)
 *
 * User apna password change karta hai
 *
 * @param int    $userId      User ID
 * @param string $oldPassword Current password
 * @param string $newPassword New password
 * @return bool               Success or failure
 * @throws Exception          If validation fails
 *
 * Usage:
 *   $success = change_user_password(5, 'OldPass', 'NewPass123!');
 */
function change_user_password(int $userId, string $oldPassword, string $newPassword): bool
{
    // Get user
    $user = get_user_by_id($userId);
    if (!$user) {
        throw new Exception('User not found');
    }

    // Verify old password
    if (!password_verify($oldPassword, $user['password_hash'])) {
        throw new Exception('Current password is incorrect');
    }

    // Validate new password
    if (strlen($newPassword) < 8) {
        throw new Exception('New password must be at least 8 characters');
    }

    // Update password
    return update_user_password($userId, $newPassword) > 0;
}


// ============================================================================
// DELETE FUNCTIONS
// ============================================================================

/**
 * Delete User (Soft)
 *
 * @param int $id  User ID
 * @return int     Affected rows
 */
function delete_user(int $id): int
{
    return db_update_array('users', ['is_active' => 0], 'id = ?', [$id]);
}

/**
 * Hard Delete User
 *
 * @param int $id  User ID
 * @return int     Affected rows
 */
function hard_delete_user(int $id): int
{
    return db_execute("DELETE FROM users WHERE id = ?", [$id]);
}

/**
 * Restore User
 *
 * @param int $id  User ID
 * @return int     Affected rows
 */
function restore_user(int $id): int
{
    return db_update_array('users', ['is_active' => 1], 'id = ?', [$id]);
}


// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Get User Count
 *
 * @param string|null $role  Filter by role
 * @return int               Count
 */
function get_user_count(?string $role = null): int
{
    if ($role) {
        return db_count('users', 'role = ? AND is_active = 1', [$role]);
    }

    return db_count('users', 'is_active = 1');
}

/**
 * Get Users by Role
 *
 * @param string $role  User role
 * @return array        Users
 */
function get_users_by_role(string $role): array
{
    return db_fetch_all(
        "SELECT id, name, email, role, last_login, created_at
         FROM users
         WHERE role = ? AND is_active = 1
         ORDER BY name ASC",
        [$role]
    );
}

/**
 * Check User Permission
 *
 * User ka role check karta hai
 *
 * @param int    $userId         User ID
 * @param string $requiredRole   Required role (admin, editor, viewer)
 * @return bool                  Has permission or not
 *
 * Usage:
 *   if (user_has_role(5, 'admin')) {
 *       // Allow admin action
 *   }
 */
function user_has_role(int $userId, string $requiredRole): bool
{
    $user = get_user_by_id($userId);
    if (!$user) {
        return false;
    }

    // Role hierarchy: admin > editor > viewer
    $roleHierarchy = ['viewer' => 1, 'editor' => 2, 'admin' => 3];

    $userLevel = $roleHierarchy[$user['role']] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;

    return $userLevel >= $requiredLevel;
}


// ============================================================================
// VALIDATION FUNCTIONS
// ============================================================================

/**
 * Validate User Data
 *
 * @param array    $data       User data
 * @param int|null $excludeId  User ID to exclude
 * @return array               Array of error messages
 */
function validate_user_data(array $data, ?int $excludeId = null): array
{
    $errors = [];

    // Name validation
    if (isset($data['name'])) {
        if (empty($data['name'])) {
            $errors[] = 'Name is required';
        } elseif (strlen($data['name']) > 100) {
            $errors[] = 'Name must be less than 100 characters';
        }
    }

    // Email validation
    if (isset($data['email'])) {
        $email = strtolower(trim($data['email']));

        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        } elseif (strlen($email) > 255) {
            $errors[] = 'Email must be less than 255 characters';
        } elseif (user_email_exists($email, $excludeId)) {
            $errors[] = 'Email already exists';
        }
    }

    // Password validation (only for create or password change)
    if (isset($data['password'])) {
        if (strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        // Optional: Add more password requirements
        // if (!preg_match('/[A-Z]/', $data['password'])) {
        //     $errors[] = 'Password must contain at least one uppercase letter';
        // }
        // if (!preg_match('/[0-9]/', $data['password'])) {
        //     $errors[] = 'Password must contain at least one number';
        // }
    }

    // Role validation
    if (isset($data['role'])) {
        $allowedRoles = ['admin', 'editor', 'viewer'];
        if (!in_array($data['role'], $allowedRoles)) {
            $errors[] = 'Invalid role selected';
        }
    }

    return $errors;
}


// ============================================================================
// PASSWORD RESET FUNCTIONS (Future Implementation)
// ============================================================================

/**
 * Generate Password Reset Token
 *
 * @param string $email  User email
 * @return string|false  Reset token or false
 *
 * Note: This is a placeholder. Implement proper token storage in DB.
 */
function generate_password_reset_token(string $email)
{
    $user = get_user_by_email($email);
    if (!$user) {
        return false;
    }

    // Generate secure token
    $token = bin2hex(random_bytes(32));

    // TODO: Store token in database with expiry
    // For now, just return the token
    // In production, you would:
    // 1. Store token hash in DB with user_id and expiry time
    // 2. Send email with reset link
    // 3. On reset page, verify token and allow password change

    return $token;
}

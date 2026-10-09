<?php
// auth_helper.php - RBAC Guard Middleware & Security Helpers

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if the user is authenticated.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Ensures user has correct role, otherwise redirects to unauthorized or login.
 * @param array|int $allowed_roles Role ID(s) permitted (1: Customer, 2: Staff, 3: Admin)
 */
function require_role($allowed_roles) {
    $base = (strpos($_SERVER['SCRIPT_NAME'], '/customer/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/staff/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' : '';
    
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: " . $base . "login.php?error=session_expired");
        exit();
    }
    
    $user_role = $_SESSION['role_id'];
    
    if (is_array($allowed_roles)) {
        if (!in_array($user_role, $allowed_roles)) {
            header("Location: " . $base . "unauthorized.php");
            exit();
        }
    } else {
        if ($user_role != $allowed_roles) {
            header("Location: " . $base . "unauthorized.php");
            exit();
        }
    }
}

/**
 * Cleans user output to prevent XSS.
 */
function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generates and returns a CSRF token for forms.
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates a submitted CSRF token.
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Adds an audit log to the database.
 */
function log_event($pdo, $action, $details) {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $ip = $_SERVER['REMOTE_ADDR'] === '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $details, $ip]);
    } catch (PDOException $e) {
        // Fail silently in production, or handle error
    }
}
?>

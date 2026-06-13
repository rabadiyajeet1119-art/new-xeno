<?php
/**
 * Transport & Logistics Management System
 * Authentication & Authorization Checks
 */

require_once 'functions.php';

/**
 * Require user to be logged in
 * Redirects to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please login to access this page.";
        redirect('login.php');
    }
}

/**
 * Require user to be a customer
 */
function requireCustomer() {
    requireLogin();
    
    if (getUserRole() !== 'customer') {
        $_SESSION['error'] = "Access denied. Customer privileges required.";
        redirect('index.php');
    }
}

/**
 * Require user to be a driver
 */
function requireDriver() {
    requireLogin();
    
    if (getUserRole() !== 'driver') {
        $_SESSION['error'] = "Access denied. Driver privileges required.";
        redirect('index.php');
    }
}

/**
 * Require user to be an admin
 */
function requireAdmin() {
    requireLogin();
    
    if (getUserRole() !== 'admin') {
        $_SESSION['error'] = "Access denied. Admin privileges required.";
        redirect('index.php');
    }
}

/**
 * Redirect if already logged in
 * @param string $redirectUrl
 */
function redirectIfLoggedIn($redirectUrl = 'dashboard.php') {
    if (isLoggedIn()) {
        // Redirect based on role
        $role = getUserRole();
        
        switch ($role) {
            case 'admin':
                redirect('admin/dashboard.php');
                break;
            case 'driver':
                redirect('driver/dashboard.php');
                break;
            case 'customer':
            default:
                redirect($redirectUrl);
                break;
        }
    }
}

/**
 * Check if user has permission to view/edit booking
 * @param int $bookingId
 * @param string $action
 * @return bool
 */
function canAccessBooking($bookingId, $action = 'view') {
    global $pdo;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    $role = getUserRole();
    $userId = getUserId();
    
    // Admin can access all bookings
    if ($role === 'admin') {
        return true;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT user_id, driver_id FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            return false;
        }
        
        // Customer can access their own bookings
        if ($role === 'customer' && $booking['user_id'] == $userId) {
            return true;
        }
        
        // Driver can access assigned bookings
        if ($role === 'driver' && $booking['driver_id'] == $userId) {
            return true;
        }
        
    } catch (PDOException $e) {
        error_log("Error checking booking access: " . $e->getMessage());
        return false;
    }
    
    return false;
}

/**
 * Log user activity (optional)
 * @param string $action
 * @param string $details
 */
function logActivity($action, $details = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) 
                               VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            getUserId(),
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token field HTML
 * @return string
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}
?>

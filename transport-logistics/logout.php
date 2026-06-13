<?php
/**
 * Transport & Logistics Management System
 * Logout Script
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Clear all session variables
$_SESSION = [];

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Destroy session
session_destroy();

// Start new session for flash message
session_start();
$_SESSION['info'] = "You have been logged out successfully.";

// Redirect to home page
redirect('index.php');
?>
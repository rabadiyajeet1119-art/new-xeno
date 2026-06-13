<?php
/**
 * Transport & Logistics Management System
 * Database Configuration File
 * 
 * INSTRUCTIONS:
 * 1. Update the database credentials below to match your XAMPP/LAMP setup
 * 2. Default XAMPP settings: host=localhost, dbname=transport_logistics, user=root, password=(empty)
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'transport_logistics');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'Transport & Logistics');
define('APP_URL', 'http://localhost/transport-logistics');
define('APP_VERSION', '1.0.0');

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create Database Connection using PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // Log error and display user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');

/**
 * Get PDO connection instance
 * @return PDO
 */
function getDB() {
    global $pdo;
    return $pdo;
}
?>

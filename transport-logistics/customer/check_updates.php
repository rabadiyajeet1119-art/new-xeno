<?php
/**
 * API Endpoint: Check Updates for Customer
 * Returns JSON list of bookings where accepted_by IS NOT NULL and customer needs update
 */

header('Content-Type: application/json');

require_once '../includes/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/function_helpers.php';

// Auth check: customer only
if (!isLoggedIn() || getUserRole() !== 'customer') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$userId = getUserId();

try {
    $pdo = getDB();

    // Check for notifications or just check bookings that are accepted
    // The instructions say: "check notifications table (optional)" or "bookings where accepted_by IS NOT NULL"
    // To make it simple and robust, let's fetch any booking where accepted_by IS NOT NULL.
    // The client side will likely compare with what it already has, or we can just return all accepted ones.
    // Optimally, we return the latest accepted ones.

    // Let's fetch all accepted bookings for this user that are NOT 'Delivered' or 'Cancelled' maybe?
    // Or just all accepted ones for dashboard display update.
    // Simpler: Fetch bookings for this user where accepted_by IS NOT NULL.
    // And also join with users table to get driver info.

    $sql = "SELECT b.id, b.status, b.accepted_by, b.accepted_at, 
                   d.name as driver_name, d.phone as driver_phone, d.email as driver_email
            FROM bookings b
            JOIN users d ON b.accepted_by = d.id
            WHERE b.user_id = ? 
            AND b.accepted_by IS NOT NULL
            ORDER BY b.updated_at DESC LIMIT 20"; // Limit to recent

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $updates = $stmt->fetchAll();

    // Also fetch unread notifications?
    // Instructions: "Returns JSON list of the logged-in customer's bookings where accepted_by IS NOT NULL"
    // Also mentioned: "Optionally push a small in-page toast/alert and create a row in top notifications area"

    // Let's fetch unread notifications too
    $sqlNotif = "SELECT id, message, created_at FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC";
    $stmtNotif = $pdo->prepare($sqlNotif);
    $stmtNotif->execute([$userId]);
    $notifications = $stmtNotif->fetchAll();

    // Mark notifications as read? Maybe not yet, let user dismiss or mark read.
    // Or just return them.

    echo json_encode([
        'success' => true,
        'updates' => $updates,
        'notifications' => $notifications
    ]);

} catch (PDOException $e) {
    error_log("check_updates error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
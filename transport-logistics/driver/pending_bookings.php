<?php
/**
 * API Endpoint: Get Pending Bookings for Drivers
 * Returns JSON list of open bookings (accepted_by IS NULL)
 */

header('Content-Type: application/json');

require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Auth check: ensure logged-in user role == 'driver'
// Assuming requireDriver() handles the check and redirect/exit if not driver.
// However, since this is an API endpoint, we might want to return JSON error instead of redirecting HTML.
// But following instructions to use existing auth.
if (!isLoggedIn() || getUserRole() !== 'driver') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

try {
    $pdo = getDB();

    // Select open bookings with customer info
    // Assuming 'users' table has the customer info linked by 'user_id' in bookings
    $sql = "SELECT b.id, b.pickup_location, b.delivery_location, b.goods_type, b.weight, b.delivery_date, 
                   u.id as customer_id, u.name as customer_name
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            WHERE b.accepted_by IS NULL 
            AND b.status = 'Pending'
            ORDER BY b.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $bookings = $stmt->fetchAll();

    echo json_encode(['success' => true, 'bookings' => $bookings]);

} catch (PDOException $e) {
    error_log("pending_bookings error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
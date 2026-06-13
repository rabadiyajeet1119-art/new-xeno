<?php
/**
 * API Endpoint: Accept Booking
 * atomically assigns the booking to the driver
 */

header('Content-Type: application/json');

require_once '../includes/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/function_helpers.php';

// Auth check: driver only
if (!isLoggedIn() || getUserRole() !== 'driver') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// Get booking_id from POST request
$input = json_decode(file_get_contents('php://input'), true);
// Fallback to $_POST if not JSON body
$bookingId = $input['booking_id'] ?? $_POST['booking_id'] ?? null;

if (!$bookingId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit;
}

$driverId = getUserId();

try {
    $pdo = getDB();

    // Atomic UPDATE
    // Check if status needs to be updated. Instructions say set status = 'Accepted'.
    $sql = "UPDATE bookings 
            SET accepted_by = ?, accepted_at = NOW(), status = 'Accepted' 
            WHERE id = ? AND (accepted_by IS NULL OR accepted_by = 0)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$driverId, $bookingId]);

    if ($stmt->rowCount() == 1) {
        // Success: Driver assigned

        // Return driver info and booking id
        // We need to fetch the customer ID to send notification
        // Note: We could have done this before update, but we authorized first.

        // Fetch booking details to get customer ID
        $stmtBooking = $pdo->prepare("SELECT user_id FROM bookings WHERE id = ?");
        $stmtBooking->execute([$bookingId]);
        $booking = $stmtBooking->fetch();

        if ($booking) {
            $userId = $booking['user_id'];

            // Get Driver Info for notification message
            $driverInfo = getUserInfo($driverId);
            $driverName = $driverInfo['name'];
            $driverPhone = $driverInfo['phone'];

            // Create notification for customer
            $message = "Driver {$driverName} (Phone: {$driverPhone}) has accepted your booking #{$bookingId}. View details in My Bookings.";
            createNotification($userId, 'booking_accepted', $message);
        }

        // Return success response with driver info (instructed to return driver info, though mostly for client verification)
        // Actually, the driver endpoint response goes to the DRIVER, so they know they got it.
        // But the customer polling endpoint will need this info. 
        // The instructions said: 
        // -> return JSON { success: true, driver: {id, name, phone, email}, booking_id: X }

        $driverInfo = getUserInfo($driverId); // Fetch again or use previous

        echo json_encode([
            'success' => true,
            'booking_id' => $bookingId,
            'driver' => [
                'id' => $driverId,
                'name' => $driverInfo['name'],
                'phone' => $driverInfo['phone'],
                'email' => $driverInfo['email']
            ]
        ]);

    } else {
        // Failure: Already assigned or invalid ID
        http_response_code(409); // Conflict
        echo json_encode(['success' => false, 'message' => 'Booking already accepted by another driver']);
    }

} catch (PDOException $e) {
    error_log("accept_booking error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
<?php
require_once 'includes/config.php';

function testConcurrency()
{
    global $pdo;

    echo "Starting Concurrency Test...\n";

    try {
        // 1. Setup: Create a test user and booking
        // Insert test user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone) VALUES ('Test Customer', 'test@example.com', 'pass', 'customer', '1234567890')");
        $stmt->execute();
        $userId = $pdo->lastInsertId();

        // Insert test booking
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, pickup_location, delivery_location, goods_type, weight, delivery_date, status) VALUES (?, 'A', 'B', 'Box', 10, NOW(), 'Pending')");
        $stmt->execute([$userId]);
        $bookingId = $pdo->lastInsertId();

        echo "Created Booking #$bookingId for User #$userId\n";

        // 2. Simulate Driver A accept
        $driverA = 99991; // Fake ID
        $sql = "UPDATE bookings 
                SET accepted_by = ?, accepted_at = NOW(), status = 'Accepted' 
                WHERE id = ? AND (accepted_by IS NULL OR accepted_by = 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$driverA, $bookingId]);

        if ($stmt->rowCount() == 1) {
            echo "PASS: Driver A successfully accepted the booking.\n";
        } else {
            echo "FAIL: Driver A failed to accept.\n";
        }

        // 3. Simulate Driver B accept (Should Fail)
        $driverB = 99992; // Fake ID
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$driverB, $bookingId]);

        if ($stmt->rowCount() == 0) {
            echo "PASS: Driver B was correctly blocked from accepting.\n";
        } else {
            echo "FAIL: Driver B was able to accept! Race condition exists.\n";
        }

        // 4. Verify Notification created
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ?");
        $stmt->execute([$userId]);
        // Note: The notification is created in the PHP endpoint, not the SQL. 
        // Since we are only testing the SQL logic here, we won't see a notification 
        // unless we call the helper.
        // But we can verify the booking status.

        $stmt = $pdo->prepare("SELECT accepted_by, status FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch();

        if ($booking['accepted_by'] == $driverA && $booking['status'] == 'Accepted') {
            echo "PASS: Booking is correctly assigned to Driver A.\n";
        } else {
            echo "FAIL: Booking state is incorrect.\n";
        }

        // Cleanup
        $pdo->exec("DELETE FROM bookings WHERE id = $bookingId");
        $pdo->exec("DELETE FROM users WHERE id = $userId");
        // Also delete notifications if any (foreign key cascade might handle it)

        echo "Test Completed.\n";

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

testConcurrency();
?>
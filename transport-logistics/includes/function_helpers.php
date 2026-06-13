<?php
/**
 * Helper Functions for Driver Marketplace & Notifications
 */

/**
 * Get minimal user info (name, phone, email)
 * @param int $userId
 * @return array|false Associative array with name, phone, email or false if not found
 */
function getUserInfo($userId)
{
    $pdo = getDB();
    try {
        $stmt = $pdo->prepare("SELECT name, phone, email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(); // Returns associative array or false
    } catch (PDOException $e) {
        error_log("getUserInfo error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a notification for a user
 * @param int $userId
 * @param string $type
 * @param string $message
 * @return bool True on success, false on failure
 */
function createNotification($userId, $type, $message)
{
    $pdo = getDB();
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, created_at) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$userId, $type, $message]);
    } catch (PDOException $e) {
        error_log("createNotification error: " . $e->getMessage());
        return false;
    }
}
?>
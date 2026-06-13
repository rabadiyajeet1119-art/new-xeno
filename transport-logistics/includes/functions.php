<?php
/**
 * Transport & Logistics Management System
 * Helper Functions
 */

require_once 'config.php';

/**
 * Sanitize user input
 * @param string $data
 * @return string
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Display success message
 * @param string $message
 * @return string
 */
function showSuccess($message) {
    return '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Display error message
 * @param string $message
 * @return string
 */
function showError($message) {
    return '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Display warning message
 * @param string $message
 * @return string
 */
function showWarning($message) {
    return '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Display info message
 * @param string $message
 * @return string
 */
function showInfo($message) {
    return '<div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Redirect to a URL
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user role
 * @return string|null
 */
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Get current user ID
 * @return int|null
 */
function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get current user name
 * @return string|null
 */
function getUserName() {
    return isset($_SESSION['name']) ? $_SESSION['name'] : null;
}

/**
 * Format date for display
 * @param string $date
 * @return string
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Format datetime for display
 * @param string $datetime
 * @return string
 */
function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

/**
 * Get status badge HTML
 * @param string $status
 * @return string
 */
function getStatusBadge($status) {
    $badges = [
        'Pending'     => '<span class="badge bg-warning text-dark">Pending</span>',
        'In Transit'  => '<span class="badge bg-info text-dark">In Transit</span>',
        'Delivered'   => '<span class="badge bg-success">Delivered</span>',
        'Cancelled'   => '<span class="badge bg-danger">Cancelled</span>',
        'Accepted'    => '<span class="badge bg-primary">Accepted</span>',
        'Declined'    => '<span class="badge bg-secondary">Declined</span>'
    ];
    
    return isset($badges[$status]) ? $badges[$status] : '<span class="badge bg-secondary">' . $status . '</span>';
}

/**
 * Generate unique booking reference
 * @return string
 */
function generateBookingRef() {
    return 'TRK' . date('Ymd') . rand(1000, 9999);
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic validation)
 * @param string $phone
 * @return bool
 */
function isValidPhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', preg_replace('/[^0-9]/', '', $phone));
}

/**
 * Check if string is empty
 * @param string $str
 * @return bool
 */
function isEmpty($str) {
    return empty(trim($str));
}

/**
 * Get booking by ID
 * @param int $bookingId
 * @return array|null
 */
function getBookingById($bookingId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT b.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                                      d.name as driver_name, d.email as driver_email, d.phone as driver_phone
                               FROM bookings b 
                               JOIN users u ON b.user_id = u.id 
                               LEFT JOIN users d ON b.driver_id = d.id 
                               WHERE b.id = ?");
        $stmt->execute([$bookingId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting booking: " . $e->getMessage());
        return null;
    }
}

/**
 * Get user bookings count
 * @param int $userId
 * @return int
 */
function getUserBookingsCount($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting bookings count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get user bookings by status
 * @param int $userId
 * @param string $status
 * @return int
 */
function getUserBookingsByStatus($userId, $status) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = ?");
        $stmt->execute([$userId, $status]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting bookings by status: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get total users count
 * @param string|null $role
 * @return int
 */
function getTotalUsers($role = null) {
    global $pdo;
    
    try {
        if ($role) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
            $stmt->execute([$role]);
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        }
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting total users: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get total bookings count
 * @param string|null $status
 * @return int
 */
function getTotalBookings($status = null) {
    global $pdo;
    
    try {
        if ($status) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE status = ?");
            $stmt->execute([$status]);
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
        }
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting total bookings: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get recent bookings
 * @param int $limit
 * @return array
 */
function getRecentBookings($limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT b.*, u.name as customer_name 
                               FROM bookings b 
                               JOIN users u ON b.user_id = u.id 
                               ORDER BY b.created_at DESC 
                               LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting recent bookings: " . $e->getMessage());
        return [];
    }
}

/**
 * Export data to CSV
 * @param array $data
 * @param string $filename
 */
function exportToCSV($data, $filename = 'export.csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Output headers
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
    }
    
    // Output data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}

/**
 * Paginate results
 * @param string $query
 * @param array $params
 * @param int $page
 * @param int $perPage
 * @return array
 */
function paginate($query, $params = [], $page = 1, $perPage = 10) {
    global $pdo;
    
    $offset = ($page - 1) * $perPage;
    
    // Get total count
    $countQuery = preg_replace('/SELECT.*?FROM/i', 'SELECT COUNT(*) FROM', $query, 1);
    $countQuery = preg_replace('/ORDER BY.*/i', '', $countQuery);
    $countQuery = preg_replace('/LIMIT.*/i', '', $countQuery);
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    
    // Get paginated results
    $query .= " LIMIT $perPage OFFSET $offset";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    $totalPages = ceil($total / $perPage);
    
    return [
        'data' => $results,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => $totalPages,
        'hasNext' => $page < $totalPages,
        'hasPrev' => $page > 1
    ];
}
?>

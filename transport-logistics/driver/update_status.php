<?php
$pageTitle = 'Update Status';
require_once '../includes/auth_check.php';
requireDriver();

$driverId = getUserId();
$errors = [];

// Get booking ID
$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$bookingId) {
    $_SESSION['error'] = "Invalid booking ID.";
    redirect('assigned.php');
}

// Get booking details
try {
    $stmt = $pdo->prepare("SELECT b.*, u.name as customer_name, u.phone as customer_phone
                           FROM bookings b 
                           JOIN users u ON b.user_id = u.id 
                           WHERE b.id = ? AND b.driver_id = ?");
    $stmt->execute([$bookingId, $driverId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        $_SESSION['error'] = "Booking not found or you don't have permission to update it.";
        redirect('assigned.php');
    }
    
} catch (PDOException $e) {
    error_log("Update status error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again later.";
    redirect('assigned.php');
}

// Process status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = sanitize($_POST['status'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Validate status
    $validStatuses = ['Pending', 'In Transit', 'Delivered'];
    if (!in_array($newStatus, $validStatuses)) {
        $errors[] = "Invalid status selected.";
    }
    
    // Status flow validation
    $currentStatus = $booking['status'];
    
    if ($currentStatus === 'Delivered') {
        $errors[] = "This booking has already been delivered and cannot be updated.";
    }
    
    if ($currentStatus === 'Pending' && $newStatus === 'Delivered') {
        $errors[] = "Booking must be marked as 'In Transit' before 'Delivered'.";
    }
    
    // If no errors, update status
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ?, notes = CONCAT(COALESCE(notes, ''), ?) WHERE id = ? AND driver_id = ?");
            $noteUpdate = $notes ? "\n[" . date('Y-m-d H:i') . "] " . $notes : '';
            $stmt->execute([$newStatus, $noteUpdate, $bookingId, $driverId]);
            
            $_SESSION['success'] = "Booking status updated successfully to '$newStatus'!";
            redirect('assigned.php');
            
        } catch (PDOException $e) {
            error_log("Status update error: " . $e->getMessage());
            $errors[] = "An error occurred while updating the status. Please try again.";
        }
    }
}

require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-success text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-edit me-2"></i>Update Status</h2>
                <p class="mb-0 opacity-75">Booking #<?php echo $booking['id']; ?></p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="assigned.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Jobs
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Update Form -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Booking Details -->
            <div class="col-lg-4">
                <div class="card border-0 shadow mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Booking Details</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <small class="text-muted d-block">Booking ID</small>
                            <strong>#<?php echo $booking['id']; ?></strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Customer</small>
                            <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
                            <?php if ($booking['customer_phone']): ?>
                                <br><small><?php echo htmlspecialchars($booking['customer_phone']); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Pickup Location</small>
                            <strong><?php echo htmlspecialchars($booking['pickup_location']); ?></strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Delivery Location</small>
                            <strong><?php echo htmlspecialchars($booking['delivery_location']); ?></strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Goods Type</small>
                            <strong><?php echo htmlspecialchars($booking['goods_type']); ?></strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Weight</small>
                            <strong><?php echo $booking['weight'] ? $booking['weight'] . ' kg' : 'N/A'; ?></strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Current Status</small>
                            <?php echo getStatusBadge($booking['status']); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Status Update Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-sync-alt me-2"></i>Update Delivery Status</h5>
                    </div>
                    <div class="card-body p-4 p-lg-5">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($booking['status'] === 'Delivered'): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                This booking has been successfully delivered. No further updates are possible.
                            </div>
                        <?php else: ?>
                            <form method="POST" action="" id="updateStatusForm">
                                <div class="mb-4">
                                    <label class="form-label">Select New Status <span class="text-danger">*</span></label>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="form-check card p-3 text-center <?php echo $booking['status'] === 'Pending' ? 'border-success' : ''; ?>">
                                                <input class="form-check-input" type="radio" name="status" id="statusPending" 
                                                       value="Pending" <?php echo $booking['status'] === 'Pending' ? 'checked' : ''; ?>
                                                       <?php echo $booking['status'] !== 'Pending' ? 'disabled' : ''; ?>>
                                                <label class="form-check-label d-block mt-2" for="statusPending">
                                                    <i class="fas fa-clock text-warning fa-2x mb-2 d-block"></i>
                                                    <strong>Pending</strong>
                                                    <small class="d-block text-muted">Waiting to start</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check card p-3 text-center <?php echo $booking['status'] === 'In Transit' ? 'border-success' : ''; ?>">
                                                <input class="form-check-input" type="radio" name="status" id="statusTransit" 
                                                       value="In Transit" <?php echo $booking['status'] === 'In Transit' ? 'checked' : ''; ?>
                                                       <?php echo $booking['status'] === 'Delivered' ? 'disabled' : ''; ?>>
                                                <label class="form-check-label d-block mt-2" for="statusTransit">
                                                    <i class="fas fa-shipping-fast text-info fa-2x mb-2 d-block"></i>
                                                    <strong>In Transit</strong>
                                                    <small class="d-block text-muted">On the way</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check card p-3 text-center <?php echo $booking['status'] === 'Delivered' ? 'border-success' : ''; ?>">
                                                <input class="form-check-input" type="radio" name="status" id="statusDelivered" 
                                                       value="Delivered" <?php echo $booking['status'] === 'Delivered' ? 'checked' : ''; ?>
                                                       <?php echo $booking['status'] === 'Pending' ? 'disabled' : ''; ?>>
                                                <label class="form-check-label d-block mt-2" for="statusDelivered">
                                                    <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                                                    <strong>Delivered</strong>
                                                    <small class="d-block text-muted">Completed</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Status must follow the flow: Pending → In Transit → Delivered
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="notes" class="form-label">
                                        <i class="fas fa-sticky-note text-warning me-2"></i>Update Notes
                                    </label>
                                    <textarea class="form-control" id="notes" name="notes" rows="4" 
                                              placeholder="Add any notes about this status update (optional)"></textarea>
                                    <div class="form-text">
                                        These notes will be appended to the booking record.
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save me-2"></i>Update Status
                                    </button>
                                    <a href="assigned.php" class="btn btn-outline-secondary btn-lg">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Previous Notes -->
                <?php if ($booking['notes']): ?>
                    <div class="card border-0 shadow mt-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Previous Notes</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="bg-light p-3 rounded">
                                <?php echo nl2br(htmlspecialchars($booking['notes'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

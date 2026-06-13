<?php
$pageTitle = 'Booking Details';
require_once 'includes/auth_check.php';
requireCustomer();

$userId = getUserId();

// Get booking ID
$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$bookingId) {
    $_SESSION['error'] = "Invalid booking ID.";
    redirect('bookings.php');
}

// Get booking details
try {
    $stmt = $pdo->prepare("SELECT b.*, d.name as driver_name, d.phone as driver_phone, d.email as driver_email
                           FROM bookings b 
                           LEFT JOIN users d ON b.driver_id = d.id 
                           WHERE b.id = ? AND b.user_id = ?");
    $stmt->execute([$bookingId, $userId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        $_SESSION['error'] = "Booking not found or you don't have permission to view it.";
        redirect('bookings.php');
    }
    
} catch (PDOException $e) {
    error_log("Booking view error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again later.";
    redirect('bookings.php');
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-file-alt me-2"></i>Booking Details</h2>
                <p class="mb-0 opacity-75">Booking #<?php echo $booking['id']; ?></p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="bookings.php" class="btn btn-light me-2">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
                <a href="track.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-info text-white">
                    <i class="fas fa-map-marker-alt me-2"></i>Track
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Booking Details -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Main Details -->
            <div class="col-lg-8">
                <div class="card border-0 shadow mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Booking Information</h5>
                        <?php echo getStatusBadge($booking['status']); ?>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>Pickup Location
                                </h6>
                                <p class="fw-medium"><?php echo nl2br(htmlspecialchars($booking['pickup_location'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-flag-checkered text-success me-2"></i>Delivery Location
                                </h6>
                                <p class="fw-medium"><?php echo nl2br(htmlspecialchars($booking['delivery_location'])); ?></p>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row g-4">
                            <div class="col-md-4">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-box text-primary me-2"></i>Goods Type
                                </h6>
                                <p class="fw-medium"><?php echo htmlspecialchars($booking['goods_type']); ?></p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-weight text-primary me-2"></i>Weight
                                </h6>
                                <p class="fw-medium"><?php echo $booking['weight'] ? $booking['weight'] . ' kg' : 'N/A'; ?></p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-calendar text-primary me-2"></i>Delivery Date
                                </h6>
                                <p class="fw-medium"><?php echo $booking['delivery_date'] ? formatDate($booking['delivery_date']) : 'N/A'; ?></p>
                            </div>
                        </div>
                        
                        <?php if ($booking['notes']): ?>
                            <hr class="my-4">
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-sticky-note text-warning me-2"></i>Additional Notes
                                    </h6>
                                    <p class="fw-medium"><?php echo nl2br(htmlspecialchars($booking['notes'])); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Status Timeline -->
                <div class="card border-0 shadow">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Status Timeline</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="tracking-timeline">
                            <?php 
                            $statuses = ['Pending', 'In Transit', 'Delivered'];
                            $currentStatus = $booking['status'];
                            
                            foreach ($statuses as $status):
                                $isActive = false;
                                $isCurrent = false;
                                
                                if ($status === 'Pending') {
                                    $isActive = true;
                                    $isCurrent = ($currentStatus === 'Pending');
                                } elseif ($status === 'In Transit') {
                                    $isActive = in_array($currentStatus, ['In Transit', 'Delivered']);
                                    $isCurrent = ($currentStatus === 'In Transit');
                                } elseif ($status === 'Delivered') {
                                    $isActive = ($currentStatus === 'Delivered');
                                    $isCurrent = ($currentStatus === 'Delivered');
                                }
                            ?>
                                <div class="timeline-item <?php echo $isActive ? 'active' : ''; ?> <?php echo $isCurrent ? 'current' : ''; ?>">
                                    <h6 class="mb-1"><?php echo $status; ?></h6>
                                    <p class="text-muted small mb-0">
                                        <?php 
                                        switch($status) {
                                            case 'Pending':
                                                echo 'Your booking has been received and is being processed.';
                                                break;
                                            case 'In Transit':
                                                echo 'Your shipment is on its way to the destination.';
                                                break;
                                            case 'Delivered':
                                                echo 'Your shipment has been delivered successfully.';
                                                break;
                                        }
                                        ?>
                                    </p>
                                    <?php if ($isCurrent): ?>
                                        <small class="text-primary">
                                            <i class="fas fa-clock me-1"></i>
                                            Last Updated: <?php echo formatDateTime($booking['updated_at']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Booking Summary -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Summary</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Booking ID:</span>
                            <strong>#<?php echo $booking['id']; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Created:</span>
                            <strong><?php echo formatDateTime($booking['created_at']); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Last Updated:</span>
                            <strong><?php echo formatDateTime($booking['updated_at']); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Status:</span>
                            <?php echo getStatusBadge($booking['status']); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Driver Info -->
                <?php if ($booking['driver_name']): ?>
                    <div class="card border-0 shadow mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Assigned Driver</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 80px; height: 80px;">
                                    <i class="fas fa-user-tie text-primary fa-2x"></i>
                                </div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($booking['driver_name']); ?></h5>
                                <span class="badge bg-success">Assigned</span>
                            </div>
                            <?php if ($booking['driver_phone']): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-phone text-primary me-2"></i>
                                    <span><?php echo htmlspecialchars($booking['driver_phone']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($booking['driver_email']): ?>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <span><?php echo htmlspecialchars($booking['driver_email']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Driver Assignment</h5>
                        </div>
                        <div class="card-body p-4 text-center">
                            <i class="fas fa-user-clock text-muted fa-3x mb-3"></i>
                            <p class="text-muted mb-0">Driver not assigned yet. You will be notified once a driver is assigned to your booking.</p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="card border-0 shadow">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-grid gap-2">
                            <a href="track.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-info text-white">
                                <i class="fas fa-map-marker-alt me-2"></i>Track Shipment
                            </a>
                            <a href="bookings.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>View All Bookings
                            </a>
                            <a href="contact.php" class="btn btn-outline-secondary">
                                <i class="fas fa-headset me-2"></i>Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

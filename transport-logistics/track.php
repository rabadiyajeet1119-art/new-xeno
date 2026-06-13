<?php
$pageTitle = 'Track Shipment';
require_once 'includes/config.php';
require_once 'includes/functions.php';

$booking = null;
$error = null;

// Process tracking request
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['booking_id'])) {
    $bookingId = sanitize($_POST['booking_id'] ?? $_GET['booking_id'] ?? '');

    if (isEmpty($bookingId)) {
        $error = "Please enter a Booking ID.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT b.*, u.name as customer_name, d.name as driver_name 
                                   FROM bookings b 
                                   JOIN users u ON b.user_id = u.id 
                                   LEFT JOIN users d ON b.driver_id = d.id 
                                   WHERE b.id = ?");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch();

            if (!$booking) {
                $error = "Booking not found. Please check the Booking ID and try again.";
            }
        } catch (PDOException $e) {
            error_log("Tracking error: " . $e->getMessage());
            $error = "An error occurred. Please try again later.";
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="hero-section" style="padding: 3rem 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="hero-title" style="font-size: 2.5rem;">Track Your Shipment</h1>
                <p class="hero-subtitle mb-0">
                    Enter your Booking ID to track the status of your shipment
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Tracking Section -->
<section class="tracking-section">
    <div class="container">
        <!-- Tracking Form -->
        <div class="tracking-form">
            <div class="card border-0 shadow">
                <div class="card-body p-4 p-lg-5">
                    <form method="POST" action="" id="trackingForm">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="number" class="form-control" id="booking_id" name="booking_id"
                                placeholder="Enter Booking ID (e.g., 1, 2, 3...)"
                                value="<?php echo isset($bookingId) ? htmlspecialchars($bookingId) : ''; ?>" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Track
                            </button>
                        </div>
                        <div class="form-text mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Enter the Booking ID you received when creating your booking.
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="alert alert-danger mt-4">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Tracking Result -->
        <?php if ($booking): ?>
            <div class="tracking-result">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <i class="fas fa-box text-primary me-2"></i>Booking #<?php echo $booking['id']; ?>
                    </h4>
                    <?php echo getStatusBadge($booking['status']); ?>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>Pickup Location
                                </h6>
                                <p class="mb-0 fw-medium"><?php echo htmlspecialchars($booking['pickup_location']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-flag-checkered text-success me-2"></i>Delivery Location
                                </h6>
                                <p class="mb-0 fw-medium"><?php echo htmlspecialchars($booking['delivery_location']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="fas fa-box text-primary mb-2" style="font-size: 1.5rem;"></i>
                            <h6 class="mb-1">Goods Type</h6>
                            <p class="mb-0 fw-medium"><?php echo htmlspecialchars($booking['goods_type'] ?: 'N/A'); ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="fas fa-weight text-primary mb-2" style="font-size: 1.5rem;"></i>
                            <h6 class="mb-1">Weight</h6>
                            <p class="mb-0 fw-medium"><?php echo $booking['weight'] ? $booking['weight'] . ' kg' : 'N/A'; ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="fas fa-calendar text-primary mb-2" style="font-size: 1.5rem;"></i>
                            <h6 class="mb-1">Delivery Date</h6>
                            <p class="mb-0 fw-medium">
                                <?php echo $booking['delivery_date'] ? formatDate($booking['delivery_date']) : 'N/A'; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="fas fa-user text-primary mb-2" style="font-size: 1.5rem;"></i>
                            <h6 class="mb-1">Assigned Driver</h6>
                            <p class="mb-0 fw-medium">
                                <?php echo $booking['driver_name'] ? htmlspecialchars($booking['driver_name']) : 'Not Assigned'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tracking Timeline -->
                <h5 class="mb-3">Shipment Progress</h5>
                <div class="tracking-timeline">
                    <?php
                    $statuses = ['Pending', 'In Transit', 'Delivered'];
                    $currentStatus = $booking['status'];
                    $currentReached = false;

                    foreach ($statuses as $status):
                        $isActive = $status === $currentStatus || ($currentStatus === 'Delivered' && in_array($status, ['Pending', 'In Transit', 'Delivered']));
                        $isCurrent = $status === $currentStatus;

                        if ($status === 'Pending' && $currentStatus === 'In Transit') {
                            $isActive = true;
                        }
                        ?>
                        <div
                            class="timeline-item <?php echo $isActive ? 'active' : ''; ?> <?php echo $isCurrent ? 'current' : ''; ?>">
                            <h6 class="mb-1"><?php echo $status; ?></h6>
                            <p class="text-muted small mb-0">
                                <?php
                                switch ($status) {
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
                                    Updated: <?php echo formatDateTime($booking['updated_at']); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($booking['notes']): ?>
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="mb-2">
                            <i class="fas fa-sticky-note text-warning me-2"></i>Notes
                        </h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($booking['notes'])); ?></p>
                    </div>
                <?php endif; ?>

                <div class="mt-4 text-center">
                    <a href="track.php" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>Track Another
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Quick Links -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <i class="fas fa-plus-circle text-primary fa-3x mb-3"></i>
                        <h5>Create a Booking</h5>
                        <p class="text-muted mb-3">Ready to ship? Create a new booking in minutes.</p>
                        <?php if (isLoggedIn() && getUserRole() === 'customer'): ?>
                            <a href="create_booking.php" class="btn btn-primary btn-sm">Create Booking</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-sm">Login to Book</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <i class="fas fa-list text-success fa-3x mb-3"></i>
                        <h5>View My Bookings</h5>
                        <p class="text-muted mb-3">Check all your bookings and their status.</p>
                        <?php if (isLoggedIn() && getUserRole() === 'customer'): ?>
                            <a href="bookings.php" class="btn btn-success btn-sm">My Bookings</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-success btn-sm">Login to View</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <i class="fas fa-headset text-info fa-3x mb-3"></i>
                        <h5>Need Help?</h5>
                        <p class="text-muted mb-3">Contact our support team for assistance.</p>
                        <a href="contact.php" class="btn btn-info btn-sm text-white">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
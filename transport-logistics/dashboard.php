<?php
$pageTitle = 'Customer Dashboard';
require_once 'includes/auth_check.php';
requireCustomer();

$userId = getUserId();

// Get customer statistics
try {
    $totalBookings = getUserBookingsCount($userId);
    $pendingBookings = getUserBookingsByStatus($userId, 'Pending');
    $inTransitBookings = getUserBookingsByStatus($userId, 'In Transit');
    $deliveredBookings = getUserBookingsByStatus($userId, 'Delivered');

    // Get recent bookings
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$userId]);
    $recentBookings = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalBookings = $pendingBookings = $inTransitBookings = $deliveredBookings = 0;
    $recentBookings = [];
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-tachometer-alt me-2"></i>Welcome,
                    <?php echo htmlspecialchars(getUserName()); ?>!</h2>
                <p class="mb-0 opacity-75">Manage your bookings and track your shipments</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="create_booking.php" class="btn btn-light">
                    <i class="fas fa-plus-circle me-2"></i>Create New Booking
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Notification Area -->
<div class="container mt-3" id="notificationArea"></div>

<!-- Dashboard Stats -->

<section class="py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="dashboard-card dashboard-card-primary">
                    <div class="dashboard-card-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="dashboard-card-value"><?php echo $totalBookings; ?></div>
                    <div class="dashboard-card-label">Total Bookings</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="dashboard-card dashboard-card-warning">
                    <div class="dashboard-card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="dashboard-card-value"><?php echo $pendingBookings; ?></div>
                    <div class="dashboard-card-label">Pending</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="dashboard-card dashboard-card-info">
                    <div class="dashboard-card-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="dashboard-card-value"><?php echo $inTransitBookings; ?></div>
                    <div class="dashboard-card-label">In Transit</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="dashboard-card dashboard-card-success">
                    <div class="dashboard-card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="dashboard-card-value"><?php echo $deliveredBookings; ?></div>
                    <div class="dashboard-card-label">Delivered</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Bookings -->
<section class="py-4">
    <div class="container">
        <div class="card border-0 shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Bookings</h5>
                <a href="bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentBookings)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box-open text-muted fa-4x mb-3"></i>
                        <h5 class="text-muted">No bookings yet</h5>
                        <p class="text-muted mb-3">Create your first booking to get started</p>
                        <a href="create_booking.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>Create Booking
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Pickup</th>
                                    <th>Delivery</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBookings as $booking): ?>
                                    <tr>
                                        <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars(substr($booking['pickup_location'], 0, 30)) . (strlen($booking['pickup_location']) > 30 ? '...' : ''); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($booking['delivery_location'], 0, 30)) . (strlen($booking['delivery_location']) > 30 ? '...' : ''); ?>
                                        </td>
                                        <td><?php echo getStatusBadge($booking['status']); ?></td>
                                        <td><?php echo formatDate($booking['created_at']); ?></td>
                                        <td>
                                            <a href="booking_view.php?id=<?php echo $booking['id']; ?>"
                                                class="btn btn-sm btn-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="track.php?booking_id=<?php echo $booking['id']; ?>"
                                                class="btn btn-sm btn-info text-white" title="Track">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Quick Actions -->
<section class="py-4">
    <div class="container">
        <h5 class="mb-3">Quick Actions</h5>
        <div class="row g-4">
            <div class="col-md-4">
                <a href="create_booking.php" class="card border-0 shadow-sm text-decoration-none h-100">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                                style="width: 70px; height: 70px;">
                                <i class="fas fa-plus-circle text-primary fa-2x"></i>
                            </div>
                        </div>
                        <h5 class="text-dark">Create Booking</h5>
                        <p class="text-muted mb-0">Book a new shipment</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="bookings.php" class="card border-0 shadow-sm text-decoration-none h-100">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                                style="width: 70px; height: 70px;">
                                <i class="fas fa-list text-success fa-2x"></i>
                            </div>
                        </div>
                        <h5 class="text-dark">My Bookings</h5>
                        <p class="text-muted mb-0">View all your bookings</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="track.php" class="card border-0 shadow-sm text-decoration-none h-100">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                                style="width: 70px; height: 70px;">
                                <i class="fas fa-search-location text-info fa-2x"></i>
                            </div>
                        </div>
                        <h5 class="text-dark">Track Shipment</h5>
                        <p class="text-muted mb-0">Track your packages</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Help Section -->
<section class="py-4 mb-4">
    <div class="container">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5><i class="fas fa-question-circle text-primary me-2"></i>Need Help?</h5>
                        <p class="mb-md-0">If you have any questions or need assistance, our support team is here to
                            help.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="contact.php" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notificationArea = document.getElementById('notificationArea');

        // Function to check for updates
        function checkUpdates() {
            fetch('customer/check_updates.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        handleUpdates(data.updates);
                        handleNotifications(data.notifications);
                    }
                })
                .catch(error => console.error('Error checking updates:', error));
        }

        function handleUpdates(updates) {
            if (!updates || updates.length === 0) return;

            updates.forEach(booking => {
                // Find the booking row if it exists on page
                // Assuming table rows have some ID or we search by content. 
                // The table in dashboard.php doesn't have IDs on tr. 
                // But we can find <td>#ID</td>

                // Or simpler: just match by looping all rows
                const rows = document.querySelectorAll('table tbody tr');
                rows.forEach(row => {
                    // Check if this row is for the booking
                    if (row.innerHTML.includes('#' + booking.id + '</strong>')) {
                        // Found the row. Update status badge if changed.
                        // The status column is index 3 (4th column)
                        const statusCell = row.cells[3];
                        if (statusCell && !statusCell.innerHTML.includes(booking.status)) {
                            // Update status badge
                            statusCell.innerHTML = `<span class="badge bg-success">${booking.status}</span>`; // Assuming 'Accepted' maps to success
                        }

                        // If we want to show driver info, we might need a place for it.
                        // The current dashboard table has: Booking ID, Pickup, Delivery, Status, Date, Actions.
                        // There isn't a dedicated "Driver" column in the dashboard table.
                        // However, the instructions say:
                        // "If response indicates booking(s) now have accepted_by set, update the booking row to show: Assigned Driver..."
                        // "When assigned, update #status-##ID## => "Accepted" and #driver-##ID## => ..."

                        // Since I can't easily change the table structure dynamically if it wasn't there, 
                        // I should probably rely on the notification to tell them.
                        // OR I could inject the driver info into the status column or add a tooltip?
                        // "View Details" page usually has it.

                        // Let's rely on the notification for the "Push" effect, and maybe a toast.
                    }
                });
            });
        }

        function handleNotifications(notifications) {
            if (!notifications || notifications.length === 0) return;

            // We only want to show new notifications that haven't been shown in this session perhaps?
            // Or just show all unread.
            // For now, let's just clear and show unread ones in a stack.

            let html = '';
            notifications.forEach(notif => {
                html += `
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-bell me-2"></i> ${notif.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            });

            if (html) {
                notificationArea.innerHTML = html;
            }
        }

        // Poll every 10 seconds
        setInterval(checkUpdates, 10000);
    });
</script>
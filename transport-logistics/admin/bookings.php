<?php
$pageTitle = 'Manage Bookings';
require_once '../includes/auth_check.php';
requireAdmin();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Filter by status
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Delete booking
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$deleteId]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Booking deleted successfully.";
        } else {
            $_SESSION['error'] = "Booking not found.";
        }
    } catch (PDOException $e) {
        error_log("Delete booking error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while deleting the booking.";
    }
    
    redirect('bookings.php');
}

// Update booking status
if (isset($_POST['update_status']) && isset($_POST['booking_id']) && isset($_POST['status'])) {
    $bookingId = (int)$_POST['booking_id'];
    $newStatus = sanitize($_POST['status']);
    $driverId = !empty($_POST['driver_id']) ? (int)$_POST['driver_id'] : null;
    
    $validStatuses = ['Pending', 'In Transit', 'Delivered', 'Cancelled'];
    
    if (in_array($newStatus, $validStatuses)) {
        try {
            if ($driverId) {
                $stmt = $pdo->prepare("UPDATE bookings SET status = ?, driver_id = ? WHERE id = ?");
                $stmt->execute([$newStatus, $driverId, $bookingId]);
            } else {
                $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $bookingId]);
            }
            
            $_SESSION['success'] = "Booking #$bookingId status updated to '$newStatus'.";
        } catch (PDOException $e) {
            error_log("Update booking status error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while updating the booking.";
        }
    } else {
        $_SESSION['error'] = "Invalid status selected.";
    }
    
    redirect('bookings.php');
}

// Get all drivers for assignment
$stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'driver' ORDER BY name");
$drivers = $stmt->fetchAll();

// Build query
$whereClause = "WHERE 1=1";
$params = [];

if ($statusFilter && in_array($statusFilter, ['Pending', 'In Transit', 'Delivered', 'Cancelled'])) {
    $whereClause .= " AND b.status = ?";
    $params[] = $statusFilter;
}

if ($search) {
    $whereClause .= " AND (b.pickup_location LIKE ? OR b.delivery_location LIKE ? OR u.name LIKE ? OR b.id = ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = is_numeric($search) ? $search : 0;
}

// Get total count
$countQuery = "SELECT COUNT(*) FROM bookings b JOIN users u ON b.user_id = u.id $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalBookings = $stmt->fetchColumn();
$totalPages = ceil($totalBookings / $perPage);

// Get bookings with pagination
$query = "SELECT b.*, u.name as customer_name, d.name as driver_name 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          LEFT JOIN users d ON b.driver_id = d.id 
          $whereClause 
          ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-danger text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-box me-2"></i>Manage Bookings</h2>
                <p class="mb-0 opacity-75">View and manage all bookings</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Bookings List -->
<section class="py-5">
    <div class="container">
        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Booking ID, location, customer..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filter by Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="In Transit" <?php echo $statusFilter === 'In Transit' ? 'selected' : ''; ?>>In Transit</option>
                            <option value="Delivered" <?php echo $statusFilter === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="Cancelled" <?php echo $statusFilter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                    <?php if ($search || $statusFilter): ?>
                        <div class="col-md-2">
                            <a href="bookings.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>Clear
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Bookings Table -->
        <div class="card border-0 shadow">
            <div class="card-body p-0">
                <?php if (empty($bookings)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box-open text-muted fa-4x mb-3"></i>
                        <h5 class="text-muted">No bookings found</h5>
                        <p class="text-muted mb-0">
                            <?php echo $search || $statusFilter ? "No bookings match your search criteria." : "No bookings in the system yet."; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Pickup</th>
                                    <th>Delivery</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars(substr($booking['pickup_location'], 0, 20)) . (strlen($booking['pickup_location']) > 20 ? '...' : ''); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(substr($booking['delivery_location'], 0, 20)) . (strlen($booking['delivery_location']) > 20 ? '...' : ''); ?>
                                        </td>
                                        <td>
                                            <?php echo $booking['driver_name'] ? htmlspecialchars($booking['driver_name']) : '<span class="text-muted">Not Assigned</span>'; ?>
                                        </td>
                                        <td><?php echo getStatusBadge($booking['status']); ?></td>
                                        <td><?php echo formatDate($booking['created_at']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                    data-bs-target="#updateModal<?php echo $booking['id']; ?>" title="Update">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="bookings.php?delete=<?php echo $booking['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this booking?')"
                                               title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    
                                    <!-- Update Modal -->
                                    <div class="modal fade" id="updateModal<?php echo $booking['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Update Booking #<?php echo $booking['id']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="Pending" <?php echo $booking['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="In Transit" <?php echo $booking['status'] === 'In Transit' ? 'selected' : ''; ?>>In Transit</option>
                                                                <option value="Delivered" <?php echo $booking['status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                                <option value="Cancelled" <?php echo $booking['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Assign Driver</label>
                                                            <select name="driver_id" class="form-select">
                                                                <option value="">-- Select Driver --</option>
                                                                <?php foreach ($drivers as $driver): ?>
                                                                    <option value="<?php echo $driver['id']; ?>" 
                                                                        <?php echo $booking['driver_id'] == $driver['id'] ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($driver['name']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-0">
                                                            <label class="form-label">Customer</label>
                                                            <p class="form-control-static"><?php echo htmlspecialchars($booking['customer_name']); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="card-footer bg-white">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Summary -->
        <?php if (!empty($bookings)): ?>
            <div class="mt-3 text-muted">
                <small>
                    Showing <?php echo count($bookings); ?> of <?php echo $totalBookings; ?> booking(s)
                    <?php if ($statusFilter): ?> with status "<?php echo $statusFilter; ?>"<?php endif; ?>
                    <?php if ($search): ?> matching "<?php echo htmlspecialchars($search); ?>"<?php endif; ?>
                </small>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

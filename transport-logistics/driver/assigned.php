<?php
$pageTitle = 'Assigned Jobs';
require_once '../includes/auth_check.php';
requireDriver();

$driverId = getUserId();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Filter by status
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Get total count
$countQuery = "SELECT COUNT(*) FROM bookings WHERE driver_id = ?";
$countParams = [$driverId];

if ($statusFilter && in_array($statusFilter, ['Pending', 'In Transit', 'Delivered'])) {
    $countQuery .= " AND status = ?";
    $countParams[] = $statusFilter;
}

$stmt = $pdo->prepare($countQuery);
$stmt->execute($countParams);
$totalBookings = $stmt->fetchColumn();
$totalPages = ceil($totalBookings / $perPage);

// Get bookings with pagination
$query = "SELECT b.*, u.name as customer_name, u.phone as customer_phone, u.email as customer_email
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          WHERE b.driver_id = ?";
$params = [$driverId];

if ($statusFilter && in_array($statusFilter, ['Pending', 'In Transit', 'Delivered'])) {
    $query .= " AND b.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-success text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-list me-2"></i>Assigned Jobs</h2>
                <p class="mb-0 opacity-75">View and manage all your assigned deliveries</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Jobs List -->
<section class="py-5">
    <div class="container">
        <!-- Filter -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Filter by Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="In Transit" <?php echo $statusFilter === 'In Transit' ? 'selected' : ''; ?>>In Transit</option>
                            <option value="Delivered" <?php echo $statusFilter === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                    <?php if ($statusFilter): ?>
                        <div class="col-md-2">
                            <a href="assigned.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>Clear
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Jobs Table -->
        <div class="card border-0 shadow">
            <div class="card-body p-0">
                <?php if (empty($bookings)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box-open text-muted fa-4x mb-3"></i>
                        <h5 class="text-muted">No jobs found</h5>
                        <p class="text-muted mb-0">
                            <?php echo $statusFilter ? "No jobs with status '$statusFilter' found." : "You don't have any assigned jobs yet."; ?>
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
                                    <th>Goods</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($booking['customer_name']); ?>
                                            <?php if ($booking['customer_phone']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($booking['customer_phone']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(substr($booking['pickup_location'], 0, 25)) . (strlen($booking['pickup_location']) > 25 ? '...' : ''); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(substr($booking['delivery_location'], 0, 25)) . (strlen($booking['delivery_location']) > 25 ? '...' : ''); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($booking['goods_type']); ?>
                                            <?php if ($booking['weight']): ?>
                                                <br><small class="text-muted"><?php echo $booking['weight']; ?> kg</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo getStatusBadge($booking['status']); ?></td>
                                        <td>
                                            <a href="update_status.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-success" title="Update Status">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
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
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?>">
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
                    Showing <?php echo count($bookings); ?> of <?php echo $totalBookings; ?> job(s)
                    <?php if ($statusFilter): ?> with status "<?php echo $statusFilter; ?>"<?php endif; ?>
                </small>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

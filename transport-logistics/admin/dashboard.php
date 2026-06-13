<?php
$pageTitle = 'Admin Dashboard';
require_once '../includes/auth_check.php';
requireAdmin();

// Get statistics
try {
    // Total counts
    $totalUsers = getTotalUsers();
    $totalCustomers = getTotalUsers('customer');
    $totalDrivers = getTotalUsers('driver');
    $totalBookings = getTotalBookings();
    $pendingBookings = getTotalBookings('Pending');
    $inTransitBookings = getTotalBookings('In Transit');
    $deliveredBookings = getTotalBookings('Delivered');
    
    // Recent bookings
    $recentBookings = getRecentBookings(5);
    
    // Recent users
    $stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $recentUsers = $stmt->fetchAll();
    
    // Recent messages
    $stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
    $recentMessages = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $totalUsers = $totalCustomers = $totalDrivers = $totalBookings = 0;
    $pendingBookings = $inTransitBookings = $deliveredBookings = 0;
    $recentBookings = $recentUsers = $recentMessages = [];
}

require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-danger text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
                <p class="mb-0 opacity-75">Welcome back, <?php echo htmlspecialchars(getUserName()); ?>!</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="../logout.php" class="btn btn-light">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Stats -->
<section class="py-4">
    <div class="container">
        <h5 class="mb-3">Overview</h5>
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="dashboard-card dashboard-card-primary">
                    <div class="dashboard-card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="dashboard-card-value"><?php echo $totalUsers; ?></div>
                    <div class="dashboard-card-label">Total Users</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="dashboard-card dashboard-card-success">
                    <div class="dashboard-card-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="dashboard-card-value"><?php echo $totalCustomers; ?></div>
                    <div class="dashboard-card-label">Customers</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="dashboard-card dashboard-card-info">
                    <div class="dashboard-card-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="dashboard-card-value"><?php echo $totalDrivers; ?></div>
                    <div class="dashboard-card-label">Drivers</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="dashboard-card dashboard-card-warning">
                    <div class="dashboard-card-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="dashboard-card-value"><?php echo $totalBookings; ?></div>
                    <div class="dashboard-card-label">Total Bookings</div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mt-1">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0"><?php echo $pendingBookings; ?></h5>
                            <p class="text-muted mb-0">Pending Bookings</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-shipping-fast text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0"><?php echo $inTransitBookings; ?></h5>
                            <p class="text-muted mb-0">In Transit</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0"><?php echo $deliveredBookings; ?></h5>
                            <p class="text-muted mb-0">Delivered</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Actions -->
<section class="py-4">
    <div class="container">
        <h5 class="mb-3">Quick Actions</h5>
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <a href="users.php" class="card border-0 shadow-sm text-decoration-none h-100">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-users text-primary fa-2x mb-2"></i>
                        <h6 class="mb-0 text-dark">Manage Users</h6>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="bookings.php" class="card border-0 shadow-sm text-decoration-none h-100">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-box text-warning fa-2x mb-2"></i>
                        <h6 class="mb-0 text-dark">Manage Bookings</h6>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="contacts.php" class="card border-0 shadow-sm text-decoration-none h-100">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-envelope text-info fa-2x mb-2"></i>
                        <h6 class="mb-0 text-dark">View Messages</h6>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="../register.php" class="card border-0 shadow-sm text-decoration-none h-100">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-user-plus text-success fa-2x mb-2"></i>
                        <h6 class="mb-0 text-dark">Add User</h6>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Recent Activity -->
<section class="py-4">
    <div class="container">
        <div class="row g-4">
            <!-- Recent Bookings -->
            <div class="col-lg-6">
                <div class="card border-0 shadow h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-box me-2"></i>Recent Bookings</h5>
                        <a href="bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentBookings)): ?>
                            <div class="text-center py-4">
                                <p class="text-muted mb-0">No bookings yet</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentBookings as $booking): ?>
                                            <tr>
                                                <td>#<?php echo $booking['id']; ?></td>
                                                <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                                <td><?php echo getStatusBadge($booking['status']); ?></td>
                                                <td><?php echo formatDate($booking['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Users -->
            <div class="col-lg-6">
                <div class="card border-0 shadow h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Recent Users</h5>
                        <a href="users.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentUsers)): ?>
                            <div class="text-center py-4">
                                <p class="text-muted mb-0">No users yet</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Role</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentUsers as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'driver' ? 'info' : 'success'); 
                                                    ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($user['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Messages -->
<section class="py-4 mb-4">
    <div class="container">
        <div class="card border-0 shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Recent Messages</h5>
                <a href="contacts.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentMessages)): ?>
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">No messages yet</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMessages as $message): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($message['message'], 0, 50)) . (strlen($message['message']) > 50 ? '...' : ''); ?></td>
                                        <td><?php echo formatDate($message['created_at']); ?></td>
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

<?php require_once '../includes/footer.php'; ?>

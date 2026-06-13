<?php
$pageTitle = 'Manage Users';
require_once '../includes/auth_check.php';
requireAdmin();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Filter by role
$roleFilter = isset($_GET['role']) ? sanitize($_GET['role']) : '';

// Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    
    // Prevent deleting own account
    if ($deleteId === getUserId()) {
        $_SESSION['error'] = "You cannot delete your own account.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
            $stmt->execute([$deleteId]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "User deleted successfully.";
            } else {
                $_SESSION['error'] = "Cannot delete admin users or user not found.";
            }
        } catch (PDOException $e) {
            error_log("Delete user error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while deleting the user.";
        }
    }
    
    redirect('users.php');
}

// Build query
$whereClause = "WHERE 1=1";
$params = [];

if ($roleFilter && in_array($roleFilter, ['customer', 'driver', 'admin'])) {
    $whereClause .= " AND role = ?";
    $params[] = $roleFilter;
}

if ($search) {
    $whereClause .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Get total count
$countQuery = "SELECT COUNT(*) FROM users $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalUsers = $stmt->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

// Get users with pagination
$query = "SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-danger text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-users me-2"></i>Manage Users</h2>
                <p class="mb-0 opacity-75">View and manage all system users</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Users List -->
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
                            <input type="text" name="search" class="form-control" placeholder="Name, email or phone" 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filter by Role</label>
                        <select name="role" class="form-select">
                            <option value="">All Roles</option>
                            <option value="customer" <?php echo $roleFilter === 'customer' ? 'selected' : ''; ?>>Customer</option>
                            <option value="driver" <?php echo $roleFilter === 'driver' ? 'selected' : ''; ?>>Driver</option>
                            <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                    <?php if ($search || $roleFilter): ?>
                        <div class="col-md-2">
                            <a href="users.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>Clear
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="card border-0 shadow">
            <div class="card-body p-0">
                <?php if (empty($users)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users text-muted fa-4x mb-3"></i>
                        <h5 class="text-muted">No users found</h5>
                        <p class="text-muted mb-0">
                            <?php echo $search || $roleFilter ? "No users match your search criteria." : "No users in the system yet."; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>#<?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo $user['phone'] ? htmlspecialchars($user['phone']) : '-'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'driver' ? 'info' : 'success'); 
                                            ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                        <td>
                                            <?php if ($user['id'] !== getUserId() && $user['role'] !== 'admin'): ?>
                                                <a href="users.php?delete=<?php echo $user['id']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')"
                                                   title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $roleFilter ? '&role=' . $roleFilter : ''; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $roleFilter ? '&role=' . $roleFilter : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $roleFilter ? '&role=' . $roleFilter : ''; ?>">
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
        <?php if (!empty($users)): ?>
            <div class="mt-3 text-muted">
                <small>
                    Showing <?php echo count($users); ?> of <?php echo $totalUsers; ?> user(s)
                    <?php if ($roleFilter): ?> with role "<?php echo $roleFilter; ?>"<?php endif; ?>
                    <?php if ($search): ?> matching "<?php echo htmlspecialchars($search); ?>"<?php endif; ?>
                </small>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

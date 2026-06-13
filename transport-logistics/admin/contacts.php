<?php
$pageTitle = 'Contact Messages';
require_once '../includes/auth_check.php';
requireAdmin();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Delete message
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$deleteId]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Message deleted successfully.";
        } else {
            $_SESSION['error'] = "Message not found.";
        }
    } catch (PDOException $e) {
        error_log("Delete message error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while deleting the message.";
    }
    
    redirect('contacts.php');
}

// Get total count
$stmt = $pdo->query("SELECT COUNT(*) FROM contacts");
$totalMessages = $stmt->fetchColumn();
$totalPages = ceil($totalMessages / $perPage);

// Get messages with pagination
$stmt = $pdo->prepare("SELECT * FROM contacts ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$perPage, $offset]);
$messages = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-danger text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-envelope me-2"></i>Contact Messages</h2>
                <p class="mb-0 opacity-75">View messages from customers</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Messages List -->
<section class="py-5">
    <div class="container">
        <!-- Messages Table -->
        <div class="card border-0 shadow">
            <div class="card-body p-0">
                <?php if (empty($messages)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-envelope-open text-muted fa-4x mb-3"></i>
                        <h5 class="text-muted">No messages yet</h5>
                        <p class="text-muted mb-0">Messages from the contact form will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $message): ?>
                                    <tr>
                                        <td>#<?php echo $message['id']; ?></td>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                                <?php echo htmlspecialchars($message['email']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars(substr($message['message'], 0, 50)) . (strlen($message['message']) > 50 ? '...' : ''); ?>
                                        </td>
                                        <td><?php echo formatDateTime($message['created_at']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                    data-bs-target="#viewModal<?php echo $message['id']; ?>" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="contacts.php?delete=<?php echo $message['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this message?')"
                                               title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $message['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Message from <?php echo htmlspecialchars($message['name']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="text-muted small">From</label>
                                                            <p class="mb-0"><?php echo htmlspecialchars($message['name']); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="text-muted small">Email</label>
                                                            <p class="mb-0">
                                                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                                                    <?php echo htmlspecialchars($message['email']); ?>
                                                                </a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="text-muted small">Subject</label>
                                                        <p class="mb-0 fw-medium"><?php echo htmlspecialchars($message['subject']); ?></p>
                                                    </div>
                                                    <div class="mb-0">
                                                        <label class="text-muted small">Message</label>
                                                        <div class="bg-light p-3 rounded">
                                                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <label class="text-muted small">Received</label>
                                                        <p class="mb-0"><?php echo formatDateTime($message['created_at']); ?></p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>" 
                                                       class="btn btn-primary">
                                                        <i class="fas fa-reply me-2"></i>Reply
                                                    </a>
                                                </div>
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
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">
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
        <?php if (!empty($messages)): ?>
            <div class="mt-3 text-muted">
                <small>Showing <?php echo count($messages); ?> of <?php echo $totalMessages; ?> message(s)</small>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

<?php
$pageTitle = 'Admin Login';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in as admin
if (isLoggedIn() && getUserRole() === 'admin') {
    redirect('dashboard.php');
}

$errors = [];

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (isEmpty($email)) {
        $errors[] = "Email address is required.";
    }
    
    if (isEmpty($password)) {
        $errors[] = "Password is required.";
    }
    
    // If no validation errors, attempt login
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? AND role = 'admin'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful - set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                $_SESSION['success'] = "Welcome back, Admin " . $user['name'] . "!";
                redirect('dashboard.php');
            } else {
                $errors[] = "Invalid admin credentials. Please try again.";
            }
            
        } catch (PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again later.";
        }
    }
}

require_once '../includes/header.php';
?>

<!-- Auth Section -->
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="auth-card">
                    <div class="auth-header" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">
                        <h3><i class="fas fa-lock me-2"></i>Admin Login</h3>
                        <p class="mb-0">Restricted Access - Authorized Personnel Only</p>
                    </div>
                    
                    <div class="auth-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="adminLoginForm" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Admin Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                                           placeholder="Enter admin email" required autofocus>
                                </div>
                                <div class="invalid-feedback">Please enter your email address.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Please enter your password.</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login as Admin
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="auth-footer">
                        <p class="mb-2">
                            <a href="../login.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Back to User Login
                            </a>
                        </p>
                        <p class="mb-0 text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Default: admin@example.com / Admin@123
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

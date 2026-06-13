<?php
$pageTitle = 'Login';
require_once 'includes/config.php';

require_once 'includes/auth_check.php';
// Redirect if already logged in
redirectIfLoggedIn();

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
            $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login successful - set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                $_SESSION['success'] = "Welcome back, " . $user['name'] . "!";

                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        redirect('admin/dashboard.php');
                        break;
                    case 'driver':
                        redirect('driver/dashboard.php');
                        break;
                    case 'customer':
                    default:
                        redirect('dashboard.php');
                        break;
                }
            } else {
                $errors[] = "Invalid email or password. Please try again.";
            }

        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again later.";
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Auth Section -->
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="auth-card">
                    <div class="auth-header">
                        <h3><i class="fas fa-sign-in-alt me-2"></i>Login</h3>
                        <p class="mb-0">Access your account</p>
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

                        <form method="POST" action="" id="loginForm" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                        placeholder="Enter your email" required autofocus>
                                </div>
                                <div class="invalid-feedback">Please enter your email address.</div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Enter your password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Please enter your password.</div>
                            </div>

                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                                <a href="#" class="text-primary small">Forgot password?</a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="auth-footer">
                        <div class="auth-divider">
                            <span>OR</span>
                        </div>
                        <p class="mb-0">
                            Don't have an account?
                            <a href="register.php" class="fw-bold">Register here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
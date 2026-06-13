<?php
$pageTitle = 'Register';
require_once 'includes/config.php';

require_once 'includes/auth_check.php';
// Redirect if already logged in
redirectIfLoggedIn();

$errors = [];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? 'customer');

    // Server-side validation
    if (isEmpty($name)) {
        $errors[] = "Full name is required.";
    } elseif (strlen($name) < 3) {
        $errors[] = "Name must be at least 3 characters long.";
    }

    if (isEmpty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!isValidEmail($email)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (isEmpty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter, one lowercase letter, and one number.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (isEmpty($phone)) {
        $errors[] = "Phone number is required.";
    } elseif (!isValidPhone($phone)) {
        $errors[] = "Please enter a valid phone number (10-15 digits).";
    }

    if (!in_array($role, ['customer', 'driver'])) {
        $errors[] = "Invalid role selected.";
    }

    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Email address is already registered. Please use a different email or login.";
            }
        } catch (PDOException $e) {
            error_log("Registration check error: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again later.";
        }
    }

    // If no errors, create user
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword, $phone, $role]);

            $_SESSION['success'] = "Registration successful! Please login with your credentials.";
            redirect('login.php');

        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors[] = "An error occurred during registration. Please try again later.";
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Auth Section -->
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="auth-card">
                    <div class="auth-header">
                        <h3><i class="fas fa-user-plus me-2"></i>Create Account</h3>
                        <p class="mb-0">Register as a Customer or Driver</p>
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

                        <form method="POST" action="" id="registerForm" novalidate>
                            <!-- Role Selection -->
                            <div class="mb-3">
                                <label class="form-label">Register As <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="form-check card p-3 text-center">
                                            <input class="form-check-input" type="radio" name="role" id="roleCustomer"
                                                value="customer" <?php echo (isset($role) && $role === 'customer') || !isset($role) ? 'checked' : ''; ?>>
                                            <label class="form-check-label d-block mt-2" for="roleCustomer">
                                                <i class="fas fa-user text-primary fa-2x mb-2 d-block"></i>
                                                <strong>Customer</strong>
                                                <small class="d-block text-muted">Book shipments</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check card p-3 text-center">
                                            <input class="form-check-input" type="radio" name="role" id="roleDriver"
                                                value="driver" <?php echo isset($role) && $role === 'driver' ? 'checked' : ''; ?>>
                                            <label class="form-check-label d-block mt-2" for="roleDriver">
                                                <i class="fas fa-truck text-success fa-2x mb-2 d-block"></i>
                                                <strong>Driver</strong>
                                                <small class="d-block text-muted">Deliver shipments</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="name" class="form-label">Full Name <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="name" name="name"
                                            value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"
                                            placeholder="Enter your full name" required minlength="3">
                                    </div>
                                    <div class="invalid-feedback">Please enter your full name (at least 3 characters).
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="email" class="form-label">Email Address <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                            placeholder="Enter your email" required>
                                    </div>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>

                                <div class="col-12">
                                    <label for="phone" class="form-label">Phone Number <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                            value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>"
                                            placeholder="Enter your phone number" required>
                                    </div>
                                    <div class="form-text">Format: 10-15 digits</div>
                                    <div class="invalid-feedback">Please enter a valid phone number.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Create password" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Min 6 chars, 1 uppercase, 1 lowercase, 1 number</div>
                                    <div class="invalid-feedback">Password does not meet requirements.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Confirm Password <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="confirm_password"
                                            name="confirm_password" placeholder="Confirm password" required>
                                    </div>
                                    <div class="invalid-feedback">Passwords do not match.</div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy
                                            Policy</a>
                                    </label>
                                    <div class="invalid-feedback">You must agree to the terms.</div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary w-100 btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Register
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="auth-footer">
                        <div class="auth-divider">
                            <span>OR</span>
                        </div>
                        <p class="mb-0">
                            Already have an account?
                            <a href="login.php" class="fw-bold">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
<?php
require_once 'functions.php';

// Determine current page for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$isAdmin = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$isDriver = (strpos($_SERVER['PHP_SELF'], '/driver/') !== false);

// Get user role
$userRole = getUserRole();
$isLoggedIn = isLoggedIn();

// Base paths for links
$adminBase = $isAdmin ? '' : 'admin/';
$driverBase = $isDriver ? '' : 'driver/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts: Inter + Space Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ($isAdmin || $isDriver) ? '../' : ''; ?>css/style.css">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ($isAdmin || $isDriver) ? '../' : ''; ?>favicon.ico">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo ($isAdmin || $isDriver) ? '../' : ''; ?>index.php">
                <div class="brand-icon">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <span class="fw-bold ms-2"><?php echo APP_NAME; ?></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (!$isLoggedIn || $userRole === 'customer'): ?>
                        <!-- Public Menu -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>"
                                href="<?php echo ($isAdmin || $isDriver) ? '../' : ''; ?>index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>"
                                href="<?php echo ($isAdmin || $isDriver) ? '../' : ''; ?>about.php">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'services' ? 'active' : ''; ?>"
                                href="<?php echo ($isAdmin || $isDriver) ? '../' : ''; ?>services.php">Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'track' ? 'active' : ''; ?>"
                                href="<?php echo ($isAdmin || $isDriver) ? '../' : ''; ?>track.php">Track</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>"
                                href="<?php echo ($isAdmin || $isDriver) ? '../' : ''; ?>contact.php">Contact</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($isLoggedIn && $userRole === 'customer'): ?>
                        <!-- Customer Menu -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>"
                                href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'bookings' ? 'active' : ''; ?>"
                                href="bookings.php">My Bookings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'create_booking' ? 'active' : ''; ?>"
                                href="create_booking.php">New Booking</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($isLoggedIn && $userRole === 'driver'): ?>
                        <!-- Driver Menu -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>"
                                href="<?php echo $driverBase; ?>dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'assigned' ? 'active' : ''; ?>"
                                href="<?php echo $driverBase; ?>assigned.php">Assigned Jobs</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                        <!-- Admin Menu -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>"
                                href="<?php echo $adminBase; ?>dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'users' ? 'active' : ''; ?>"
                                href="<?php echo $adminBase; ?>users.php">Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'bookings' ? 'active' : ''; ?>"
                                href="<?php echo $adminBase; ?>bookings.php">Bookings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'contacts' ? 'active' : ''; ?>"
                                href="<?php echo $adminBase; ?>contacts.php">Messages</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo htmlspecialchars(getUserName()); ?>
                                <span class="badge bg-<?php
                                echo $userRole === 'admin' ? 'danger' : ($userRole === 'driver' ? 'info' : 'success');
                                ?> ms-1"><?php echo ucfirst($userRole); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="<?php
                                    echo $userRole === 'admin' ? $adminBase . 'dashboard.php' : ($userRole === 'driver' ? $driverBase . 'dashboard.php' : 'dashboard.php');
                                    ?>">
                                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger"
                                        href="<?php echo ($isAdmin || $isDriver) ? '../' : ''; ?>logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>             
                        <li class="nav-item">
                            <a class="nav-link btn-nav-login <?php echo $currentPage === 'login' ? 'active' : ''; ?>"
                                href="<?php echo ($isAdmin || $isDriver) ? '../' : ''; ?>login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item ms-2">
                            <a class="nav-link btn-nav-register <?php echo $currentPage === 'register' ? 'active' : ''; ?>"
                                href="<?php echo ($isAdmin || $isDriver) ? '../' : ''; ?>register.php">
                                <i class="fas fa-rocket me-1"></i>Get Started
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container mt-3">
        <?php if (isset($_SESSION['success'])): ?>
            <?php echo showSuccess($_SESSION['success']); ?>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <?php echo showError($_SESSION['error']); ?>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['warning'])): ?>
            <?php echo showWarning($_SESSION['warning']); ?>
            <?php unset($_SESSION['warning']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['info'])): ?>
            <?php echo showInfo($_SESSION['info']); ?>
            <?php unset($_SESSION['info']); ?>
        <?php endif; ?>
    </div>

    <!-- Main Content Wrapper -->
    <main class="main-content">
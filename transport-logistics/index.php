<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

// Get statistics for homepage
try {
    $totalBookings = getTotalBookings();
    $totalCustomers = getTotalUsers('customer');
    $totalDrivers = getTotalUsers('driver');
    $deliveredBookings = getTotalBookings('Delivered');
} catch (Exception $e) {
    $totalBookings = 0;
    $totalCustomers = 0;
    $totalDrivers = 0;
    $deliveredBookings = 0;
}
?>

<!-- ==================== HERO SECTION ==================== -->
<section class="hero-section">
    <!-- Animated grid overlay -->
    <div class="hero-grid"></div>

    <div class="container">
        <div class="row align-items-center g-5">
            <!-- Left Content -->
            <div class="col-lg-6 hero-content">

                <div class="hero-badge fade-in">
                    <span></span>
                    🚀 #1 Trusted Logistics Platform
                </div>

                <h1 class="hero-title fade-in">
                    Reliable Transport
                    <span class="gradient-text">&amp; Logistics</span>
                    Solutions
                </h1>

                <p class="hero-subtitle fade-in">
                    We provide efficient, secure, and timely transportation services for all your
                    logistics needs. From goods transportation to warehousing — we've got you covered.
                </p>

                <div class="hero-buttons fade-in">
                    <?php if (!isLoggedIn()): ?>
                        <a href="register.php" class="btn btn-primary btn-lg" id="hero-get-started">
                            <i class="fas fa-rocket me-2"></i>Get Started Free
                        </a>
                        <a href="login.php" class="btn btn-outline-light btn-lg" id="hero-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    <?php else: ?>
                        <?php if (getUserRole() === 'customer'): ?>
                            <a href="create_booking.php" class="btn btn-primary btn-lg" id="hero-create-booking">
                                <i class="fas fa-plus-circle me-2"></i>Create Booking
                            </a>
                            <a href="dashboard.php" class="btn btn-outline-light btn-lg" id="hero-dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        <?php elseif (getUserRole() === 'driver'): ?>
                            <a href="driver/dashboard.php" class="btn btn-primary btn-lg" id="hero-driver-dash">
                                <i class="fas fa-tachometer-alt me-2"></i>Driver Dashboard
                            </a>
                        <?php elseif (getUserRole() === 'admin'): ?>
                            <a href="admin/dashboard.php" class="btn btn-primary btn-lg" id="hero-admin-panel">
                                <i class="fas fa-cog me-2"></i>Admin Panel
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Quick Stats Row -->
                <div class="hero-stats fade-in">
                    <div class="hero-stat">
                        <span class="hero-stat-num"><?php echo number_format($totalBookings); ?>+</span>
                        <span class="hero-stat-label">Bookings</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-num"><?php echo number_format($totalCustomers); ?>+</span>
                        <span class="hero-stat-label">Customers</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-num">99%</span>
                        <span class="hero-stat-label">On-time Rate</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-num">24/7</span>
                        <span class="hero-stat-label">Support</span>
                    </div>
                </div>
            </div>

            <!-- Right Visual -->
            <div class="col-lg-6 hero-visual d-none d-lg-flex">
                <div class="hero-truck-wrapper">
                    <div class="hero-circle-bg"></div>
                    <div style="display:flex;align-items:center;justify-content:center;height:100%;">
                        <i class="fas fa-truck-fast hero-truck-icon"></i>
                    </div>

                    <!-- Floating info cards -->
                    <div class="float-card float-card-1">
                        <div class="float-card-icon" style="background:linear-gradient(135deg,#6366f1,#818cf8);">
                            <i class="fas fa-map-marker-alt" style="color:#fff;"></i>
                        </div>
                        <div class="float-card-text">
                            <div class="float-card-title">Live Tracking</div>
                            <div class="float-card-sub">Real-time updates</div>
                        </div>
                    </div>

                    <div class="float-card float-card-2">
                        <div class="float-card-icon" style="background:linear-gradient(135deg,#10b981,#059669);">
                            <i class="fas fa-shield-alt" style="color:#fff;"></i>
                        </div>
                        <div class="float-card-text">
                            <div class="float-card-title">100% Secure</div>
                            <div class="float-card-sub">Insured delivery</div>
                        </div>
                    </div>

                    <div class="float-card float-card-3">
                        <div class="float-card-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                            <i class="fas fa-bolt" style="color:#fff;"></i>
                        </div>
                        <div class="float-card-text">
                            <div class="float-card-title">Express Delivery</div>
                            <div class="float-card-sub">Same day available</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== STATS SECTION ==================== -->
<section class="stats-section">
    <div class="container">
        <div class="row g-0">
            <div class="col-md-3 col-6">
                <div class="stat-item reveal">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-number" data-target="<?php echo $totalBookings; ?>" data-suffix="+">
                        <?php echo number_format($totalBookings); ?>+
                    </div>
                    <div class="stat-label">Total Bookings</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item reveal">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number" data-target="<?php echo $totalCustomers; ?>" data-suffix="+">
                        <?php echo number_format($totalCustomers); ?>+
                    </div>
                    <div class="stat-label">Happy Customers</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item reveal">
                    <div class="stat-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-number" data-target="<?php echo $totalDrivers; ?>" data-suffix="+">
                        <?php echo number_format($totalDrivers); ?>+
                    </div>
                    <div class="stat-label">Pro Drivers</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item reveal">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number" data-target="<?php echo $deliveredBookings; ?>" data-suffix="+">
                        <?php echo number_format($deliveredBookings); ?>+
                    </div>
                    <div class="stat-label">Deliveries Done</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== FEATURES SECTION ==================== -->
<section class="features-section">
    <div class="container">
        <div class="section-title reveal">
            <div class="section-badge">✨ Why Choose Us</div>
            <h2>Everything You Need <span class="gradient-text">In One Place</span></h2>
            <p>We offer comprehensive logistics solutions tailored precisely to your needs</p>
        </div>
        <div class="row g-4 stagger-children">
            <div class="col-md-4">
                <div class="feature-card reveal">
                    <div class="feature-card-inner">
                        <div class="feature-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h4 class="feature-title">Fast Delivery</h4>
                        <p class="feature-description">
                            Quick and efficient delivery services ensuring your goods reach their destination
                            on time, every time, without compromise.
                        </p>
                        <div class="feature-arrow">
                            Explore <i class="fas fa-arrow-right ms-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card reveal">
                    <div class="feature-card-inner">
                        <div class="feature-icon" style="background:linear-gradient(135deg,#10b981,#059669);">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="feature-title">Secure Transport</h4>
                        <p class="feature-description">
                            Your goods are handled with utmost care and security throughout the entire
                            transportation and delivery process.
                        </p>
                        <div class="feature-arrow">
                            Explore <i class="fas fa-arrow-right ms-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card reveal">
                    <div class="feature-card-inner">
                        <div class="feature-icon" style="background:linear-gradient(135deg,#06b6d4,#0891b2);">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4 class="feature-title">Real-time Tracking</h4>
                        <p class="feature-description">
                            Track your shipments in real-time and stay updated on the live status of
                            every delivery with precision.
                        </p>
                        <div class="feature-arrow">
                            Explore <i class="fas fa-arrow-right ms-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card reveal">
                    <div class="feature-card-inner">
                        <div class="feature-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <h4 class="feature-title">Warehousing</h4>
                        <p class="feature-description">
                            Safe and secure warehousing facilities for short-term and long-term storage
                            with full inventory management.
                        </p>
                        <div class="feature-arrow">
                            Explore <i class="fas fa-arrow-right ms-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card reveal">
                    <div class="feature-card-inner">
                        <div class="feature-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4 class="feature-title">24/7 Support</h4>
                        <p class="feature-description">
                            Our dedicated support team is available round the clock to assist you
                            with any queries or concerns.
                        </p>
                        <div class="feature-arrow">
                            Explore <i class="fas fa-arrow-right ms-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card reveal">
                    <div class="feature-card-inner">
                        <div class="feature-icon" style="background:linear-gradient(135deg,#f43f5e,#e11d48);">
                            <i class="fas fa-tags"></i>
                        </div>
                        <h4 class="feature-title">Competitive Pricing</h4>
                        <p class="feature-description">
                            Affordable rates without compromising on the quality and reliability
                            of our premium services.
                        </p>
                        <div class="feature-arrow">
                            Explore <i class="fas fa-arrow-right ms-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== SERVICES SECTION ==================== -->
<section class="services-section">
    <div class="container">
        <div class="section-title reveal">
            <div class="section-badge">🚛 Our Services</div>
            <h2>Comprehensive <span class="gradient-text">Logistics Solutions</span></h2>
            <p>End-to-end transportation services for all your business needs</p>
        </div>
        <div class="row g-4 stagger-children">
            <div class="col-md-4">
                <div class="service-card reveal">
                    <div class="service-image">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="service-content">
                        <h4 class="service-title">Goods Transportation</h4>
                        <p class="service-description">
                            Reliable transportation services for all types of goods, from small
                            packages to large cargo — nationwide coverage.
                        </p>
                        <a href="services.php" class="btn btn-outline-primary btn-sm" id="svc-goods">
                            <i class="fas fa-arrow-right me-1"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card reveal">
                    <div class="service-image" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="service-content">
                        <h4 class="service-title">Warehousing</h4>
                        <p class="service-description">
                            Secure storage facilities with inventory management, order fulfillment
                            and distribution services.
                        </p>
                        <a href="services.php" class="btn btn-outline-primary btn-sm" id="svc-warehouse">
                            <i class="fas fa-arrow-right me-1"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card reveal">
                    <div class="service-image" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="service-content">
                        <h4 class="service-title">Express Delivery</h4>
                        <p class="service-description">
                            Express delivery services for time-sensitive shipments with guaranteed
                            delivery times and live status.
                        </p>
                        <a href="services.php" class="btn btn-outline-primary btn-sm" id="svc-express">
                            <i class="fas fa-arrow-right me-1"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== CTA SECTION ==================== -->
<section class="cta-section">
    <div class="container">
        <div class="cta-card reveal">
            <div class="section-badge">🎯 Get Started Today</div>
            <h2>Ready to <span class="gradient-text">Transform</span> Your Logistics?</h2>
            <p class="text-secondary">
                Join thousands of satisfied customers who trust us with their logistics needs.
                Create an account today and experience seamless transportation services.
            </p>
            <?php if (!isLoggedIn()): ?>
                <div style="display:flex;justify-content:center;gap:1rem;flex-wrap:wrap;">
                    <a href="register.php" class="btn btn-primary btn-lg" id="cta-register">
                        <i class="fas fa-rocket me-2"></i>Register Now — It's Free
                    </a>
                    <a href="contact.php" class="btn btn-outline-primary btn-lg" id="cta-contact">
                        <i class="fas fa-envelope me-2"></i>Contact Us
                    </a>
                </div>
            <?php else: ?>
                <a href="contact.php" class="btn btn-primary btn-lg" id="cta-contact-logged">
                    <i class="fas fa-envelope me-2"></i>Contact Us
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<?php
// Determine path prefix for assets
$isAdmin = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$isDriver = (strpos($_SERVER['PHP_SELF'], '/driver/') !== false);
$pathPrefix = ($isAdmin || $isDriver) ? '../' : '';
?>
    </main><!-- End Main Content -->
    
    <!-- Footer -->
    <footer class="footer bg-dark text-light py-5 mt-auto">
        <div class="container">
            <div class="row g-4">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="mb-3">
                        <i class="fas fa-truck-fast me-2 text-primary"></i>
                        <?php echo APP_NAME; ?>
                    </h5>
                    <p class="text-muted">
                        Your trusted partner for reliable transportation and logistics solutions. 
                        We deliver excellence with every shipment.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-light" title="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?php echo $pathPrefix; ?>index.php" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="<?php echo $pathPrefix; ?>about.php" class="text-muted text-decoration-none">About Us</a></li>
                        <li><a href="<?php echo $pathPrefix; ?>services.php" class="text-muted text-decoration-none">Services</a></li>
                        <li><a href="<?php echo $pathPrefix; ?>track.php" class="text-muted text-decoration-none">Track</a></li>
                        <li><a href="<?php echo $pathPrefix; ?>contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Services -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="mb-3">Our Services</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?php echo $pathPrefix; ?>services.php" class="text-muted text-decoration-none">Goods Transportation</a></li>
                        <li><a href="<?php echo $pathPrefix; ?>services.php" class="text-muted text-decoration-none">Warehousing</a></li>
                        <li><a href="<?php echo $pathPrefix; ?>services.php" class="text-muted text-decoration-none">Fast Delivery</a></li>
                        <li><a href="<?php echo $pathPrefix; ?>services.php" class="text-muted text-decoration-none">Logistics Solutions</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="mb-3">Contact Us</h6>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            123 Logistics Street, Business City
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2 text-primary"></i>
                            +1 (555) 123-4567
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            info@transportlogistics.com
                        </li>
                        <li>
                            <i class="fas fa-clock me-2 text-primary"></i>
                            Mon - Sat: 9:00 AM - 6:00 PM
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4 border-secondary">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0 text-muted">
                        <small>Designed for College Project</small>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo $pathPrefix; ?>js/main.js"></script>
    
    <!-- Auto-hide alerts after 5 seconds -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>

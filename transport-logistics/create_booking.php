<?php
$pageTitle = 'Create Booking';
require_once 'includes/auth_check.php';
requireCustomer();

$userId = getUserId();
$errors = [];

// Process booking form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickupLocation = sanitize($_POST['pickup_location'] ?? '');
    $deliveryLocation = sanitize($_POST['delivery_location'] ?? '');
    $goodsType = sanitize($_POST['goods_type'] ?? '');
    $weight = sanitize($_POST['weight'] ?? '');
    $deliveryDate = sanitize($_POST['delivery_date'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Validation
    if (isEmpty($pickupLocation)) {
        $errors[] = "Pickup location is required.";
    }
    
    if (isEmpty($deliveryLocation)) {
        $errors[] = "Delivery location is required.";
    }
    
    if (isEmpty($goodsType)) {
        $errors[] = "Goods type is required.";
    }
    
    if (isEmpty($weight)) {
        $errors[] = "Weight is required.";
    } elseif (!is_numeric($weight) || $weight <= 0) {
        $errors[] = "Please enter a valid weight.";
    }
    
    if (isEmpty($deliveryDate)) {
        $errors[] = "Delivery date is required.";
    } elseif (strtotime($deliveryDate) < strtotime(date('Y-m-d'))) {
        $errors[] = "Delivery date cannot be in the past.";
    }
    
    // If no errors, create booking
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, pickup_location, delivery_location, goods_type, weight, delivery_date, notes, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute([$userId, $pickupLocation, $deliveryLocation, $goodsType, $weight, $deliveryDate, $notes]);
            
            $bookingId = $pdo->lastInsertId();
            
            $_SESSION['success'] = "Booking created successfully! Your Booking ID is #" . $bookingId;
            redirect('bookings.php');
            
        } catch (PDOException $e) {
            error_log("Booking creation error: " . $e->getMessage());
            $errors[] = "An error occurred while creating the booking. Please try again.";
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-plus-circle me-2"></i>Create New Booking</h2>
                <p class="mb-0 opacity-75">Fill in the details to book your shipment</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Booking Form -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4 p-lg-5">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="bookingForm" novalidate>
                            <div class="row g-3">
                                <!-- Pickup Location -->
                                <div class="col-12">
                                    <label for="pickup_location" class="form-label">
                                        <i class="fas fa-map-marker-alt text-danger me-2"></i>Pickup Location <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="pickup_location" name="pickup_location" rows="2" 
                                              placeholder="Enter complete pickup address" required><?php echo isset($pickupLocation) ? htmlspecialchars($pickupLocation) : ''; ?></textarea>
                                    <div class="invalid-feedback">Please enter the pickup location.</div>
                                </div>
                                
                                <!-- Delivery Location -->
                                <div class="col-12">
                                    <label for="delivery_location" class="form-label">
                                        <i class="fas fa-flag-checkered text-success me-2"></i>Delivery Location <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="delivery_location" name="delivery_location" rows="2" 
                                              placeholder="Enter complete delivery address" required><?php echo isset($deliveryLocation) ? htmlspecialchars($deliveryLocation) : ''; ?></textarea>
                                    <div class="invalid-feedback">Please enter the delivery location.</div>
                                </div>
                                
                                <!-- Goods Type -->
                                <div class="col-md-6">
                                    <label for="goods_type" class="form-label">
                                        <i class="fas fa-box text-primary me-2"></i>Goods Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="goods_type" name="goods_type" required>
                                        <option value="">Select goods type</option>
                                        <option value="Electronics" <?php echo isset($goodsType) && $goodsType === 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                                        <option value="Furniture" <?php echo isset($goodsType) && $goodsType === 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
                                        <option value="Clothing" <?php echo isset($goodsType) && $goodsType === 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
                                        <option value="Documents" <?php echo isset($goodsType) && $goodsType === 'Documents' ? 'selected' : ''; ?>>Documents</option>
                                        <option value="Food" <?php echo isset($goodsType) && $goodsType === 'Food' ? 'selected' : ''; ?>>Food & Beverages</option>
                                        <option value="Machinery" <?php echo isset($goodsType) && $goodsType === 'Machinery' ? 'selected' : ''; ?>>Machinery</option>
                                        <option value="Automotive" <?php echo isset($goodsType) && $goodsType === 'Automotive' ? 'selected' : ''; ?>>Automotive Parts</option>
                                        <option value="Chemicals" <?php echo isset($goodsType) && $goodsType === 'Chemicals' ? 'selected' : ''; ?>>Chemicals</option>
                                        <option value="Other" <?php echo isset($goodsType) && $goodsType === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                    <div class="invalid-feedback">Please select the goods type.</div>
                                </div>
                                
                                <!-- Weight -->
                                <div class="col-md-6">
                                    <label for="weight" class="form-label">
                                        <i class="fas fa-weight text-primary me-2"></i>Weight (kg) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="weight" name="weight" 
                                               value="<?php echo isset($weight) ? htmlspecialchars($weight) : ''; ?>" 
                                               placeholder="Enter weight" step="0.01" min="0.01" required>
                                        <span class="input-group-text">kg</span>
                                    </div>
                                    <div class="invalid-feedback">Please enter a valid weight.</div>
                                </div>
                                
                                <!-- Delivery Date -->
                                <div class="col-md-6">
                                    <label for="delivery_date" class="form-label">
                                        <i class="fas fa-calendar text-primary me-2"></i>Delivery Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" id="delivery_date" name="delivery_date" 
                                           value="<?php echo isset($deliveryDate) ? htmlspecialchars($deliveryDate) : ''; ?>" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                    <div class="invalid-feedback">Please select a valid delivery date.</div>
                                </div>
                                
                                <!-- Notes -->
                                <div class="col-12">
                                    <label for="notes" class="form-label">
                                        <i class="fas fa-sticky-note text-warning me-2"></i>Additional Notes
                                    </label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                                              placeholder="Any special instructions or notes (optional)"><?php echo isset($notes) ? htmlspecialchars($notes) : ''; ?></textarea>
                                </div>
                            </div>
                            
                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-circle me-2"></i>Create Booking
                                </button>
                                <button type="reset" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-danger btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

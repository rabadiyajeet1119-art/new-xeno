-- Transport & Logistics Management System - Database Schema
-- College Project - MySQL Database

-- Create database (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS transport_logistics;
-- USE transport_logistics;

-- ============================================
-- TABLE: users
-- Stores all user accounts (customers, drivers, admins)
-- ============================================
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    role ENUM('customer','driver','admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: bookings
-- Stores all transport bookings
-- ============================================
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    driver_id INT DEFAULT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    delivery_location VARCHAR(255) NOT NULL,
    goods_type VARCHAR(100),
    weight DECIMAL(8,2),
    delivery_date DATE,
    status ENUM('Pending','In Transit','Delivered','Cancelled') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: contacts
-- Stores contact form submissions
-- ============================================
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    email VARCHAR(150),
    subject VARCHAR(200),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA
-- ============================================

-- Admin Account
-- Email: admin@example.com
-- Password: Admin@123
INSERT INTO users (name, email, password, phone, role) VALUES 
('System Administrator', 'admin@example.com', '$2y$10$fPFZ0C0rHQFTF0jDjeJVe.3XNj1dzBNDH4iXyYasjgOfRDRTvpYg6', '9876543210', 'admin');

-- Sample Customer Account
-- Email: customer@example.com
-- Password: Customer@123
INSERT INTO users (name, email, password, phone, role) VALUES 
('John Customer', 'customer@example.com', '$2y$10$MJBHCKZ9xOiAz4ycfRL2OehTXPiZvNgux.jP1fq8ls90fabB9d/Gm', '9876543211', 'customer');

-- Sample Driver Account
-- Email: driver@example.com
-- Password: Driver@123
INSERT INTO users (name, email, password, phone, role) VALUES 
('Mike Driver', 'driver@example.com', '$2y$10$ichC351g8S5qlpZyp8k9iOYC9EcYBPpvOnvuq0h3t5CdoIfsLqG5m', '9876543212', 'driver');

-- Additional Sample Customers
INSERT INTO users (name, email, password, phone, role) VALUES 
('Sarah Johnson', 'sarah@example.com', '$2y$10$MJBHCKZ9xOiAz4ycfRL2OehTXPiZvNgux.jP1fq8ls90fabB9d/Gm', '9876543213', 'customer'),
('Robert Brown', 'robert@example.com', '$2y$10$MJBHCKZ9xOiAz4ycfRL2OehTXPiZvNgux.jP1fq8ls90fabB9d/Gm', '9876543214', 'customer');

-- Additional Sample Drivers
INSERT INTO users (name, email, password, phone, role) VALUES 
('David Wilson', 'david@example.com', '$2y$10$ichC351g8S5qlpZyp8k9iOYC9EcYBPpvOnvuq0h3t5CdoIfsLqG5m', '9876543215', 'driver'),
('Emma Davis', 'emma@example.com', '$2y$10$ichC351g8S5qlpZyp8k9iOYC9EcYBPpvOnvuq0h3t5CdoIfsLqG5m', '9876543216', 'driver');

-- Sample Bookings
INSERT INTO bookings (user_id, driver_id, pickup_location, delivery_location, goods_type, weight, delivery_date, status, notes) VALUES 
(2, 3, '123 Main Street, New York', '456 Park Avenue, New York', 'Electronics', 25.50, '2024-03-15', 'Delivered', 'Delivered on time'),
(2, 3, '789 Broadway, New York', '321 5th Avenue, New York', 'Furniture', 150.00, '2024-03-20', 'In Transit', 'Handle with care'),
(4, 5, '555 Market Street, San Francisco', '777 Mission Street, San Francisco', 'Documents', 5.00, '2024-03-25', 'Pending', 'Urgent delivery'),
(5, 6, '100 Pine Street, Los Angeles', '200 Oak Street, Los Angeles', 'Clothing', 45.75, '2024-03-22', 'Pending', NULL);

-- Sample Contact Messages
INSERT INTO contacts (name, email, subject, message) VALUES 
('Alice Smith', 'alice@example.com', 'Inquiry about services', 'I would like to know more about your warehousing services. Please contact me.'),
('Bob Johnson', 'bob@example.com', 'Booking issue', 'I am having trouble creating a new booking. Can you help me?'),
('Carol White', 'carol@example.com', 'Feedback', 'Great service! My package arrived on time and in perfect condition.');

-- ============================================
-- END OF DATABASE SCHEMA
-- ============================================

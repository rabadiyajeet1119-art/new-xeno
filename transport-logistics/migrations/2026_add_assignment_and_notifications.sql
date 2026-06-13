-- Add columns to bookings for assignment tracking
ALTER TABLE bookings
  ADD COLUMN accepted_by INT NULL AFTER status,
  ADD COLUMN accepted_at DATETIME NULL AFTER accepted_by,
  ADD COLUMN driver_note VARCHAR(255) NULL AFTER accepted_at;

-- Create notifications table for simple in-app notifications
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type VARCHAR(50) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY(user_id),
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add index on accepted_by for performance
ALTER TABLE bookings ADD INDEX idx_accepted_by (accepted_by);

-- Update status enum to include 'Accepted'
ALTER TABLE bookings MODIFY COLUMN status ENUM('Pending','Accepted','In Transit','Delivered','Cancelled') DEFAULT 'Pending';

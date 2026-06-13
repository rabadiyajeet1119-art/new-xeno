ALTER TABLE bookings MODIFY COLUMN status ENUM('Pending','Accepted','In Transit','Delivered','Cancelled') DEFAULT 'Pending';

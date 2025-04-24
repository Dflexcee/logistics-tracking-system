USE logistics;

ALTER TABLE consignments 
ADD COLUMN payment_status ENUM('pending', 'paid') DEFAULT 'pending',
ADD COLUMN payment_method ENUM('cash', 'bank_transfer', 'card') DEFAULT NULL,
ADD COLUMN amount_paid DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN paid_by ENUM('sender', 'receiver') DEFAULT NULL,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP; 
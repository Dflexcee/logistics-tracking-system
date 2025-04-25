-- Create company_details table
CREATE TABLE IF NOT EXISTS company_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address TEXT,
    logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default company details
INSERT INTO company_details (company_name, email, phone, address, logo) 
VALUES (
    'Logistics Company',
    'info@logistics.com',
    '+1234567890',
    '123 Business Street, City, Country',
    'assets/images/logo.png'
); 
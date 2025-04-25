<?php
require_once 'include/db.php';

// Create company_details table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS company_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($create_table_sql)) {
    echo "Table 'company_details' created or already exists.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
    exit;
}

// Check if there's any data in the table
$check_sql = "SELECT COUNT(*) as count FROM company_details";
$result = $conn->query($check_sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert default company details
    $insert_sql = "INSERT INTO company_details (company_name, email, phone, address) VALUES (
        'CARGOROVER',
        'Konstitucijos Av. 20, Vilnius, LT-09308, Lithuania',
        '+92 (8800) - 9850',
        '88 Broklyn Golden Street, New York'
    )";

    if ($conn->query($insert_sql)) {
        echo "Default company details inserted successfully.<br>";
    } else {
        echo "Error inserting default data: " . $conn->error . "<br>";
    }
} else {
    echo "Company details already exist in the database.<br>";
}

echo "<br>You can now update your company details in the admin dashboard.<br>";
echo "<a href='index.html'>Go to Homepage</a>";
?> 
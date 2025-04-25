<?php
require_once 'include/db.php';

// Check if table exists
$check_table = "SHOW TABLES LIKE 'company_details'";
$result = $conn->query($check_table);

if ($result->num_rows == 0) {
    // Create table if it doesn't exist
    $create_table = "CREATE TABLE company_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        address TEXT,
        logo VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table)) {
        echo "Table created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Check if any data exists
$check_data = "SELECT COUNT(*) as count FROM company_details";
$result = $conn->query($check_data);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert default data if no data exists
    $insert_data = "INSERT INTO company_details (company_name, email, phone, address, logo) 
    VALUES (
        'Logistics Company',
        'info@logistics.com',
        '+1234567890',
        '123 Business Street, City, Country',
        'assets/images/logo.png'
    )";
    
    if ($conn->query($insert_data)) {
        echo "Default data inserted successfully<br>";
    } else {
        echo "Error inserting data: " . $conn->error . "<br>";
    }
} else {
    echo "Data already exists in the table<br>";
}

// Check for missing columns and add them if needed
$columns_to_check = [
    'company_name' => 'VARCHAR(255) NOT NULL',
    'email' => 'VARCHAR(255) NOT NULL',
    'phone' => 'VARCHAR(50) NOT NULL',
    'address' => 'TEXT',
    'logo' => 'VARCHAR(255)',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
];

foreach ($columns_to_check as $column => $definition) {
    $check_column = "SHOW COLUMNS FROM company_details LIKE '$column'";
    $result = $conn->query($check_column);
    
    if ($result->num_rows == 0) {
        $add_column = "ALTER TABLE company_details ADD COLUMN $column $definition";
        if ($conn->query($add_column)) {
            echo "Column $column added successfully<br>";
        } else {
            echo "Error adding column $column: " . $conn->error . "<br>";
        }
    }
}

echo "Company details table check and update completed!<br>";
echo "You can now access the tickets page.<br>";

$conn->close();
?> 
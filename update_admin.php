<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'include/db.php';

// Admin credentials
$email = 'admin@cargorover.com';
$password = 'admin123'; // Plain text password

// Check if user exists
$check_sql = "SELECT id FROM users WHERE email = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing user
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $password, $email);
    
    if ($stmt->execute()) {
        echo "Admin password updated successfully!<br>";
        echo "Password set to: " . $password;
    } else {
        echo "Error updating password: " . $stmt->error;
    }
} else {
    // Create new admin user
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'superadmin')";
    $stmt = $conn->prepare($sql);
    $name = "Super Admin";
    $stmt->bind_param("sss", $name, $email, $password);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully!<br>";
        echo "Password set to: " . $password;
    } else {
        echo "Error creating user: " . $stmt->error;
    }
}
?> 
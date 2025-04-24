<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'include/db.php';

// Check admin user
$email = 'admin@cargorover.com';
$sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

echo "<h1>User Check</h1>";

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<pre>";
    echo "User found:\n";
    print_r($user);
    echo "\nPassword hash: " . $user['password'];
    echo "</pre>";
} else {
    echo "User not found!";
}

// Check if we need to update the password
$test_password = 'admin123';
$hashed_password = password_hash($test_password, PASSWORD_DEFAULT);

echo "<h2>Password Test</h2>";
echo "Test password: " . $test_password . "<br>";
echo "Hashed password: " . $hashed_password . "<br>";

if (isset($user['password'])) {
    echo "Password verification: " . (password_verify($test_password, $user['password']) ? "Success" : "Failed");
}
?> 
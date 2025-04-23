<?php
session_start();
require_once '../../include/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create-agent.php');
    exit();
}

// Get and sanitize input
$name = sanitize_input($_POST['name'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$phone = sanitize_input($_POST['phone'] ?? '');
$address = sanitize_input($_POST['address'] ?? '');

// Validate input
if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($address)) {
    $_SESSION['error'] = 'Please fill in all required fields.';
    header('Location: create-agent.php');
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    header('Location: create-agent.php');
    exit();
}

// Validate password length
if (strlen($password) < 8) {
    $_SESSION['error'] = 'Password must be at least 8 characters long.';
    header('Location: create-agent.php');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    
    if (!$check_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $check_stmt->bind_param("s", $email);
    
    if (!$check_stmt->execute()) {
        throw new Exception("Execute failed: " . $check_stmt->error);
    }
    
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'An account with this email already exists.';
        header('Location: create-agent.php');
        exit();
    }
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new agent into users table
    $sql = "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'agent', 'active')";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("sss", $name, $email, $hashed_password);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $agent_id = $conn->insert_id;
    
    if (!$agent_id) {
        throw new Exception("Failed to get insert ID");
    }
    
    // Insert profile information
    $profile_sql = "INSERT INTO user_profiles (user_id, phone, address) VALUES (?, ?, ?)";
    $profile_stmt = $conn->prepare($profile_sql);
    
    if (!$profile_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $profile_stmt->bind_param("iss", $agent_id, $phone, $address);
    
    if (!$profile_stmt->execute()) {
        throw new Exception("Execute failed: " . $profile_stmt->error);
    }
    
    // Log the activity
    $log_sql = "INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'create_agent', ?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    
    if (!$log_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $log_details = "Created new agent: $name ($email)";
    $log_stmt->bind_param("iss", $_SESSION['user_id'], $log_details, $_SERVER['REMOTE_ADDR']);
    
    if (!$log_stmt->execute()) {
        throw new Exception("Execute failed: " . $log_stmt->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['success'] = 'Agent created successfully.';
    header('Location: agents.php');
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Error creating agent: " . $e->getMessage());
    $_SESSION['error'] = 'Failed to create agent: ' . $e->getMessage();
    header('Location: create-agent.php');
}
exit(); 
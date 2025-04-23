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
    header('Location: agents.php');
    exit();
}

// Get and sanitize input
$agent_id = (int)$_POST['agent_id'];
$name = sanitize_input($_POST['name'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$phone = sanitize_input($_POST['phone'] ?? '');
$address = sanitize_input($_POST['address'] ?? '');
$status = sanitize_input($_POST['status'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate input
if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($status)) {
    $_SESSION['error'] = 'Please fill in all required fields.';
    header('Location: edit-agent.php?id=' . $agent_id);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    header('Location: edit-agent.php?id=' . $agent_id);
    exit();
}

// Validate password if provided
if (!empty($password)) {
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long.';
        header('Location: edit-agent.php?id=' . $agent_id);
        exit();
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: edit-agent.php?id=' . $agent_id);
        exit();
    }
}

try {
    // Check if email already exists for other users
    $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    
    if (!$check_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $check_stmt->bind_param("si", $email, $agent_id);
    
    if (!$check_stmt->execute()) {
        throw new Exception("Execute failed: " . $check_stmt->error);
    }
    
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'An account with this email already exists.';
        header('Location: edit-agent.php?id=' . $agent_id);
        exit();
    }
    
    // Update agent
    if (!empty($password)) {
        // Update with new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET name = ?, email = ?, password = ?, phone = ?, address = ?, status = ? WHERE id = ? AND role = 'agent'";
        $update_stmt = $conn->prepare($update_sql);
        
        if (!$update_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $update_stmt->bind_param("ssssssi", $name, $email, $hashed_password, $phone, $address, $status, $agent_id);
    } else {
        // Update without changing password
        $update_sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, status = ? WHERE id = ? AND role = 'agent'";
        $update_stmt = $conn->prepare($update_sql);
        
        if (!$update_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $update_stmt->bind_param("sssssi", $name, $email, $phone, $address, $status, $agent_id);
    }
    
    if (!$update_stmt->execute()) {
        throw new Exception("Execute failed: " . $update_stmt->error);
    }
    
    if ($update_stmt->affected_rows === 0) {
        throw new Exception("No changes were made to the agent.");
    }
    
    // Log the activity
    $log_sql = "INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'update_agent', ?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    
    if (!$log_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $log_details = "Updated agent: $name ($email)";
    $log_stmt->bind_param("iss", $_SESSION['user_id'], $log_details, $_SERVER['REMOTE_ADDR']);
    
    if (!$log_stmt->execute()) {
        throw new Exception("Execute failed: " . $log_stmt->error);
    }
    
    $_SESSION['success'] = 'Agent updated successfully.';
    header('Location: agents.php');
    
} catch (Exception $e) {
    error_log("Error updating agent: " . $e->getMessage());
    $_SESSION['error'] = 'Failed to update agent. Please try again.';
    header('Location: edit-agent.php?id=' . $agent_id);
}
exit(); 
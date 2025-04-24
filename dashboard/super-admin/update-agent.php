<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: ../../login.php");
    exit();
}

$is_superadmin = ($_SESSION['user_role'] === 'superadmin');
$is_agent = ($_SESSION['user_role'] === 'agent');

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
$city = sanitize_input($_POST['city'] ?? '');
$state = sanitize_input($_POST['state'] ?? '');
$country = sanitize_input($_POST['country'] ?? '');
$postal_code = sanitize_input($_POST['postal_code'] ?? '');
$bio = sanitize_input($_POST['bio'] ?? '');
$timezone = sanitize_input($_POST['timezone'] ?? 'UTC');
$status = sanitize_input($_POST['status'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Debug log
error_log("Updating agent ID: " . $agent_id);
error_log("Input values: " . print_r($_POST, true));

// Validate input
if (empty($name) || empty($email) || empty($status)) {
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

// Validate phone format if provided
if (!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone)) {
    $_SESSION['error'] = 'Please enter a valid phone number (10-15 digits).';
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
    // Start transaction
    $conn->begin_transaction();

    // First, verify the agent exists
    $verify_sql = "SELECT id FROM users WHERE id = ? AND role = 'agent'";
    $verify_stmt = $conn->prepare($verify_sql);
    
    if (!$verify_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $verify_stmt->bind_param("i", $agent_id);
    
    if (!$verify_stmt->execute()) {
        throw new Exception("Execute failed: " . $verify_stmt->error);
    }
    
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        throw new Exception("Agent not found or invalid role.");
    }

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
        throw new Exception("An account with this email already exists.");
    }

    // Update user table
    if (!empty($password)) {
        // Update with new password
        $update_sql = "UPDATE users SET name = ?, email = ?, password = ?, status = ? WHERE id = ? AND role = 'agent'";
        $update_stmt = $conn->prepare($update_sql);
        
        if (!$update_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $update_stmt->bind_param("ssssi", $name, $email, $password, $status, $agent_id);
    } else {
        // Update without changing password
        $update_sql = "UPDATE users SET name = ?, email = ?, status = ? WHERE id = ? AND role = 'agent'";
        $update_stmt = $conn->prepare($update_sql);
        
        if (!$update_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $update_stmt->bind_param("sssi", $name, $email, $status, $agent_id);
    }
    
    if (!$update_stmt->execute()) {
        throw new Exception("Execute failed: " . $update_stmt->error);
    }

    // Check if profile exists
    $check_profile_sql = "SELECT user_id FROM user_profiles WHERE user_id = ?";
    $check_profile_stmt = $conn->prepare($check_profile_sql);
    $check_profile_stmt->bind_param("i", $agent_id);
    $check_profile_stmt->execute();
    $profile_result = $check_profile_stmt->get_result();
    
    if ($profile_result->num_rows > 0) {
        // Update existing profile
        $profile_sql = "UPDATE user_profiles SET 
                       phone = ?, 
                       address = ?, 
                       city = ?, 
                       state = ?, 
                       country = ?, 
                       postal_code = ?, 
                       bio = ?, 
                       timezone = ? 
                       WHERE user_id = ?";
        
        $profile_stmt = $conn->prepare($profile_sql);
        
        if (!$profile_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $profile_stmt->bind_param("ssssssssi", 
            $phone, 
            $address, 
            $city, 
            $state, 
            $country, 
            $postal_code, 
            $bio, 
            $timezone,
            $agent_id
        );
    } else {
        // Insert new profile
        $profile_sql = "INSERT INTO user_profiles 
                       (user_id, phone, address, city, state, country, postal_code, bio, timezone) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $profile_stmt = $conn->prepare($profile_sql);
        
        if (!$profile_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $profile_stmt->bind_param("issssssss", 
            $agent_id,
            $phone, 
            $address, 
            $city, 
            $state, 
            $country, 
            $postal_code, 
            $bio, 
            $timezone
        );
    }
    
    if (!$profile_stmt->execute()) {
        throw new Exception("Execute failed: " . $profile_stmt->error);
    }
    
    // Log the activity
    $log_sql = "INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'update_agent', ?)";
    $log_stmt = $conn->prepare($log_sql);
    
    if (!$log_stmt) {
        throw new Exception("Prepare failed: " . $log_stmt->error);
    }
    
    $log_details = "Updated agent: $name ($email)";
    $log_stmt->bind_param("is", $_SESSION['user_id'], $log_details);
    
    if (!$log_stmt->execute()) {
        throw new Exception("Execute failed: " . $log_stmt->error);
    }

    // Commit transaction
    $conn->commit();
    
    $_SESSION['success'] = 'Agent updated successfully.';
    header('Location: agents.php');
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log the error
    error_log("Error updating agent: " . $e->getMessage());
    
    // Set error message with details
    $_SESSION['error'] = 'Failed to update agent: ' . $e->getMessage();
    header('Location: edit-agent.php?id=' . $agent_id);
    exit();
}
?> 
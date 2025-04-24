<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: consignments.php');
    exit();
}

try {
    // Get and sanitize input
    $consignment_id = (int)$_POST['consignment_id'];
    $agent_id = (int)$_POST['agent_id'];

    // Verify consignment exists
    $check_sql = "SELECT tracking_number FROM consignments WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Database error");
    }
    
    $check_stmt->bind_param("i", $consignment_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        throw new Exception("Consignment not found");
    }

    $consignment = $check_result->fetch_assoc();
    $tracking_number = $consignment['tracking_number'];

    // Verify agent exists and is active
    $agent_sql = "SELECT name FROM users WHERE id = ? AND role = 'agent' AND status = 'active'";
    $agent_stmt = $conn->prepare($agent_sql);
    if (!$agent_stmt) {
        throw new Exception("Database error");
    }
    
    $agent_stmt->bind_param("i", $agent_id);
    $agent_stmt->execute();
    $agent_result = $agent_stmt->get_result();

    if ($agent_result->num_rows === 0) {
        throw new Exception("Invalid agent selected");
    }

    $agent = $agent_result->fetch_assoc();
    $agent_name = $agent['name'];

    // Update consignment agent
    $update_sql = "UPDATE consignments SET agent_id = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    if (!$update_stmt) {
        throw new Exception("Database error");
    }
    
    $update_stmt->bind_param("ii", $agent_id, $consignment_id);
    if (!$update_stmt->execute()) {
        throw new Exception("Error updating consignment agent");
    }

    // Log the activity
    $activity = "Assigned consignment " . $tracking_number . " to agent " . $agent_name;
    $log_sql = "INSERT INTO activity_logs (user_id, action) VALUES (?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    if (!$log_stmt) {
        throw new Exception("Database error");
    }
    
    $log_stmt->bind_param("is", $_SESSION['user_id'], $activity);
    $log_stmt->execute();

    // Set success message and redirect
    $_SESSION['success'] = "Agent assigned successfully";
    header('Location: consignments.php');
    exit();

} catch (Exception $e) {
    // Set error message and redirect back
    $_SESSION['error'] = $e->getMessage();
    header('Location: consignments.php');
    exit();
}
?> 
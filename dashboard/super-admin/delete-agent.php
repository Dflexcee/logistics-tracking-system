<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: ../../login.php");
    exit();
}

$is_superadmin = ($_SESSION['user_role'] === 'superadmin');
$is_agent = ($_SESSION['user_role'] === 'agent');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Check if agent ID is provided
if (!isset($_POST['agent_id'])) {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: agents.php');
    exit();
}

$agent_id = (int)$_POST['agent_id'];

try {
    // Get agent details for logging
    $get_sql = "SELECT name, email FROM users WHERE id = ? AND role = 'agent'";
    $stmt = $conn->prepare($get_sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $agent_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Agent not found.';
        header('Location: agents.php');
        exit();
    }
    
    $agent = $result->fetch_assoc();
    
    // Delete agent
    $delete_sql = "DELETE FROM users WHERE id = ? AND role = 'agent'";
    $delete_stmt = $conn->prepare($delete_sql);
    
    if (!$delete_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $delete_stmt->bind_param("i", $agent_id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception("Execute failed: " . $delete_stmt->error);
    }
    
    if ($delete_stmt->affected_rows === 0) {
        throw new Exception("No agent was deleted.");
    }
    
    // Log the activity
    $log_sql = "INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'delete_agent', ?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    
    if (!$log_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $log_details = "Deleted agent: {$agent['name']} ({$agent['email']})";
    $log_stmt->bind_param("iss", $_SESSION['user_id'], $log_details, $_SERVER['REMOTE_ADDR']);
    
    if (!$log_stmt->execute()) {
        throw new Exception("Execute failed: " . $log_stmt->error);
    }
    
    $_SESSION['success'] = 'Agent deleted successfully.';
    
} catch (Exception $e) {
    error_log("Error deleting agent: " . $e->getMessage());
    $_SESSION['error'] = 'Failed to delete agent. Please try again.';
}

header('Location: agents.php');
exit(); 
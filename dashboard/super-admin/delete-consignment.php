<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: ../../login.php");
    exit();
}

$is_superadmin = ($_SESSION['user_role'] === 'superadmin');
$is_agent = ($_SESSION['user_role'] === 'agent');

require_once '../../include/db.php';

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid consignment ID";
    header('Location: consignments.php');
    exit();
}

$consignment_id = (int)$_GET['id'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Get consignment details for logging
    $sql = "SELECT tracking_number FROM consignments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error");
    }
    
    $stmt->bind_param("i", $consignment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Consignment not found");
    }

    $consignment = $result->fetch_assoc();
    $tracking_number = $consignment['tracking_number'];

    // Delete tracking history first (due to foreign key constraint)
    $sql = "DELETE FROM tracking_history WHERE consignment_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error");
    }
    
    $stmt->bind_param("i", $consignment_id);
    if (!$stmt->execute()) {
        throw new Exception("Error deleting tracking history");
    }

    // Delete the consignment
    $sql = "DELETE FROM consignments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error");
    }
    
    $stmt->bind_param("i", $consignment_id);
    if (!$stmt->execute()) {
        throw new Exception("Error deleting consignment");
    }

    // Log the activity
    $activity = "Deleted consignment with tracking number " . $tracking_number;
    $sql = "INSERT INTO activity_logs (user_id, action) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error");
    }
    
    $stmt->bind_param("is", $_SESSION['user_id'], $activity);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Set success message and redirect
    $_SESSION['success'] = "Consignment deleted successfully";
    header('Location: consignments.php');
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Set error message and redirect back
    $_SESSION['error'] = $e->getMessage();
    header('Location: consignments.php');
    exit();
}
?> 
<?php
session_start();
require_once 'include/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get and sanitize input
    $tracking_number = sanitize_input($_POST['tracking_number']);
    $status = sanitize_input($_POST['status']);
    $comment = sanitize_input($_POST['comment']);
    $user_id = $_SESSION['user_id'];

    // Validate required fields
    if (empty($tracking_number) || empty($status) || empty($comment)) {
        throw new Exception("All fields are required");
    }

    // Get consignment_id from tracking number
    $sql = "SELECT id FROM consignments WHERE tracking_number = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error");
    }
    
    $stmt->bind_param("s", $tracking_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Consignment not found");
    }
    
    $consignment = $result->fetch_assoc();
    $consignment_id = $consignment['id'];

    // Insert tracking update
    $sql = "INSERT INTO tracking_updates (consignment_id, status, comment, updated_by, timestamp) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error");
    }
    
    $stmt->bind_param("issi", $consignment_id, $status, $comment, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error saving tracking update");
    }

    // Update consignment status
    $sql = "UPDATE consignments SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error");
    }
    
    $stmt->bind_param("si", $status, $consignment_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating consignment status");
    }

    // Log the activity
    $activity = "Updated tracking status for consignment " . $tracking_number;
    $sql = "INSERT INTO activity_logs (user_id, action) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error");
    }
    
    $stmt->bind_param("is", $user_id, $activity);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Set success message and redirect
    $_SESSION['success'] = "Tracking status updated successfully";
    header('Location: tracking.php?tracking_number=' . urlencode($tracking_number));
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Set error message and redirect back
    $_SESSION['error'] = $e->getMessage();
    header('Location: tracking.php?tracking_number=' . urlencode($tracking_number));
    exit();
}
?> 
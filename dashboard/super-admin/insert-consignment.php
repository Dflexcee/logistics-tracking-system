<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize all inputs
        $sender_name = sanitize_input($_POST['sender_name']);
        $sender_phone = sanitize_input($_POST['sender_phone']);
        $receiver_name = sanitize_input($_POST['receiver_name']);
        $receiver_phone = sanitize_input($_POST['receiver_phone']);
        $pickup_location = sanitize_input($_POST['pickup_location']);
        $drop_location = sanitize_input($_POST['drop_location']);
        $agent_id = (int)$_POST['agent_id'];
        $status = sanitize_input($_POST['status']);
        
        // New fields
        $item_description = sanitize_input($_POST['item_description']);
        $weight_kg = (float)$_POST['weight_kg'];
        $amount_paid = (float)$_POST['amount_paid'];
        $dispatch_method = sanitize_input($_POST['dispatch_method']);
        $sent_from_country = sanitize_input($_POST['sent_from_country']);
        $sent_from_state = sanitize_input($_POST['sent_from_state']);
        $sent_from_terminal = sanitize_input($_POST['sent_from_terminal']);

        // Validate required fields
        if (empty($sender_name) || empty($sender_phone) || empty($receiver_name) || 
            empty($receiver_phone) || empty($pickup_location) || empty($drop_location) || 
            empty($agent_id) || empty($status) || empty($item_description) || 
            empty($weight_kg) || empty($amount_paid) || empty($dispatch_method) || 
            empty($sent_from_country) || empty($sent_from_state) || empty($sent_from_terminal)) {
            throw new Exception("All fields are required");
        }

        // Validate numeric fields
        if ($weight_kg <= 0) {
            throw new Exception("Weight must be greater than 0");
        }
        if ($amount_paid < 0) {
            throw new Exception("Amount paid cannot be negative");
        }

        // Validate dispatch method
        $valid_dispatch_methods = ['Air', 'Land', 'Sea', 'Rider', 'Train', 'Taxi', 'Bus'];
        if (!in_array($dispatch_method, $valid_dispatch_methods)) {
            throw new Exception("Invalid dispatch method");
        }

        // Check if agent exists and is active
        $agent_check_sql = "SELECT id FROM users WHERE id = ? AND role = 'agent' AND status = 'active'";
        $agent_check_stmt = $conn->prepare($agent_check_sql);
        $agent_check_stmt->bind_param("i", $agent_id);
        $agent_check_stmt->execute();
        $agent_result = $agent_check_stmt->get_result();
        
        if ($agent_result->num_rows === 0) {
            throw new Exception("Invalid or inactive agent selected");
        }

        // Generate unique tracking number
        $tracking_number = 'FXC' . strtoupper(substr(md5(uniqid()), 0, 6));

        // Start transaction
        $conn->begin_transaction();

        // Insert consignment
        $sql = "INSERT INTO consignments (
                    tracking_number, sender_name, sender_phone, receiver_name, 
                    receiver_phone, pickup_location, drop_location, agent_id, 
                    status, item_description, weight_kg, amount_paid, 
                    dispatch_method, sent_from_country, sent_from_state, 
                    sent_from_terminal, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssisddsssss", 
            $tracking_number, $sender_name, $sender_phone, $receiver_name,
            $receiver_phone, $pickup_location, $drop_location, $agent_id,
            $status, $item_description, $weight_kg, $amount_paid,
            $dispatch_method, $sent_from_country, $sent_from_state,
            $sent_from_terminal
        );

        if (!$stmt->execute()) {
            throw new Exception("Error creating consignment: " . $stmt->error);
        }

        $consignment_id = $conn->insert_id;

        // Add initial tracking update
        $update_sql = "INSERT INTO tracking_updates (consignment_id, status, comment, timestamp) VALUES (?, ?, ?, NOW())";
        $update_stmt = $conn->prepare($update_sql);
        $comment = "Consignment created and assigned to agent";
        $update_stmt->bind_param("iss", $consignment_id, $status, $comment);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Error creating tracking update: " . $update_stmt->error);
        }

        // Log activity
        $activity_sql = "INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'create_consignment', ?)";
        $activity_stmt = $conn->prepare($activity_sql);
        $details = "Created consignment with tracking number: " . $tracking_number;
        $activity_stmt->bind_param("is", $_SESSION['user_id'], $details);
        
        if (!$activity_stmt->execute()) {
            throw new Exception("Error logging activity: " . $activity_stmt->error);
        }

        // Commit transaction
        $conn->commit();

        // Set success message and redirect
        $_SESSION['success'] = "Consignment created successfully with tracking number: " . $tracking_number;
        header('Location: consignments.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Log error
        error_log("Error in insert-consignment.php: " . $e->getMessage());
        
        // Set error message and redirect
        $_SESSION['error'] = $e->getMessage();
        header('Location: create-consignment.php');
        exit();
    }
} else {
    // If not POST request, redirect to create page
    header('Location: create-consignment.php');
    exit();
}
?> 
<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is agent
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header('Location: ../../login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create-consignment.php');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Verify agent exists and is active
    $agent_id = $_SESSION['user_id'];
    $check_agent_sql = "SELECT id FROM users WHERE id = ? AND role = 'agent' AND status = 'active'";
    $check_agent_stmt = $conn->prepare($check_agent_sql);
    $check_agent_stmt->bind_param("i", $agent_id);
    $check_agent_stmt->execute();
    $agent_result = $check_agent_stmt->get_result();

    if ($agent_result->num_rows === 0) {
        throw new Exception("Invalid or inactive agent account");
    }

    // Insert sender information
    $sender_sql = "INSERT INTO senders (name, phone, address) VALUES (?, ?, ?)";
    $sender_stmt = $conn->prepare($sender_sql);
    if ($sender_stmt === false) {
        throw new Exception("Error preparing sender query: " . $conn->error);
    }
    $sender_stmt->bind_param("sss", 
        $_POST['sender_name'],
        $_POST['sender_phone'],
        $_POST['pickup_location']
    );
    if (!$sender_stmt->execute()) {
        throw new Exception("Error inserting sender: " . $sender_stmt->error);
    }
    $sender_id = $conn->insert_id;

    // Insert receiver information
    $receiver_sql = "INSERT INTO receivers (name, phone, address) VALUES (?, ?, ?)";
    $receiver_stmt = $conn->prepare($receiver_sql);
    if ($receiver_stmt === false) {
        throw new Exception("Error preparing receiver query: " . $conn->error);
    }
    $receiver_stmt->bind_param("sss", 
        $_POST['receiver_name'],
        $_POST['receiver_phone'],
        $_POST['drop_location']
    );
    if (!$receiver_stmt->execute()) {
        throw new Exception("Error inserting receiver: " . $receiver_stmt->error);
    }
    $receiver_id = $conn->insert_id;

    // Generate tracking number
    $tracking_number = 'TRK' . date('Ymd') . str_pad($agent_id, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);

    // Insert consignment information
    $consignment_sql = "INSERT INTO consignments (
        tracking_number,
        sender_id,
        receiver_id,
        agent_id,
        weight,
        dimensions,
        description,
        package_type,
        special_instructions,
        status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    
    $consignment_stmt = $conn->prepare($consignment_sql);
    if ($consignment_stmt === false) {
        throw new Exception("Error preparing consignment query: " . $conn->error);
    }
    $consignment_stmt->bind_param("siiidsssss", 
        $tracking_number,
        $sender_id,
        $receiver_id,
        $agent_id,
        $_POST['weight_kg'],
        $_POST['dimensions'],
        $_POST['description'],
        $_POST['package_type'],
        $_POST['special_instructions']
    );
    if (!$consignment_stmt->execute()) {
        throw new Exception("Error inserting consignment: " . $consignment_stmt->error);
    }
    $consignment_id = $conn->insert_id;

    // Add initial tracking history
    $history_sql = "INSERT INTO tracking_history (consignment_id, status, location, notes) 
        VALUES (?, 'pending', ?, 'Consignment created')";
    $history_stmt = $conn->prepare($history_sql);
    if ($history_stmt === false) {
        throw new Exception("Error preparing history query: " . $conn->error);
    }
    $history_stmt->bind_param("is", 
        $consignment_id,
        $_POST['pickup_location']
    );
    if (!$history_stmt->execute()) {
        throw new Exception("Error inserting tracking history: " . $history_stmt->error);
    }

    // Commit transaction
    $conn->commit();

    // Set success message and redirect
    $_SESSION['success'] = "Consignment created successfully with tracking number: " . $tracking_number;
    header('Location: view-consignment.php?id=' . $consignment_id);
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log the error for debugging
    error_log("Error in insert-agent-consignment.php: " . $e->getMessage());
    
    // Set error message and redirect back
    $_SESSION['error'] = $e->getMessage();
    header('Location: create-consignment.php');
    exit();
}
?> 
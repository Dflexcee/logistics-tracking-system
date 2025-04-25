<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is an agent
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header('Location: ../../login.php');
    exit();
}

$agent_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Get consignment ID
        $consignment_id = isset($_POST['consignment_id']) ? (int)$_POST['consignment_id'] : 0;

        if ($consignment_id === 0) {
            throw new Exception("Invalid consignment ID");
        }

        // Verify consignment belongs to agent
        $check_sql = "SELECT id FROM consignments WHERE id = ? AND agent_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $consignment_id, $agent_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            throw new Exception("Consignment not found or access denied");
        }

        // Update consignment status
        $update_sql = "UPDATE consignments SET 
            status = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND agent_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sii", 
            $_POST['status'],
            $consignment_id,
            $agent_id
        );
        $update_stmt->execute();

        // Add tracking history
        $history_sql = "INSERT INTO tracking_history (consignment_id, status, location, notes, created_at) 
            VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $history_stmt = $conn->prepare($history_sql);
        $history_stmt->bind_param("isss", 
            $consignment_id,
            $_POST['status'],
            $_POST['location'],
            $_POST['notes']
        );
        $history_stmt->execute();

        // Commit transaction
        $conn->commit();

        // Return success response
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        header("Location: track-consignment.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Return error response
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}

// If not POST request, return error
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
exit(); 
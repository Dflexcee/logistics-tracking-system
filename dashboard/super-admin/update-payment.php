<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is either superadmin or agent
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['superadmin', 'agent'])) {
    header('Location: ../../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $consignment_id = sanitize_input($_POST['consignment_id']);
    $tracking_number = sanitize_input($_POST['tracking_number']);
    $payment_status = sanitize_input($_POST['payment_status']);
    $payment_method = sanitize_input($_POST['payment_method']);
    $amount_paid = floatval($_POST['amount_paid']);
    $paid_by = sanitize_input($_POST['paid_by']);

    try {
        // For agents, check if they are assigned to this consignment
        if ($_SESSION['user_role'] === 'agent') {
            $check_sql = "SELECT agent_id FROM consignments WHERE id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $consignment_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $consignment = $result->fetch_assoc();

            if (!$consignment || $consignment['agent_id'] != $_SESSION['user_id']) {
                throw new Exception("You are not authorized to update this consignment");
            }
        }

        // Update payment information
        $sql = "UPDATE consignments SET 
                payment_status = ?,
                payment_method = ?,
                amount_paid = ?,
                paid_by = ?,
                updated_at = NOW()
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsi", $payment_status, $payment_method, $amount_paid, $paid_by, $consignment_id);
        
        if ($stmt->execute()) {
            // Add payment update to tracking history
            $status = $payment_status === 'paid' ? 'Payment Completed' : 'Payment Pending';
            $notes = "Payment Status: " . ucfirst($payment_status) . 
                    ($payment_method ? ", Method: " . ucfirst($payment_method) : "") . 
                    ($amount_paid > 0 ? ", Amount: ₦" . number_format($amount_paid, 2) : "") . 
                    ($paid_by ? ", Paid by: " . ucfirst($paid_by) : "");

            $history_sql = "INSERT INTO tracking_history (consignment_id, status, notes, created_at) 
                          VALUES (?, ?, ?, NOW())";
            $history_stmt = $conn->prepare($history_sql);
            $history_stmt->bind_param("iss", $consignment_id, $status, $notes);
            $history_stmt->execute();

            $_SESSION['success'] = "Payment information updated successfully";
        } else {
            throw new Exception("Error updating payment information");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    // Redirect back to tracking history
    header("Location: tracking-history.php?tracking_number=" . urlencode($tracking_number));
    exit();
}
?> 
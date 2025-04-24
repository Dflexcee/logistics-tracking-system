<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

$success = [];
$errors = [];

try {
    // Add payment_status column
    $sql1 = "ALTER TABLE consignments ADD COLUMN payment_status ENUM('pending', 'paid') DEFAULT 'pending'";
    if ($conn->query($sql1)) {
        $success[] = "Added payment_status column";
    } else {
        $errors[] = "Error adding payment_status: " . $conn->error;
    }

    // Add payment_method column
    $sql2 = "ALTER TABLE consignments ADD COLUMN payment_method ENUM('cash', 'bank_transfer', 'card') DEFAULT NULL";
    if ($conn->query($sql2)) {
        $success[] = "Added payment_method column";
    } else {
        $errors[] = "Error adding payment_method: " . $conn->error;
    }

    // Add amount_paid column
    $sql3 = "ALTER TABLE consignments ADD COLUMN amount_paid DECIMAL(10,2) DEFAULT 0.00";
    if ($conn->query($sql3)) {
        $success[] = "Added amount_paid column";
    } else {
        $errors[] = "Error adding amount_paid: " . $conn->error;
    }

    // Add paid_by column
    $sql4 = "ALTER TABLE consignments ADD COLUMN paid_by ENUM('sender', 'receiver') DEFAULT NULL";
    if ($conn->query($sql4)) {
        $success[] = "Added paid_by column";
    } else {
        $errors[] = "Error adding paid_by: " . $conn->error;
    }

    // Add updated_at column
    $sql5 = "ALTER TABLE consignments ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    if ($conn->query($sql5)) {
        $success[] = "Added updated_at column";
    } else {
        $errors[] = "Error adding updated_at: " . $conn->error;
    }

    // Store results in session
    if (!empty($success)) {
        $_SESSION['success'] = implode("<br>", $success);
    }
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

// Redirect back to tracking history
header("Location: tracking-history.php");
exit();
?> 
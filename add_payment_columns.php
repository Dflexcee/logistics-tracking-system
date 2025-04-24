<?php
require_once 'include/db.php';

try {
    // Add payment_status column
    $sql1 = "ALTER TABLE consignments ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'paid') DEFAULT 'pending'";
    if ($conn->query($sql1)) {
        echo "Added payment_status column\n";
    }

    // Add payment_method column
    $sql2 = "ALTER TABLE consignments ADD COLUMN IF NOT EXISTS payment_method ENUM('cash', 'bank_transfer', 'card') DEFAULT NULL";
    if ($conn->query($sql2)) {
        echo "Added payment_method column\n";
    }

    // Add amount_paid column
    $sql3 = "ALTER TABLE consignments ADD COLUMN IF NOT EXISTS amount_paid DECIMAL(10,2) DEFAULT 0.00";
    if ($conn->query($sql3)) {
        echo "Added amount_paid column\n";
    }

    // Add paid_by column
    $sql4 = "ALTER TABLE consignments ADD COLUMN IF NOT EXISTS paid_by ENUM('sender', 'receiver') DEFAULT NULL";
    if ($conn->query($sql4)) {
        echo "Added paid_by column\n";
    }

    // Add updated_at column
    $sql5 = "ALTER TABLE consignments ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    if ($conn->query($sql5)) {
        echo "Added updated_at column\n";
    }

    echo "All columns added successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
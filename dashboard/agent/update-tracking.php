<?php

// Insert tracking history
$tracking_sql = "INSERT INTO tracking_history (consignment_id, status, location, notes) VALUES (?, ?, ?, ?)";
$tracking_stmt = $conn->prepare($tracking_sql);
$tracking_stmt->bind_param("isss", $consignment_id, $status, $location, $notes);
$tracking_stmt->execute();

// Update consignment status
$update_sql = "UPDATE consignments SET status = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("si", $status, $consignment_id);
$update_stmt->execute(); 
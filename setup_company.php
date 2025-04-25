<?php
require_once 'include/db.php';

// Read the SQL file
$sql = file_get_contents('setup_company_details.sql');

// Execute the SQL commands
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    
    echo "Company details table created and default data inserted successfully!";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?> 
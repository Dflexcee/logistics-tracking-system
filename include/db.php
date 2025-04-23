<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dbroot');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // Log the error (in a production environment, you would log to a file)
    error_log("Connection failed: " . $conn->connect_error);
    
    // Show a user-friendly error message
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Function to safely escape user input
function sanitize_input($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

// Function to execute queries safely
function execute_query($sql, $params = []) {
    global $conn;
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Query preparation failed: " . $conn->error);
        return false;
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // Default to string type
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Query execution failed: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result;
}
?> 
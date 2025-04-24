<?php
session_start();

// Check if user is logged in and has a role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: ../../login.php");
    exit();
}

// Optional: Check if user is active
if (isset($_SESSION['user_status']) && $_SESSION['user_status'] !== 'active') {
    session_destroy();
    header("Location: ../../login.php?error=inactive");
    exit();
}

// Optional: Check session timeout (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_destroy();
    header("Location: ../../login.php?error=timeout");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();
?> 
<?php
// Start session and enable error reporting
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all request information
error_log("=== New Login Attempt ===");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("POST Data: " . print_r($_POST, true));
error_log("GET Data: " . print_r($_GET, true));
error_log("Headers: " . print_r(getallheaders(), true));

// Check if this is a direct access
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'login.php') === false) {
    error_log("Direct access detected - Redirecting to login");
    $_SESSION['error'] = 'Please access the login page through the proper form.';
    header('Location: login.php');
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method detected: " . $_SERVER['REQUEST_METHOD']);
    $_SESSION['error'] = 'Invalid request method. Please login through the form.';
    header('Location: login.php');
    exit();
}

// Include database connection
require_once 'include/db.php';

// Verify database connection
if (!$conn) {
    error_log("Database connection failed");
    $_SESSION['error'] = 'Database connection error. Please try again later.';
    header('Location: login.php');
    exit();
}

// Get and sanitize inputs
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

error_log("Processing login for email: " . $email);

if (empty($email) || empty($password)) {
    error_log("Empty email or password");
    $_SESSION['error'] = 'Email and password are required.';
    header('Location: login.php');
    exit();
}

// Prepare and execute query
$sql = "SELECT id, name, password, role FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Compare plain text passwords
    if ($password === $user['password']) {
        // Store session values
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        error_log("Login successful for user: " . $email);

        // Redirect based on role with correct paths
        switch ($user['role']) {
            case 'superadmin':
                header('Location: dashboard/super-admin/index.php');
                break;
            case 'agent':
                header('Location: dashboard/agent/index.php');
                break;
            case 'manager':
                header('Location: dashboard/manager/index.php');
                break;
            default:
                $_SESSION['error'] = 'Unrecognized user role.';
                header('Location: login.php');
        }
        exit();
    } else {
        error_log("Invalid password for user: " . $email);
        $_SESSION['error'] = 'Incorrect password.';
    }
} else {
    error_log("User not found: " . $email);
    $_SESSION['error'] = 'Account not found.';
}

header('Location: login.php');
exit();

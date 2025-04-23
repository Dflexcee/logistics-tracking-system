<?php
session_start();
require_once 'include/db.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// Get and sanitize input
$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validate input
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Please enter both email and password.';
    header('Location: login.php');
    exit();
}

// Query user
$sql = "SELECT id, name, email, password, role, status FROM users WHERE email = ?";
$result = execute_query($sql, [$email]);

if (!$result) {
    $_SESSION['error'] = 'Database error occurred.';
    header('Location: login.php');
    exit();
}

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Invalid email or password.';
    header('Location: login.php');
    exit();
}

$user = $result->fetch_assoc();

// Check if user is active
if ($user['status'] !== 'active') {
    $_SESSION['error'] = 'Your account is inactive. Please contact support.';
    header('Location: login.php');
    exit();
}

// Verify password (plain text comparison)
if ($password !== $user['password']) {
    $_SESSION['error'] = 'Invalid email or password.';
    header('Location: login.php');
    exit();
}

// Set session variables
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];

// Set remember me cookie if requested
if ($remember) {
    $token = bin2hex(random_bytes(32));
    $expires = time() + (30 * 24 * 60 * 60); // 30 days
    
    // Store token in database
    $sql = "UPDATE users SET remember_token = ?, token_expires = FROM_UNIXTIME(?) WHERE id = ?";
    execute_query($sql, [$token, $expires, $user['id']]);
    
    // Set cookie
    setcookie('remember_token', $token, $expires, '/', '', true, true);
}

// Log successful login
$sql = "INSERT INTO login_logs (user_id, ip_address, user_agent) VALUES (?, ?, ?)";
execute_query($sql, [
    $user['id'],
    $_SERVER['REMOTE_ADDR'],
    $_SERVER['HTTP_USER_AGENT']
]);

// Redirect based on role
switch ($user['role']) {
    case 'superadmin':
        header('Location: /dashboard/super-admin/index.php');
        break;
    case 'agent':
        header('Location: /dashboard/agent/index.php');
        break;
    case 'manager':
        header('Location: /dashboard/manager/index.php');
        break;
    default:
        $_SESSION['error'] = 'Invalid user role.';
        header('Location: login.php');
        break;
}
exit(); 
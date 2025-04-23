<?php
/**
 * Check if user is logged in
 * Redirects to login page if not logged in
 * 
 * @param string $redirect_path Optional custom redirect path
 * @return bool True if user is logged in
 */
function check_login($redirect_path = '../login.php') {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Store current URL for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login page
        header('Location: ' . $redirect_path);
        exit();
    }

    return true;
}

/**
 * Get current user's role
 * 
 * @return string|null User role or null if not logged in
 */
function get_user_role() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return $_SESSION['user_role'] ?? null;
}

/**
 * Check if user has required role
 * 
 * @param string|array $required_roles Single role or array of roles
 * @return bool True if user has required role
 */
function has_role($required_roles) {
    $user_role = get_user_role();
    
    if (is_array($required_roles)) {
        return in_array($user_role, $required_roles);
    }
    
    return $user_role === $required_roles;
}

/**
 * Get current user's ID
 * 
 * @return int|null User ID or null if not logged in
 */
function get_user_id() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user's name
 * 
 * @return string|null User name or null if not logged in
 */
function get_user_name() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return $_SESSION['user_name'] ?? null;
}

/**
 * Check if user has specific permission
 * 
 * @param string $permission Permission to check
 * @return bool True if user has permission
 */
function has_permission($permission) {
    global $conn;
    
    $user_id = get_user_id();
    if (!$user_id) return false;
    
    $sql = "SELECT r.permissions 
            FROM users u 
            JOIN roles r ON u.role = r.role_name 
            WHERE u.id = ?";
    $result = execute_query($sql, [$user_id]);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $permissions = json_decode($row['permissions'], true);
        
        return isset($permissions['all']) && $permissions['all'] === true || 
               isset($permissions[$permission]) && $permissions[$permission] === true;
    }
    
    return false;
}

/**
 * Get user's permissions
 * 
 * @return array User's permissions
 */
function get_user_permissions() {
    global $conn;
    
    $user_id = get_user_id();
    if (!$user_id) return [];
    
    $sql = "SELECT r.permissions 
            FROM users u 
            JOIN roles r ON u.role = r.role_name 
            WHERE u.id = ?";
    $result = execute_query($sql, [$user_id]);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return json_decode($row['permissions'], true);
    }
    
    return [];
}

/**
 * Check if user is active
 * 
 * @return bool True if user is active
 */
function is_user_active() {
    global $conn;
    
    $user_id = get_user_id();
    if (!$user_id) return false;
    
    $sql = "SELECT status FROM users WHERE id = ?";
    $result = execute_query($sql, [$user_id]);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['status'] === 'active';
    }
    
    return false;
}

/**
 * Get user's last login time
 * 
 * @return string|null Last login time or null
 */
function get_last_login() {
    global $conn;
    
    $user_id = get_user_id();
    if (!$user_id) return null;
    
    $sql = "SELECT created_at 
            FROM login_logs 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1";
    $result = execute_query($sql, [$user_id]);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['created_at'];
    }
    
    return null;
}

/**
 * Log user activity
 * 
 * @param string $action Action performed
 * @param string $details Additional details
 * @return bool True if logged successfully
 */
function log_activity($action, $details = '') {
    global $conn;
    
    $user_id = get_user_id();
    if (!$user_id) return false;
    
    $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address) 
            VALUES (?, ?, ?, ?)";
    return execute_query($sql, [
        $user_id,
        $action,
        $details,
        $_SERVER['REMOTE_ADDR']
    ]);
}

/**
 * Get user's activity log
 * 
 * @param int $limit Number of records to return
 * @return array Activity log records
 */
function get_activity_log($limit = 10) {
    global $conn;
    
    $user_id = get_user_id();
    if (!$user_id) return [];
    
    $sql = "SELECT * FROM activity_logs 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?";
    $result = execute_query($sql, [$user_id, $limit]);
    
    $logs = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    
    return $logs;
}

/**
 * Check if user's session is expired
 * 
 * @param int $max_lifetime Maximum session lifetime in seconds
 * @return bool True if session is expired
 */
function is_session_expired($max_lifetime = 3600) {
    if (!isset($_SESSION['last_activity'])) {
        return true;
    }
    
    return (time() - $_SESSION['last_activity']) > $max_lifetime;
}

/**
 * Update session activity timestamp
 */
function update_session_activity() {
    $_SESSION['last_activity'] = time();
}

/**
 * Regenerate session ID
 */
function regenerate_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    session_regenerate_id(true);
    update_session_activity();
}

/**
 * Create a new user
 * 
 * @param array $user_data User data (name, email, password, role, etc.)
 * @return int|false New user ID or false on failure
 */
function create_user($user_data) {
    global $conn;
    
    // Validate required fields
    $required_fields = ['name', 'email', 'password', 'role'];
    foreach ($required_fields as $field) {
        if (empty($user_data[$field])) {
            return false;
        }
    }
    
    // Hash password
    $user_data['password'] = password_hash($user_data['password'], PASSWORD_DEFAULT);
    
    // Prepare SQL
    $sql = "INSERT INTO users (name, email, password, role, status, agent_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    // Execute query
    $result = execute_query($sql, [
        $user_data['name'],
        $user_data['email'],
        $user_data['password'],
        $user_data['role'],
        $user_data['status'] ?? 'active',
        $user_data['agent_id'] ?? null
    ]);
    
    if ($result) {
        return $conn->insert_id;
    }
    
    return false;
}

/**
 * Update user details
 * 
 * @param int $user_id User ID
 * @param array $user_data User data to update
 * @return bool True if updated successfully
 */
function update_user($user_id, $user_data) {
    global $conn;
    
    // Build update fields
    $update_fields = [];
    $params = [];
    
    $allowed_fields = ['name', 'email', 'role', 'status', 'agent_id'];
    foreach ($allowed_fields as $field) {
        if (isset($user_data[$field])) {
            $update_fields[] = "$field = ?";
            $params[] = $user_data[$field];
        }
    }
    
    // Update password if provided
    if (!empty($user_data['password'])) {
        $update_fields[] = "password = ?";
        $params[] = password_hash($user_data['password'], PASSWORD_DEFAULT);
    }
    
    if (empty($update_fields)) {
        return false;
    }
    
    // Add user_id to params
    $params[] = $user_id;
    
    // Prepare and execute query
    $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
    return execute_query($sql, $params);
}

/**
 * Delete user
 * 
 * @param int $user_id User ID
 * @return bool True if deleted successfully
 */
function delete_user($user_id) {
    global $conn;
    
    // Don't allow deleting superadmin
    $user = get_user_by_id($user_id);
    if ($user && $user['role'] === 'superadmin') {
        return false;
    }
    
    $sql = "DELETE FROM users WHERE id = ?";
    return execute_query($sql, [$user_id]);
}

/**
 * Get user by ID
 * 
 * @param int $user_id User ID
 * @return array|null User data or null if not found
 */
function get_user_by_id($user_id) {
    global $conn;
    
    $sql = "SELECT id, name, email, role, status, agent_id, created_at 
            FROM users 
            WHERE id = ?";
    $result = execute_query($sql, [$user_id]);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get user by email
 * 
 * @param string $email User email
 * @return array|null User data or null if not found
 */
function get_user_by_email($email) {
    global $conn;
    
    $sql = "SELECT id, name, email, role, status, agent_id, created_at 
            FROM users 
            WHERE email = ?";
    $result = execute_query($sql, [$email]);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get all users
 * 
 * @param array $filters Optional filters (role, status, etc.)
 * @param int $limit Optional limit
 * @param int $offset Optional offset
 * @return array List of users
 */
function get_all_users($filters = [], $limit = 0, $offset = 0) {
    global $conn;
    
    $sql = "SELECT id, name, email, role, status, agent_id, created_at 
            FROM users 
            WHERE 1=1";
    $params = [];
    
    // Add filters
    if (!empty($filters['role'])) {
        $sql .= " AND role = ?";
        $params[] = $filters['role'];
    }
    
    if (!empty($filters['status'])) {
        $sql .= " AND status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (name LIKE ? OR email LIKE ?)";
        $search = "%{$filters['search']}%";
        $params[] = $search;
        $params[] = $search;
    }
    
    // Add order
    $sql .= " ORDER BY created_at DESC";
    
    // Add limit and offset
    if ($limit > 0) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
        
        if ($offset > 0) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
        }
    }
    
    $result = execute_query($sql, $params);
    
    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    return $users;
}

/**
 * Count total users
 * 
 * @param array $filters Optional filters
 * @return int Total number of users
 */
function count_users($filters = []) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
    $params = [];
    
    // Add filters
    if (!empty($filters['role'])) {
        $sql .= " AND role = ?";
        $params[] = $filters['role'];
    }
    
    if (!empty($filters['status'])) {
        $sql .= " AND status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (name LIKE ? OR email LIKE ?)";
        $search = "%{$filters['search']}%";
        $params[] = $search;
        $params[] = $search;
    }
    
    $result = execute_query($sql, $params);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }
    
    return 0;
}

/**
 * Change user password
 * 
 * @param int $user_id User ID
 * @param string $new_password New password
 * @return bool True if password changed successfully
 */
function change_password($user_id, $new_password) {
    global $conn;
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    return execute_query($sql, [$hashed_password, $user_id]);
}

/**
 * Verify user password
 * 
 * @param int $user_id User ID
 * @param string $password Password to verify
 * @return bool True if password is correct
 */
function verify_password($user_id, $password) {
    global $conn;
    
    $sql = "SELECT password FROM users WHERE id = ?";
    $result = execute_query($sql, [$user_id]);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return password_verify($password, $row['password']);
    }
    
    return false;
}

/**
 * Get user's agents (for managers)
 * 
 * @param int $manager_id Manager's user ID
 * @return array List of agents
 */
function get_user_agents($manager_id) {
    global $conn;
    
    $sql = "SELECT id, name, email, role, status, created_at 
            FROM users 
            WHERE agent_id = ?";
    $result = execute_query($sql, [$manager_id]);
    
    $agents = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $agents[] = $row;
        }
    }
    
    return $agents;
}

/**
 * Get user profile
 * 
 * @param int $user_id User ID
 * @return array|null Profile data or null if not found
 */
function get_user_profile($user_id) {
    global $conn;
    
    $sql = "SELECT u.*, p.phone, p.address, p.city, p.state, p.country, p.postal_code, 
                   p.avatar, p.bio, p.notification_preferences, p.timezone
            FROM users u 
            LEFT JOIN user_profiles p ON u.id = p.user_id 
            WHERE u.id = ?";
    $result = execute_query($sql, [$user_id]);
    
    if ($result && $result->num_rows > 0) {
        $profile = $result->fetch_assoc();
        $profile['notification_preferences'] = json_decode($profile['notification_preferences'] ?? '{}', true);
        return $profile;
    }
    
    return null;
}

/**
 * Update user profile
 * 
 * @param int $user_id User ID
 * @param array $profile_data Profile data to update
 * @return bool True if updated successfully
 */
function update_user_profile($user_id, $profile_data) {
    global $conn;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update user table
        $user_fields = ['name', 'email'];
        $user_updates = [];
        $user_params = [];
        
        foreach ($user_fields as $field) {
            if (isset($profile_data[$field])) {
                $user_updates[] = "$field = ?";
                $user_params[] = $profile_data[$field];
            }
        }
        
        if (!empty($user_updates)) {
            $user_params[] = $user_id;
            $sql = "UPDATE users SET " . implode(', ', $user_updates) . " WHERE id = ?";
            execute_query($sql, $user_params);
        }
        
        // Update profile table
        $profile_fields = [
            'phone', 'address', 'city', 'state', 'country', 'postal_code',
            'bio', 'timezone'
        ];
        
        // Handle notification preferences
        if (isset($profile_data['notification_preferences'])) {
            $profile_data['notification_preferences'] = json_encode($profile_data['notification_preferences']);
        }
        
        $profile_updates = [];
        $profile_params = [];
        
        foreach ($profile_fields as $field) {
            if (isset($profile_data[$field])) {
                $profile_updates[] = "$field = ?";
                $profile_params[] = $profile_data[$field];
            }
        }
        
        if (!empty($profile_updates)) {
            // Check if profile exists
            $check_sql = "SELECT 1 FROM user_profiles WHERE user_id = ?";
            $check_result = execute_query($check_sql, [$user_id]);
            
            if ($check_result && $check_result->num_rows > 0) {
                // Update existing profile
                $profile_params[] = $user_id;
                $sql = "UPDATE user_profiles SET " . implode(', ', $profile_updates) . " WHERE user_id = ?";
            } else {
                // Insert new profile
                $profile_fields = array_keys($profile_data);
                $profile_values = array_fill(0, count($profile_fields), '?');
                $profile_params[] = $user_id;
                $sql = "INSERT INTO user_profiles (user_id, " . implode(', ', $profile_fields) . ") 
                        VALUES (?, " . implode(', ', $profile_values) . ")";
            }
            
            execute_query($sql, $profile_params);
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Profile update failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Update user avatar
 * 
 * @param int $user_id User ID
 * @param array $file File data from $_FILES
 * @return bool|string New avatar path or false on failure
 */
function update_user_avatar($user_id, $file) {
    global $conn;
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types) || $file['size'] > $max_size) {
        return false;
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = 'uploads/avatars/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('avatar_') . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Update database
        $sql = "UPDATE user_profiles SET avatar = ? WHERE user_id = ?";
        if (execute_query($sql, [$filepath, $user_id])) {
            return $filepath;
        }
    }
    
    return false;
}

/**
 * Get user notification preferences
 * 
 * @param int $user_id User ID
 * @return array Notification preferences
 */
function get_notification_preferences($user_id) {
    global $conn;
    
    $sql = "SELECT notification_preferences FROM user_profiles WHERE user_id = ?";
    $result = execute_query($sql, [$user_id]);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return json_decode($row['notification_preferences'] ?? '{}', true);
    }
    
    return [
        'email' => true,
        'sms' => false,
        'push' => true,
        'marketing' => false
    ];
}

/**
 * Update user notification preferences
 * 
 * @param int $user_id User ID
 * @param array $preferences Notification preferences
 * @return bool True if updated successfully
 */
function update_notification_preferences($user_id, $preferences) {
    global $conn;
    
    $preferences_json = json_encode($preferences);
    
    $sql = "UPDATE user_profiles SET notification_preferences = ? WHERE user_id = ?";
    return execute_query($sql, [$preferences_json, $user_id]);
}

/**
 * Get user timezone
 * 
 * @param int $user_id User ID
 * @return string User timezone
 */
function get_user_timezone($user_id) {
    global $conn;
    
    $sql = "SELECT timezone FROM user_profiles WHERE user_id = ?";
    $result = execute_query($sql, [$user_id]);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['timezone'] ?? 'UTC';
    }
    
    return 'UTC';
}

/**
 * Update user timezone
 * 
 * @param int $user_id User ID
 * @param string $timezone Timezone
 * @return bool True if updated successfully
 */
function update_user_timezone($user_id, $timezone) {
    global $conn;
    
    $sql = "UPDATE user_profiles SET timezone = ? WHERE user_id = ?";
    return execute_query($sql, [$timezone, $user_id]);
}

/**
 * Get user activity summary
 * 
 * @param int $user_id User ID
 * @return array Activity summary
 */
function get_user_activity_summary($user_id) {
    global $conn;
    
    $summary = [
        'total_logins' => 0,
        'last_login' => null,
        'total_actions' => 0,
        'recent_actions' => []
    ];
    
    // Get login count
    $sql = "SELECT COUNT(*) as total FROM login_logs WHERE user_id = ?";
    $result = execute_query($sql, [$user_id]);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $summary['total_logins'] = (int)$row['total'];
    }
    
    // Get last login
    $sql = "SELECT created_at FROM login_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
    $result = execute_query($sql, [$user_id]);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $summary['last_login'] = $row['created_at'];
    }
    
    // Get activity count
    $sql = "SELECT COUNT(*) as total FROM activity_logs WHERE user_id = ?";
    $result = execute_query($sql, [$user_id]);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $summary['total_actions'] = (int)$row['total'];
    }
    
    // Get recent actions
    $sql = "SELECT action, details, created_at 
            FROM activity_logs 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 5";
    $result = execute_query($sql, [$user_id]);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $summary['recent_actions'][] = $row;
        }
    }
    
    return $summary;
} 
<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: ../../login.php");
    exit();
}

$is_superadmin = ($_SESSION['user_role'] === 'superadmin');
$is_agent = ($_SESSION['user_role'] === 'agent');

require_once '../../include/db.php';

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Function to check if table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Create users table if it doesn't exist
if (!tableExists($conn, 'users')) {
    $create_users_sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('superadmin', 'agent') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($create_users_sql)) {
        die("Error creating users table: " . $conn->error);
    }
}

// Create company_settings table if it doesn't exist
if (!tableExists($conn, 'company_settings')) {
    $create_table_sql = "CREATE TABLE company_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(255) NOT NULL,
        logo_url VARCHAR(255),
        phone VARCHAR(50) NOT NULL,
        email VARCHAR(255) NOT NULL,
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($create_table_sql)) {
        die("Error creating company_settings table: " . $conn->error);
    }
}

// Create activity_logs table if it doesn't exist
if (!tableExists($conn, 'activity_logs')) {
    $create_activity_logs_sql = "CREATE TABLE activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        activity VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($create_activity_logs_sql)) {
        die("Error creating activity_logs table: " . $conn->error);
    }
}

// Get company settings
$company_settings = null;
$sql = "SELECT * FROM company_settings LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $company_settings = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Sanitize input
        $company_name = sanitize_input($_POST['company_name']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        
        // Handle logo upload
        $logo_url = $company_settings['logo_url'] ?? ''; // Keep existing logo by default
        
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['logo']['type'], $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, PNG and GIF are allowed.");
            }
            
            if ($_FILES['logo']['size'] > $max_size) {
                throw new Exception("File size too large. Maximum size is 5MB.");
            }
            
            // Create upload directory if it doesn't exist
            $upload_dir = '../../assets/images/logos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '.' . $extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                // Delete old logo if exists
                if (!empty($company_settings['logo_url'])) {
                    $old_logo_path = '../../' . $company_settings['logo_url'];
                    if (file_exists($old_logo_path)) {
                        unlink($old_logo_path);
                    }
                }
                
                $logo_url = 'assets/images/logos/' . $filename;
            } else {
                throw new Exception("Failed to upload logo. Please check directory permissions.");
            }
        }
        
        // Validate required fields
        if (empty($company_name) || empty($phone) || empty($email)) {
            throw new Exception("Company name, phone, and email are required");
        }
        
        // Check if settings exist
        if ($company_settings) {
            // Update existing settings
            $sql = "UPDATE company_settings SET 
                    company_name = ?, 
                    logo_url = ?, 
                    phone = ?, 
                    email = ?, 
                    address = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparing update statement: " . $conn->error);
            }
            $stmt->bind_param("sssssi", $company_name, $logo_url, $phone, $email, $address, $company_settings['id']);
        } else {
            // Insert new settings
            $sql = "INSERT INTO company_settings (company_name, logo_url, phone, email, address) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparing insert statement: " . $conn->error);
            }
            $stmt->bind_param("sssss", $company_name, $logo_url, $phone, $email, $address);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error saving settings: " . $stmt->error);
        }
        
        // Log the activity
        $user_id = $_SESSION['user_id'];
        $activity = "Updated company settings";
        $activity_sql = "INSERT INTO activity_logs (user_id, action) VALUES ($user_id, '$activity')";
        if (!$conn->query($activity_sql)) {
            throw new Exception("Error logging activity: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Set success message
        $_SESSION['success'] = "Settings updated successfully";
        
        // Refresh the page to show updated settings
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Settings - FLEXCEE Logistics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1a365d',
                        secondary: '#2d3748',
                        accent: '#e53e3e'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-primary text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-2">
                        <i class="fas fa-truck text-2xl"></i>
                        <span class="text-xl font-bold">FLEXCEE Logistics</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../../logout.php" class="text-sm hover:text-gray-300">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex">
            <!-- Sidebar -->
            <div class="w-64 bg-white shadow-lg rounded-lg p-4 mr-8">
                <nav class="space-y-2">
                    <a href="index.php" class="block px-4 py-2 text-gray-600 hover:bg-primary hover:text-white rounded-lg">
                        <i class="fas fa-home mr-2"></i> Dashboard
                    </a>
                    <a href="agents.php" class="block px-4 py-2 text-gray-600 hover:bg-primary hover:text-white rounded-lg">
                        <i class="fas fa-users mr-2"></i> Agents
                    </a>
                    <a href="consignments.php" class="block px-4 py-2 text-gray-600 hover:bg-primary hover:text-white rounded-lg">
                        <i class="fas fa-box mr-2"></i> Consignments
                    </a>
                    <a href="settings.php" class="block px-4 py-2 bg-primary text-white rounded-lg">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                </nav>
            </div>

            <!-- Settings Form -->
            <div class="flex-1">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h1 class="text-2xl font-bold text-primary mb-6">Company Settings</h1>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                            <input type="text" name="company_name" value="<?php echo htmlspecialchars($company_settings['company_name'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="logo">
                                Company Logo
                            </label>
                            <?php if (!empty($company_settings['logo_url'])): ?>
                                <div class="mb-2">
                                    <img src="../../<?php echo htmlspecialchars($company_settings['logo_url']); ?>" 
                                         alt="Company Logo" 
                                         class="max-h-32 mb-2">
                                </div>
                            <?php endif; ?>
                            <input type="file" 
                                   name="logo" 
                                   id="logo" 
                                   accept="image/jpeg,image/png,image/gif"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <p class="text-gray-600 text-xs mt-1">Max file size: 5MB. Allowed formats: JPG, PNG, GIF</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($company_settings['phone'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($company_settings['email'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea name="address" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($company_settings['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-800">
                                <i class="fas fa-save mr-2"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
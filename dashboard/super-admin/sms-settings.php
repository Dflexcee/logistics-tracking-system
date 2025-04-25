<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is a super-admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Get SMS settings
$sms_settings = null;
$sql = "SELECT * FROM sms_settings LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $sms_settings = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $provider = $_POST['provider'];
    $api_key = $_POST['api_key'];
    $api_secret = $_POST['api_secret'];
    $sender_id = $_POST['sender_id'];
    $api_url = $_POST['api_url'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validate input
    if (empty($provider) || empty($api_key)) {
        $_SESSION['error'] = "Please fill in all required fields";
    } else {
        // Check if settings exist
        if ($sms_settings) {
            // Update existing settings
            $sql = "UPDATE sms_settings SET 
                    provider = ?, 
                    api_key = ?, 
                    api_secret = ?, 
                    sender_id = ?, 
                    api_url = ?,
                    is_active = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssii", $provider, $api_key, $api_secret, $sender_id, $api_url, $is_active, $sms_settings['id']);
        } else {
            // Insert new settings
            $sql = "INSERT INTO sms_settings (provider, api_key, api_secret, sender_id, api_url, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $provider, $api_key, $api_secret, $sender_id, $api_url, $is_active);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = "SMS settings saved successfully";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['error'] = "Error saving settings: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Settings - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="index.php" class="text-xl font-bold text-gray-800">Super Admin Dashboard</a>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center">
                            <a href="profile.php" class="text-gray-700 mr-4">Profile</a>
                            <a href="../../logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg h-screen">
            <div class="p-4">
                <nav class="space-y-2">
                    <a href="index.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-home mr-2"></i> Dashboard
                    </a>
                    <a href="consignments.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-box mr-2"></i> Consignments
                    </a>
                    <a href="agents.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-users mr-2"></i> Agents
                    </a>
                    <a href="tickets.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-ticket-alt mr-2"></i> Tickets
                    </a>
                    <a href="email-settings.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-envelope mr-2"></i> Email Settings
                    </a>
                    <a href="sms-settings.php" class="block px-4 py-2 bg-blue-50 text-blue-600 rounded-lg">
                        <i class="fas fa-sms mr-2"></i> SMS Settings
                    </a>
                    <a href="settings.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                </nav>
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 p-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            SMS Settings
                        </h3>
                    </div>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SMS Provider</label>
                                <select name="provider" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="twilio" <?php echo ($sms_settings['provider'] ?? '') === 'twilio' ? 'selected' : ''; ?>>Twilio</option>
                                    <option value="nexmo" <?php echo ($sms_settings['provider'] ?? '') === 'nexmo' ? 'selected' : ''; ?>>Nexmo (Vonage)</option>
                                    <option value="plivo" <?php echo ($sms_settings['provider'] ?? '') === 'plivo' ? 'selected' : ''; ?>>Plivo</option>
                                    <option value="custom" <?php echo ($sms_settings['provider'] ?? '') === 'custom' ? 'selected' : ''; ?>>Custom API</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                                <input type="text" name="api_key" value="<?php echo htmlspecialchars($sms_settings['api_key'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">API Secret</label>
                                <input type="password" name="api_secret" value="<?php echo htmlspecialchars($sms_settings['api_secret'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sender ID</label>
                                <input type="text" name="sender_id" value="<?php echo htmlspecialchars($sms_settings['sender_id'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">API URL</label>
                                <input type="text" name="api_url" value="<?php echo htmlspecialchars($sms_settings['api_url'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-sm text-gray-500 mt-1">Leave empty for default API endpoints</p>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" 
                                       <?php echo ($sms_settings['is_active'] ?? 1) ? 'checked' : ''; ?>
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    Enable SMS Service
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
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
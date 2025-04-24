<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Get tracking number from URL or form
$tracking_number = isset($_GET['tracking_number']) ? sanitize_input($_GET['tracking_number']) : '';
$consignment = null;
$error = null;

if (!empty($tracking_number)) {
    try {
        // Get consignment details with latest status
        $sql = "SELECT c.*, 
                u.name as agent_name,
                s.name as sender_name, s.phone as sender_phone, s.address as sender_address,
                r.name as receiver_name, r.phone as receiver_phone, r.address as receiver_address,
                th.status as current_status,
                th.location as current_location,
                th.created_at as status_date
                FROM consignments c 
                LEFT JOIN users u ON c.agent_id = u.id 
                LEFT JOIN senders s ON c.sender_id = s.id
                LEFT JOIN receivers r ON c.receiver_id = r.id
                LEFT JOIN (
                    SELECT consignment_id, status, location, created_at
                    FROM tracking_history
                    WHERE (consignment_id, created_at) IN (
                        SELECT consignment_id, MAX(created_at)
                        FROM tracking_history
                        GROUP BY consignment_id
                    )
                ) th ON c.id = th.consignment_id
                WHERE c.tracking_number = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparing consignment query: " . $conn->error);
        }
        
        $stmt->bind_param("s", $tracking_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $consignment = $result->fetch_assoc();
            // Redirect to tracking history page
            header("Location: tracking-history.php?tracking_number=" . urlencode($tracking_number));
            exit();
        } else {
            $error = "No consignment found with tracking number: " . htmlspecialchars($tracking_number);
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Consignment - FLEXCEE Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-blue-900 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-2">
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
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            <div class="w-full lg:w-64 bg-white shadow-lg rounded-lg p-4">
                <nav class="space-y-2">
                    <a href="index.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-900 hover:text-white rounded-lg">
                        <i class="fas fa-home mr-2"></i> Dashboard
                    </a>
                    <a href="agents.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-900 hover:text-white rounded-lg">
                        <i class="fas fa-users mr-2"></i> Agents
                    </a>
                    <a href="consignments.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-900 hover:text-white rounded-lg">
                        <i class="fas fa-box mr-2"></i> Consignments
                    </a>
                    <a href="track.php" class="block px-4 py-2 bg-blue-900 text-white rounded-lg">
                        <i class="fas fa-search mr-2"></i> Track
                    </a>
                    <a href="settings.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-900 hover:text-white rounded-lg">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                </nav>
            </div>

            <!-- Content -->
            <div class="flex-1">
                <!-- Search Form -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h1 class="text-2xl font-bold text-gray-800 mb-6">Track Consignment</h1>
                    
                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="track.php" method="GET" class="max-w-2xl mx-auto">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <input type="text" name="tracking_number" value="<?php echo htmlspecialchars($tracking_number); ?>" 
                                       placeholder="Enter tracking number" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <button type="submit" class="px-6 py-3 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition-colors">
                                <i class="fas fa-search mr-2"></i> Track
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 text-center text-gray-600">
                        <p class="mb-2">Need help tracking your consignment?</p>
                        <p>Contact our support team at <a href="mailto:support@flexcee.com" class="text-blue-600 hover:underline">support@flexcee.com</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
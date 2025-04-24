<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is an agent
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header('Location: ../../login.php');
    exit();
}

$agent_id = $_SESSION['user_id'];

// Get agent information
$agent_sql = "SELECT * FROM users WHERE id = ?";
$agent_stmt = $conn->prepare($agent_sql);
if ($agent_stmt === false) {
    die("Error preparing agent query: " . $conn->error);
}
$agent_stmt->bind_param("i", $agent_id);
$agent_stmt->execute();
$agent = $agent_stmt->get_result()->fetch_assoc();

if (!$agent) {
    die("Agent not found");
}

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total_consignments,
    SUM(IF(status = 'pending', 1, 0)) as pending_consignments,
    SUM(IF(status = 'in_transit', 1, 0)) as in_transit_consignments,
    SUM(IF(status = 'delivered', 1, 0)) as delivered_consignments
    FROM consignments 
    WHERE agent_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
if ($stats_stmt === false) {
    die("Error preparing statistics query: " . $conn->error);
}
$stats_stmt->bind_param("i", $agent_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Initialize stats if null
$stats = array(
    'total_consignments' => $stats['total_consignments'] ?? 0,
    'pending_consignments' => $stats['pending_consignments'] ?? 0,
    'in_transit_consignments' => $stats['in_transit_consignments'] ?? 0,
    'delivered_consignments' => $stats['delivered_consignments'] ?? 0
);

// Get recent consignments
$recent_sql = "SELECT c.*, 
    s.name as sender_name, 
    r.name as receiver_name
    FROM consignments c
    LEFT JOIN senders s ON c.sender_id = s.id
    LEFT JOIN receivers r ON c.receiver_id = r.id
    WHERE c.agent_id = ?
    ORDER BY c.created_at DESC
    LIMIT 5";

// First, let's check if the tables exist
$check_tables_sql = "SHOW TABLES LIKE 'consignments'";
$result = $conn->query($check_tables_sql);
if ($result->num_rows == 0) {
    die("Please run create_tables.php first to create the necessary tables.");
}

$recent_stmt = $conn->prepare($recent_sql);
if ($recent_stmt === false) {
    die("Error preparing recent consignments query: " . $conn->error);
}
$recent_stmt->bind_param("i", $agent_id);
$recent_stmt->execute();
$recent_consignments = $recent_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// If no consignments found, initialize empty array
if (!$recent_consignments) {
    $recent_consignments = array();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="index.php" class="text-xl font-bold text-gray-800">Agent Dashboard</a>
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
                    <a href="index.php" class="block px-4 py-2 bg-blue-50 text-blue-600 rounded-lg">
                        <i class="fas fa-home mr-2"></i> Dashboard
                    </a>
                    <a href="consignments.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-box mr-2"></i> Consignments
                    </a>
                    <a href="create-consignment.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-plus mr-2"></i> New Consignment
                    </a>
                    <a href="edit-consignment.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-edit mr-2"></i> Edit Consignment
                    </a>
                    <a href="update-status.php" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                        <i class="fas fa-sync-alt"></i>
                        <span>Update Status</span>
                    </a>
                    <a href="payment-info.php" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Payment Information</span>
                    </a>
                    <a href="settings.php" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <a href="profile.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                </nav>
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 p-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Welcome, <?php echo htmlspecialchars($agent['name']); ?>!</h1>
                <p class="text-gray-600">Here's an overview of your consignments</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Consignments -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-box text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Consignments</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_consignments']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Pending Consignments -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Pending</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['pending_consignments']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- In Transit Consignments -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-truck text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">In Transit</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['in_transit_consignments']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Delivered Consignments -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Delivered</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['delivered_consignments']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Consignments -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Consignments</h3>
                        <a href="consignments.php" class="text-blue-600 hover:text-blue-800">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receiver</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recent_consignments as $consignment): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($consignment['tracking_number']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($consignment['sender_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($consignment['receiver_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php
                                                switch($consignment['status']) {
                                                    case 'pending':
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    case 'in_transit':
                                                        echo 'bg-blue-100 text-blue-800';
                                                        break;
                                                    case 'delivered':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $consignment['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($consignment['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="view-consignment.php?id=<?php echo $consignment['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                            <a href="edit-consignment.php?id=<?php echo $consignment['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">Edit</a>
                                            <a href="update-status.php?id=<?php echo $consignment['id']; ?>" class="text-green-600 hover:text-green-900">Update</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
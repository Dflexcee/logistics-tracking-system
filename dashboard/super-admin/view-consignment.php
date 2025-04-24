<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Get consignment ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    $_SESSION['error'] = 'Invalid consignment ID';
    header('Location: consignments.php');
    exit();
}

try {
    // Get consignment details with sender and receiver information
    $sql = "SELECT c.*, 
            s.name as sender_name, s.phone as sender_phone, s.address as sender_address,
            r.name as receiver_name, r.phone as receiver_phone, r.address as receiver_address,
            u.name as agent_name
            FROM consignments c
            LEFT JOIN senders s ON c.sender_id = s.id
            LEFT JOIN receivers r ON c.receiver_id = r.id
            LEFT JOIN users u ON c.agent_id = u.id
            WHERE c.id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Consignment not found';
        header('Location: consignments.php');
        exit();
    }
    
    $consignment = $result->fetch_assoc();
    
    // Get tracking history
    $history_sql = "SELECT * FROM tracking_history WHERE consignment_id = ? ORDER BY created_at DESC";
    $history_stmt = $conn->prepare($history_sql);
    $history_stmt->bind_param("i", $id);
    $history_stmt->execute();
    $history_result = $history_stmt->get_result();
    $tracking_history = $history_result->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
    header('Location: consignments.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Consignment - Super Admin Dashboard</title>
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
                    <a href="consignments.php" class="block px-4 py-2 bg-blue-50 text-blue-600 rounded-lg">
                        <i class="fas fa-box mr-2"></i> Consignments
                    </a>
                    <a href="create-consignment.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-plus mr-2"></i> New Consignment
                    </a>
                    <a href="agents.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-users mr-2"></i> Agents
                    </a>
                    <a href="profile.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-user mr-2"></i> Profile
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
                            Consignment Details
                        </h3>
                        <div class="flex space-x-2">
                            <a href="edit-consignment.php?id=<?php echo $id; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                                <i class="fas fa-edit mr-2"></i> Edit
                            </a>
                            <a href="update-status.php?id=<?php echo $id; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                <i class="fas fa-truck mr-2"></i> Update Status
                            </a>
                            <a href="print.php?id=<?php echo $id; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700" target="_blank">
                                <i class="fas fa-print mr-2"></i> Print
                            </a>
                        </div>
                    </div>

                    <!-- Consignment Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Tracking Information -->
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-700">Tracking Information</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Tracking Number</p>
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['tracking_number']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Status</p>
                                        <p class="text-sm font-medium">
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
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Created At</p>
                                        <p class="text-sm font-medium"><?php echo date('M d, Y H:i', strtotime($consignment['created_at'])); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Last Updated</p>
                                        <p class="text-sm font-medium"><?php echo date('M d, Y H:i', strtotime($consignment['updated_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Package Information -->
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-700">Package Information</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Weight</p>
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['weight']); ?> kg</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Dimensions</p>
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['dimensions']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Package Type</p>
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['package_type']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Amount Paid</p>
                                        <p class="text-sm font-medium"><?php echo number_format($consignment['amount_paid'], 2); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Special Instructions</p>
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['special_instructions'] ?? 'None'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sender and Receiver Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Sender Information -->
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-700">Sender Information</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="space-y-2">
                                    <div>
                                        <p class="text-sm text-gray-500">Name</p>
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['sender_name']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Phone</p>
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['sender_phone']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Address</p>
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['sender_address']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Receiver Information -->
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-700">Receiver Information</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="space-y-2">
                                    <div>
                                        <p class="text-sm text-gray-500">Name</p>
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['receiver_name']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Phone</p>
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['receiver_phone']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Address</p>
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['receiver_address']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tracking History -->
                    <div class="space-y-4">
                        <h4 class="text-md font-medium text-gray-700">Tracking History</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?php if (empty($tracking_history)): ?>
                                <p class="text-sm text-gray-500">No tracking history available.</p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($tracking_history as $history): ?>
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-circle text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($history['status']); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($history['location']); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo date('M d, Y H:i', strtotime($history['created_at'])); ?></p>
                                                <?php if (!empty($history['notes'])): ?>
                                                    <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($history['notes']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is an agent
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header('Location: ../../login.php');
    exit();
}

$agent_id = $_SESSION['user_id'];

// Get consignment ID
$consignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($consignment_id === 0) {
    header('Location: consignments.php');
    exit();
}

// Get consignment details
$sql = "SELECT c.*, 
        s.name as sender_name, s.phone as sender_phone, s.address as sender_address,
        r.name as receiver_name, r.phone as receiver_phone, r.address as receiver_address
        FROM consignments c
        LEFT JOIN senders s ON c.sender_id = s.id
        LEFT JOIN receivers r ON c.receiver_id = r.id
        WHERE c.id = ? AND c.agent_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $consignment_id, $agent_id);
$stmt->execute();
$result = $stmt->get_result();
$consignment = $result->fetch_assoc();

if (!$consignment) {
    header('Location: consignments.php');
    exit();
}

// Get tracking history
$history_sql = "SELECT * FROM tracking_history WHERE consignment_id = ? ORDER BY created_at DESC";
$history_stmt = $conn->prepare($history_sql);
$history_stmt->bind_param("i", $consignment_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
$tracking_history = $history_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Consignment - Agent Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .print-only {
                display: block;
            }
            body {
                margin: 0;
                padding: 20px;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation (Hidden when printing) -->
    <nav class="bg-white shadow-lg no-print">
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
        <!-- Sidebar (Hidden when printing) -->
        <div class="w-64 bg-white shadow-lg h-screen no-print">
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
                    <a href="profile.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                </nav>
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 p-8">
            <!-- Print Button (Hidden when printing) -->
            <div class="mb-4 no-print">
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>

            <!-- Consignment Details -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <!-- Header -->
                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-bold text-gray-900">Consignment Details</h1>
                        <p class="text-gray-600">Tracking Number: <?php echo htmlspecialchars($consignment['tracking_number']); ?></p>
                    </div>

                    <!-- Status -->
                    <div class="mb-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-2">Current Status</h2>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Status</p>
                                    <p class="text-sm font-medium">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php
                                            switch($consignment['status']) {
                                                case 'On Transit':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                                case 'Out for Delivery':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'On Hold':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                                case 'Delivered':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'Clearance Pending':
                                                    echo 'bg-purple-100 text-purple-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php echo $consignment['status']; ?>
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Last Updated</p>
                                    <p class="text-sm font-medium"><?php echo date('M d, Y H:i', strtotime($consignment['updated_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sender and Receiver Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Sender Information -->
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-2">Sender Information</h2>
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
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-2">Receiver Information</h2>
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

                    <!-- Package Information -->
                    <div class="mb-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-2">Package Information</h2>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                    <p class="text-sm text-gray-500">Special Instructions</p>
                                    <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['special_instructions'] ?? 'None'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tracking History -->
                    <div class="mb-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-2">Tracking History</h2>
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

                    <!-- Footer -->
                    <div class="mt-8 text-center text-sm text-gray-500">
                        <p>Generated on <?php echo date('M d, Y H:i'); ?></p>
                        <p>This is a computer-generated document and does not require a signature.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
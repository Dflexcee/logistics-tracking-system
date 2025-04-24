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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Update consignment status
        $update_sql = "UPDATE consignments SET 
            status = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND agent_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sii", 
            $_POST['status'],
            $consignment_id,
            $agent_id
        );
        $update_stmt->execute();

        // Add tracking history
        $history_sql = "INSERT INTO tracking_history (consignment_id, status, location, notes, created_at) 
            VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $history_stmt = $conn->prepare($history_sql);
        $history_stmt->bind_param("isss", 
            $consignment_id,
            $_POST['status'],
            $_POST['location'],
            $_POST['notes']
        );
        $history_stmt->execute();

        // Commit transaction
        $conn->commit();

        // Redirect to view page
        header("Location: view-consignment.php?id=" . $consignment_id);
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error = "Error updating status: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status - Agent Dashboard</title>
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
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Update Consignment Status
                        </h3>
                        <a href="view-consignment.php?id=<?php echo $consignment_id; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700">
                            <i class="fas fa-arrow-left mr-2"></i> Back to View
                        </a>
                    </div>

                    <!-- Consignment Details -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-2">Consignment Details</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Tracking Number</p>
                                    <p class="text-sm font-medium"><?php echo htmlspecialchars($consignment['tracking_number']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Current Status</p>
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
                            </div>
                        </div>
                    </div>

                    <form method="POST" class="space-y-6">
                        <!-- Status Update -->
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-700">Update Status</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">New Status</label>
                                    <select name="status" id="status" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="On Transit">On Transit</option>
                                        <option value="Out for Delivery">Out for Delivery</option>
                                        <option value="On Hold">On Hold</option>
                                        <option value="Delivered">Delivered</option>
                                        <option value="Clearance Pending">Clearance Pending</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                    <input type="text" name="location" id="location" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Enter current location">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea name="notes" id="notes" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Enter any additional notes about the status update"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i> Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
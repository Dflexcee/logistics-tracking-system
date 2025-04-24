<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        $consignment_id = $_POST['consignment_id'];
        $amount_paid = $_POST['amount_paid'];
        $location = $_POST['location'];
        $status = $_POST['status'];
        $notes = $_POST['notes'] ?? '';

        // Update consignment amount
        $update_sql = "UPDATE consignments SET amount_paid = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            throw new Exception("Error preparing update query: " . $conn->error);
        }
        $update_stmt->bind_param("di", $amount_paid, $consignment_id);
        if (!$update_stmt->execute()) {
            throw new Exception("Error updating amount paid: " . $update_stmt->error);
        }

        // Add new tracking history entry for location update
        $history_sql = "INSERT INTO tracking_history (consignment_id, status, location, notes) VALUES (?, ?, ?, ?)";
        $history_stmt = $conn->prepare($history_sql);
        if (!$history_stmt) {
            throw new Exception("Error preparing history query: " . $conn->error);
        }
        $history_stmt->bind_param("isss", $consignment_id, $status, $location, $notes);
        if (!$history_stmt->execute()) {
            throw new Exception("Error adding tracking history: " . $history_stmt->error);
        }

        // Update consignment status
        $status_sql = "UPDATE consignments SET status = ? WHERE id = ?";
        $status_stmt = $conn->prepare($status_sql);
        if (!$status_stmt) {
            throw new Exception("Error preparing status update query: " . $conn->error);
        }
        $status_stmt->bind_param("si", $status, $consignment_id);
        if (!$status_stmt->execute()) {
            throw new Exception("Error updating status: " . $status_stmt->error);
        }

        $conn->commit();
        $_SESSION['success'] = "Consignment updated successfully";
        header('Location: tracking-history.php?tracking_number=' . $_POST['tracking_number']);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header('Location: tracking-history.php?tracking_number=' . $_POST['tracking_number']);
        exit();
    }
}

// Get consignment ID from URL
$consignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($consignment_id === 0) {
    $_SESSION['error'] = "Invalid consignment ID";
    header('Location: consignments.php');
    exit();
}

// Get consignment details
$sql = "SELECT c.*, 
        s.name as sender_name, s.phone as sender_phone, s.address as pickup_location,
        r.name as receiver_name, r.phone as receiver_phone, r.address as drop_location,
        u.name as agent_name
        FROM consignments c 
        LEFT JOIN users u ON c.agent_id = u.id 
        LEFT JOIN senders s ON c.sender_id = s.id
        LEFT JOIN receivers r ON c.receiver_id = r.id
        WHERE c.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $consignment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Consignment not found";
    header('Location: consignments.php');
    exit();
}

$consignment = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Consignment - Super Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-2">
                        <i class="fas fa-box text-2xl"></i>
                        <span class="text-xl font-bold">Logistics Dashboard</span>
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

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <nav class="mt-4">
                <a href="index.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                <a href="consignments.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600">
                    <i class="fas fa-boxes mr-2"></i> Consignments
                </a>
                <a href="agents.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600">
                    <i class="fas fa-users mr-2"></i> Agents
                </a>
                <a href="tracking.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600">
                    <i class="fas fa-search-location mr-2"></i> Track Shipment
                </a>
                <a href="reports.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600">
                    <i class="fas fa-chart-bar mr-2"></i> Reports
                </a>
                <a href="settings.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-3xl mx-auto">
                <!-- Breadcrumb -->
                <div class="mb-6 flex items-center text-gray-600">
                    <a href="index.php" class="hover:text-blue-600">Dashboard</a>
                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                    <a href="consignments.php" class="hover:text-blue-600">Consignments</a>
                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                    <span class="text-gray-400">Update Consignment</span>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php 
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php 
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Update Consignment Details</h2>
                    
                    <form action="" method="POST" class="space-y-6">
                        <input type="hidden" name="consignment_id" value="<?php echo $consignment['id']; ?>">
                        <input type="hidden" name="tracking_number" value="<?php echo $consignment['tracking_number']; ?>">

                        <!-- Current Information -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="font-semibold mb-2">Current Information</h3>
                            <p><strong>Tracking Number:</strong> <?php echo htmlspecialchars($consignment['tracking_number']); ?></p>
                            <p><strong>Current Status:</strong> <?php echo htmlspecialchars($consignment['status']); ?></p>
                            <p><strong>Amount Paid:</strong> $<?php echo number_format($consignment['amount_paid'], 2); ?></p>
                        </div>

                        <!-- Amount Paid -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid</label>
                            <input type="number" name="amount_paid" step="0.01" min="0" 
                                   value="<?php echo $consignment['amount_paid']; ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- New Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="Pending" <?php echo $consignment['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="On Transit" <?php echo $consignment['status'] === 'On Transit' ? 'selected' : ''; ?>>On Transit</option>
                                <option value="Out for Delivery" <?php echo $consignment['status'] === 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                <option value="On Hold" <?php echo $consignment['status'] === 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                                <option value="Delivered" <?php echo $consignment['status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="Clearance Pending" <?php echo $consignment['status'] === 'Clearance Pending' ? 'selected' : ''; ?>>Clearance Pending</option>
                            </select>
                        </div>

                        <!-- New Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Location</label>
                            <input type="text" name="location" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Enter current location">
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Enter any additional notes"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4">
                            <a href="tracking-history.php?tracking_number=<?php echo urlencode($consignment['tracking_number']); ?>" 
                               class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Update Consignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Highlight current page in navigation
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('nav a');
            navLinks.forEach(link => {
                if (currentPath.includes('consignments.php') || currentPath.includes('update-consignment.php')) {
                    if (link.href.includes('consignments.php')) {
                        link.classList.add('bg-blue-50', 'text-blue-600');
                    }
                }
            });
        });
    </script>
</body>
</html> 
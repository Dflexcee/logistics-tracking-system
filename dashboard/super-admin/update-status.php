<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Get company settings
$company_settings = null;
$sql = "SELECT * FROM company_settings LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $company_settings = $result->fetch_assoc();
}

// Get all consignments
$consignments = [];
$error = null; // Initialize error variable
try {
    $sql = "SELECT c.*, 
            u.name as agent_name,
            s.name as sender_name,
            r.name as receiver_name
            FROM consignments c 
            LEFT JOIN users u ON c.agent_id = u.id 
            LEFT JOIN senders s ON c.sender_id = s.id
            LEFT JOIN receivers r ON c.receiver_id = r.id
            ORDER BY c.created_at DESC";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $consignments[] = $row;
        }
    }
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status - FLEXCEE Logistics</title>
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
                        <?php if (!empty($company_settings['logo_url'])): ?>
                            <img src="../../<?php echo htmlspecialchars($company_settings['logo_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($company_settings['company_name']); ?>" 
                                 class="h-8 w-8 rounded-full">
                        <?php endif; ?>
                        <span class="text-xl font-bold"><?php echo htmlspecialchars($company_settings['company_name'] ?? 'FLEXCEE Logistics'); ?></span>
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
                    <a href="index.php" class="block px-4 py-2 text-gray-600 hover:bg-primary hover:text-white rounded-lg">
                        <i class="fas fa-home mr-2"></i> Dashboard
                    </a>
                    <a href="agents.php" class="block px-4 py-2 text-gray-600 hover:bg-primary hover:text-white rounded-lg">
                        <i class="fas fa-users mr-2"></i> Agents
                    </a>
                    <a href="consignments.php" class="block px-4 py-2 text-gray-600 hover:bg-primary hover:text-white rounded-lg">
                        <i class="fas fa-box mr-2"></i> Consignments
                    </a>
                    <a href="settings.php" class="block px-4 py-2 text-gray-600 hover:bg-primary hover:text-white rounded-lg">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                    <a href="update-status.php" class="block px-4 py-2 bg-primary text-white rounded-lg">
                        <i class="fas fa-sync-alt mr-2"></i> Update Status
                    </a>
                </nav>
            </div>

            <!-- Consignments Table -->
            <div class="flex-1">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h1 class="text-2xl font-bold text-primary mb-6">Update Consignment Status</h1>
                    
                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Consignments Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking #</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receiver</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($consignments as $consignment): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($consignment['tracking_number']); ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php
                                                switch($consignment['status']) {
                                                    case 'On Transit':
                                                        echo 'bg-blue-100 text-blue-800';
                                                        break;
                                                    case 'Out for Delivery':
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    case 'Delivered':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'On Hold':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    case 'Clearance Pending':
                                                        echo 'bg-purple-100 text-purple-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>">
                                                <?php echo htmlspecialchars($consignment['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($consignment['sender_name']); ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($consignment['receiver_name']); ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($consignment['agent_name']); ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y H:i', strtotime($consignment['created_at'])); ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="showUpdateModal('<?php echo $consignment['tracking_number']; ?>', '<?php echo $consignment['status']; ?>')"
                                                    class="text-primary hover:text-blue-900 mr-3">
                                                <i class="fas fa-edit"></i> Update
                                            </button>
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

    <!-- Update Status Modal -->
    <div id="updateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Update Consignment Status</h3>
                <form action="insert-update.php" method="POST" class="space-y-4">
                    <input type="hidden" name="tracking_number" id="modalTrackingNumber">
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">New Status</label>
                        <select name="status" id="modalStatus" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <option value="On Transit">On Transit</option>
                            <option value="Out for Delivery">Out for Delivery</option>
                            <option value="On Hold">On Hold</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Clearance Pending">Clearance Pending</option>
                        </select>
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" name="location" id="modalLocation" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                placeholder="Enter current location">
                    </div>

                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-700">Comment</label>
                        <textarea name="comment" id="modalComment" rows="3" required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                  placeholder="Enter status update comment"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideUpdateModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-white rounded-md hover:bg-blue-800">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showUpdateModal(trackingNumber, currentStatus) {
            document.getElementById('modalTrackingNumber').value = trackingNumber;
            document.getElementById('modalStatus').value = currentStatus;
            document.getElementById('updateModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function hideUpdateModal() {
            document.getElementById('updateModal').classList.add('hidden');
            document.body.style.overflow = 'auto'; // Restore background scrolling
        }

        // Close modal when clicking outside
        document.getElementById('updateModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideUpdateModal();
            }
        });
    </script>
</body>
</html> 
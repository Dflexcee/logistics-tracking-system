<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is a super-admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Get all consignments with sender and receiver information
$sql = "SELECT c.*, s.name as sender_name, s.phone as sender_phone, 
        r.name as receiver_name, r.phone as receiver_phone,
        u.name as agent_name,
        th.status as current_status,
        th.location as current_location,
        th.created_at as status_date
        FROM consignments c
        JOIN senders s ON c.sender_id = s.id
        JOIN receivers r ON c.receiver_id = r.id
        LEFT JOIN users u ON c.agent_id = u.id
        LEFT JOIN (
            SELECT consignment_id, status, location, created_at
            FROM tracking_history
            WHERE (consignment_id, created_at) IN (
                SELECT consignment_id, MAX(created_at)
                FROM tracking_history
                GROUP BY consignment_id
            )
        ) th ON c.id = th.consignment_id
        ORDER BY c.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Consignments - Super Admin</title>
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

    <!-- Add this after the navigation -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <button onclick="this.parentElement.parentElement.remove()" class="text-green-500 hover:text-green-700">
                    <i class="fas fa-times"></i>
                </button>
            </span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <button onclick="this.parentElement.parentElement.remove()" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

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
                    <a href="agents.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-users mr-2"></i> Agents
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
                            All Consignments
                        </h3>
                        <a href="create-consignment.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i> New Consignment
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receiver</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($row['tracking_number']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($row['sender_name']); ?><br>
                                        <small class="text-gray-500"><?php echo htmlspecialchars($row['sender_phone']); ?></small>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($row['receiver_name']); ?><br>
                                        <small class="text-gray-500"><?php echo htmlspecialchars($row['receiver_phone']); ?></small>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            $status = $row['current_status'] ?? 'Pending';
                                            echo $status === 'Delivered' ? 'bg-green-100 text-green-800' : 
                                                ($status === 'In Transit' ? 'bg-blue-100 text-blue-800' : 
                                                ($status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                'bg-gray-100 text-gray-800')); 
                                            ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                        <?php if ($row['status_date']): ?>
                                            <br>
                                            <small class="text-gray-500">
                                                <?php echo date('M d, Y H:i', strtotime($row['status_date'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo $row['current_location'] ? htmlspecialchars($row['current_location']) : 'N/A'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo $row['agent_name'] ? htmlspecialchars($row['agent_name']) : 'Unassigned'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="view-consignment.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="edit-consignment.php?id=<?php echo $row['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button onclick="showAssignModal(<?php echo $row['id']; ?>, <?php echo $row['agent_id'] ? $row['agent_id'] : 'null'; ?>)" 
                                                class="text-green-600 hover:text-green-900 mr-3">
                                            <i class="fas fa-user-plus"></i> Assign
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $row['id']; ?>)" 
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Agent Modal -->
    <div id="assignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Agent</h3>
                <form action="assign-agent.php" method="POST" class="space-y-4">
                    <input type="hidden" name="consignment_id" id="modalConsignmentId">
                    
                    <div>
                        <label for="agent_id" class="block text-sm font-medium text-gray-700">Select Agent</label>
                        <select name="agent_id" id="modalAgentId" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">Select an agent</option>
                            <?php
                            // Get all active agents
                            $agents_sql = "SELECT id, name FROM users WHERE role = 'agent' AND status = 'active'";
                            $agents_result = $conn->query($agents_sql);
                            while ($agent = $agents_result->fetch_assoc()) {
                                echo '<option value="' . $agent['id'] . '">' . htmlspecialchars($agent['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideAssignModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-white rounded-md hover:bg-blue-800">
                            Assign Agent
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showAssignModal(consignmentId, currentAgentId) {
            document.getElementById('modalConsignmentId').value = consignmentId;
            document.getElementById('modalAgentId').value = currentAgentId;
            document.getElementById('assignModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function hideAssignModal() {
            document.getElementById('assignModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function confirmDelete(consignmentId) {
            if (confirm('Are you sure you want to delete this consignment? This action cannot be undone.')) {
                window.location.href = 'delete-consignment.php?id=' + consignmentId;
            }
        }

        // Close modal when clicking outside
        document.getElementById('assignModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAssignModal();
            }
        });
    </script>
</body>
</html> 
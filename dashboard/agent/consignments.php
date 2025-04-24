<?php
session_start();
require_once '../../include/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is an agent
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header('Location: ../../login.php');
    exit();
}

$agent_id = $_SESSION['user_id'];

// Get search parameters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? sanitize_input($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_input($_GET['date_to']) : '';

try {
    // Check if required tables exist
    $required_tables = ['consignments', 'senders', 'receivers'];
    $missing_tables = [];
    
    foreach ($required_tables as $table) {
        $check = $conn->query("SHOW TABLES LIKE '$table'");
        if ($check->num_rows === 0) {
            $missing_tables[] = $table;
        }
    }
    
    if (!empty($missing_tables)) {
        throw new Exception("Missing required tables: " . implode(', ', $missing_tables));
    }

    // Build query
    $sql = "SELECT c.*, 
            s.name as sender_name, s.phone as sender_phone, s.address as sender_address,
            r.name as receiver_name, r.phone as receiver_phone, r.address as receiver_address
            FROM consignments c
            LEFT JOIN senders s ON c.sender_id = s.id
            LEFT JOIN receivers r ON c.receiver_id = r.id
            WHERE c.agent_id = ?";

    $params = [$agent_id];
    $types = "i";

    if (!empty($search)) {
        $sql .= " AND (c.tracking_number LIKE ? OR s.name LIKE ? OR r.name LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "sss";
    }

    if (!empty($status)) {
        $sql .= " AND c.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if (!empty($date_from)) {
        $sql .= " AND DATE(c.created_at) >= ?";
        $params[] = $date_from;
        $types .= "s";
    }

    if (!empty($date_to)) {
        $sql .= " AND DATE(c.created_at) <= ?";
        $params[] = $date_to;
        $types .= "s";
    }

    $sql .= " ORDER BY c.created_at DESC";

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $consignments = $result->fetch_all(MYSQLI_ASSOC);

    // If we have consignments, get sender and receiver details
    if (!empty($consignments)) {
        foreach ($consignments as &$consignment) {
            // Get sender details
            if (!empty($consignment['sender_id'])) {
                $sender_sql = "SELECT name, phone, address FROM senders WHERE id = ?";
                $sender_stmt = $conn->prepare($sender_sql);
                $sender_stmt->bind_param("i", $consignment['sender_id']);
                $sender_stmt->execute();
                $sender_result = $sender_stmt->get_result();
                if ($sender = $sender_result->fetch_assoc()) {
                    $consignment['sender_name'] = $sender['name'];
                    $consignment['sender_phone'] = $sender['phone'];
                    $consignment['sender_address'] = $sender['address'];
                }
            }

            // Get receiver details
            if (!empty($consignment['receiver_id'])) {
                $receiver_sql = "SELECT name, phone, address FROM receivers WHERE id = ?";
                $receiver_stmt = $conn->prepare($receiver_sql);
                $receiver_stmt->bind_param("i", $consignment['receiver_id']);
                $receiver_stmt->execute();
                $receiver_result = $receiver_stmt->get_result();
                if ($receiver = $receiver_result->fetch_assoc()) {
                    $consignment['receiver_name'] = $receiver['name'];
                    $consignment['receiver_phone'] = $receiver['phone'];
                    $consignment['receiver_address'] = $receiver['address'];
                }
            }
        }
    }

} catch (Exception $e) {
    // Log the error
    error_log("Error in consignments.php: " . $e->getMessage());
    $error_message = "Error: " . $e->getMessage();
    $consignments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consignments - Agent Dashboard</title>
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
            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <!-- Search and Filter -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Tracking #, Sender, Receiver">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Status</option>
                                <option value="On Transit" <?php echo $status === 'On Transit' ? 'selected' : ''; ?>>On Transit</option>
                                <option value="Out for Delivery" <?php echo $status === 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                <option value="On Hold" <?php echo $status === 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                                <option value="Delivered" <?php echo $status === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="Clearance Pending" <?php echo $status === 'Clearance Pending' ? 'selected' : ''; ?>>Clearance Pending</option>
                            </select>
                        </div>
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($date_from); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($date_to); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Consignments List -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Consignments
                        </h3>
                        <a href="create-consignment.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i> New Consignment
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tracking Number
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sender
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Receiver
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Created At
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($consignments)): ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No consignments found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($consignments as $consignment): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($consignment['tracking_number']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($consignment['sender_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($consignment['receiver_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo date('M d, Y H:i', strtotime($consignment['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex space-x-2">
                                                <a href="view-consignment.php?id=<?php echo $consignment['id']; ?>" class="text-blue-600 hover:text-blue-900" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-consignment.php?id=<?php echo $consignment['id']; ?>" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="update-status.php?id=<?php echo $consignment['id']; ?>" class="text-green-600 hover:text-green-900" title="Update Status">
                                                    <i class="fas fa-truck"></i>
                                                </a>
                                                <a href="print.php?id=<?php echo $consignment['id']; ?>" class="text-gray-600 hover:text-gray-900" target="_blank" title="Print">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
<?php
session_start();
require_once '../../include/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Get tracking number from URL
$tracking_number = isset($_GET['tracking_number']) ? sanitize_input($_GET['tracking_number']) : '';
$consignment = null;
$tracking_history = [];
$error = null;

if (!empty($tracking_number)) {
    try {
        // Debug: Print the tracking number
        error_log("Searching for tracking number: " . $tracking_number);

        // Get consignment details with latest status
        $sql = "SELECT c.*, 
                u.name as agent_name,
                s.name as sender_name, s.phone as sender_phone, s.address as pickup_location,
                r.name as receiver_name, r.phone as receiver_phone, r.address as drop_location,
                th.status as current_status,
                th.location as current_location,
                th.created_at as status_date,
                th.notes as latest_notes,
                c.package_type,
                c.amount_paid,
                c.payment_method,
                c.payment_status,
                c.paid_by
                FROM consignments c 
                LEFT JOIN users u ON c.agent_id = u.id 
                LEFT JOIN senders s ON c.sender_id = s.id
                LEFT JOIN receivers r ON c.receiver_id = r.id
                LEFT JOIN (
                    SELECT consignment_id, status, location, created_at, notes
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
            error_log("Consignment data: " . print_r($consignment, true));
            
            // Get tracking history
            $history_sql = "SELECT th.* 
                           FROM tracking_history th
                           WHERE th.consignment_id = ? 
                           ORDER BY th.created_at DESC";
            
            $history_stmt = $conn->prepare($history_sql);
            if (!$history_stmt) {
                throw new Exception("Error preparing history query: " . $conn->error);
            }
            
            $history_stmt->bind_param("i", $consignment['id']);
            $history_stmt->execute();
            $history_result = $history_stmt->get_result();
            
            while ($row = $history_result->fetch_assoc()) {
                $tracking_history[] = $row;
            }
            error_log("Tracking history: " . print_r($tracking_history, true));
        } else {
            $error = "No consignment found with tracking number: " . htmlspecialchars($tracking_number);
        }
    } catch (Exception $e) {
        error_log("Error in tracking-history.php: " . $e->getMessage());
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking History - FLEXCEE Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .timeline-item {
            position: relative;
            padding-left: 2rem;
            border-left: 2px solid #e2e8f0;
        }
        .timeline-item:last-child {
            border-left: 2px solid transparent;
        }
        .timeline-dot {
            position: absolute;
            left: -0.5rem;
            top: 0.5rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background-color: #4299e1;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
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
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($consignment): ?>
                    <!-- Consignment Details -->
                    <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-gray-800">Consignment Details</h2>
                            <span class="status-badge <?php
                                $status = $consignment['current_status'] ?? 'Pending';
                                echo match($status) {
                                    'Delivered' => 'bg-green-100 text-green-800',
                                    'In Transit' => 'bg-blue-100 text-blue-800',
                                    'Out for Delivery' => 'bg-yellow-100 text-yellow-800',
                                    'On Hold' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Sender Details -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-700 mb-3">Sender Information</h3>
                                <div class="space-y-2">
                                    <p><span class="font-medium">Name:</span> <?php echo htmlspecialchars($consignment['sender_name'] ?? 'N/A'); ?></p>
                                    <p><span class="font-medium">Phone:</span> <?php echo htmlspecialchars($consignment['sender_phone'] ?? 'N/A'); ?></p>
                                    <p><span class="font-medium">Pickup Location:</span> <?php echo htmlspecialchars($consignment['pickup_location'] ?? 'N/A'); ?></p>
                                </div>
                            </div>

                            <!-- Receiver Details -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-700 mb-3">Receiver Information</h3>
                                <div class="space-y-2">
                                    <p><span class="font-medium">Name:</span> <?php echo htmlspecialchars($consignment['receiver_name'] ?? 'N/A'); ?></p>
                                    <p><span class="font-medium">Phone:</span> <?php echo htmlspecialchars($consignment['receiver_phone'] ?? 'N/A'); ?></p>
                                    <p><span class="font-medium">Drop Location:</span> <?php echo htmlspecialchars($consignment['drop_location'] ?? 'N/A'); ?></p>
                                </div>
                            </div>

                            <!-- Consignment Details -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-700 mb-3">Consignment Information</h3>
                                <div class="space-y-2">
                                    <p><span class="font-medium">Tracking Number:</span> <?php echo htmlspecialchars($consignment['tracking_number']); ?></p>
                                    <p><span class="font-medium">Current Location:</span> <?php echo htmlspecialchars($consignment['current_location'] ?? 'N/A'); ?></p>
                                    <p><span class="font-medium">Assigned Agent:</span> <?php echo htmlspecialchars($consignment['agent_name'] ?? 'Unassigned'); ?></p>
                                    <p><span class="font-medium">Created Date:</span> <?php echo date('M d, Y', strtotime($consignment['created_at'])); ?></p>
                                    <p>
                                        <a href="#payment-section" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-money-bill mr-1"></i> View/Update Payment Details
                                        </a>
                                    </p>
                                </div>
                            </div>

                            <!-- Package Details -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-700 mb-3">Package Information</h3>
                                <div class="space-y-2">
                                    <p><span class="font-medium">Weight:</span> <?php echo htmlspecialchars($consignment['weight'] ?? '0.00'); ?> kg</p>
                                    <p><span class="font-medium">Dimensions:</span> <?php echo htmlspecialchars($consignment['dimensions'] ?? 'N/A'); ?></p>
                                    <p><span class="font-medium">Package Type:</span> <?php echo htmlspecialchars($consignment['package_type'] ?? 'N/A'); ?></p>
                                    <p><span class="font-medium">Special Instructions:</span> <?php echo htmlspecialchars($consignment['special_instructions'] ?? 'N/A'); ?></p>
                                </div>
                            </div>

                            <!-- Payment Information -->
                            <div id="payment-section" class="mt-4 p-4 bg-white rounded-lg shadow">
                                <h4 class="font-semibold text-gray-700 mb-2">Payment Information</h4>
                                <form action="update-payment.php" method="POST" class="space-y-3">
                                    <input type="hidden" name="consignment_id" value="<?php echo $consignment['id']; ?>">
                                    <input type="hidden" name="tracking_number" value="<?php echo $consignment['tracking_number']; ?>">
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                                            <select name="payment_status" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                                <option value="pending" <?php echo ($consignment['payment_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="paid" <?php echo ($consignment['payment_status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                            <select name="payment_method" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">Select Method</option>
                                                <option value="cash" <?php echo ($consignment['payment_method'] == 'cash') ? 'selected' : ''; ?>>Cash</option>
                                                <option value="bank_transfer" <?php echo ($consignment['payment_method'] == 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                                                <option value="card" <?php echo ($consignment['payment_method'] == 'card') ? 'selected' : ''; ?>>Card</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Amount Paid (₦)</label>
                                            <input type="number" name="amount_paid" step="0.01" value="<?php echo $consignment['amount_paid'] ?? 0; ?>" 
                                                   class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Paid By</label>
                                            <select name="paid_by" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">Select who will pay</option>
                                                <option value="sender" <?php echo ($consignment['paid_by'] == 'sender') ? 'selected' : ''; ?>>Sender</option>
                                                <option value="receiver" <?php echo ($consignment['paid_by'] == 'receiver') ? 'selected' : ''; ?>>Receiver</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Payment Status Display -->
                                    <div class="mt-4 p-3 rounded-lg <?php echo ($consignment['payment_status'] == 'paid') ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="font-medium">Current Status:</span>
                                                <?php if ($consignment['payment_status'] == 'paid'): ?>
                                                    <span class="font-bold">PAID</span>
                                                    <?php if ($consignment['amount_paid'] > 0): ?>
                                                        <span class="ml-2">(₦<?php echo number_format($consignment['amount_paid'], 2); ?>)</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="font-bold">PENDING</span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <?php if ($consignment['payment_method']): ?>
                                                    <span class="text-sm">Method: <?php echo ucfirst($consignment['payment_method']); ?></span>
                                                <?php endif; ?>
                                                <?php if ($consignment['paid_by']): ?>
                                                    <span class="text-sm ml-2">Paid by: <?php echo ucfirst($consignment['paid_by']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                            Update Payment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Tracking History -->
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">Tracking History</h2>
                        <div class="space-y-6">
                            <?php if (empty($tracking_history)): ?>
                                <p class="text-gray-500 text-center">No tracking updates available yet.</p>
                            <?php else: ?>
                                <?php foreach ($tracking_history as $history): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-dot"></div>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($history['status']); ?></h4>
                                                    <?php if (!empty($history['notes'])): ?>
                                                        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($history['notes']); ?></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($history['location'])): ?>
                                                        <p class="text-gray-600 mt-1">
                                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                                            <?php echo htmlspecialchars($history['location']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm text-gray-500"><?php echo date('M d, Y H:i', strtotime($history['created_at'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 
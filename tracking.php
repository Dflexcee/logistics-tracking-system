<?php
require_once 'include/db.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$tracking_number = sanitize_input($_GET['tracking_number'] ?? '');
$consignment = null;
$tracking_updates = [];
$error = null;

if (!empty($tracking_number)) {
    try {
        // Get consignment details
        $sql = "SELECT c.*, u.name as agent_name 
                FROM consignments c 
                LEFT JOIN users u ON c.agent_id = u.id 
                WHERE c.tracking_number = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Database error");
        }
        
        $stmt->bind_param("s", $tracking_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = 'Tracking number not found';
        } else {
            $consignment = $result->fetch_assoc();
            
            // Get tracking updates
            $updates_sql = "SELECT status, comment, timestamp 
                           FROM tracking_updates 
                           WHERE consignment_id = ? 
                           ORDER BY timestamp DESC";
            $updates_stmt = $conn->prepare($updates_sql);
            
            if (!$updates_stmt) {
                throw new Exception("Database error");
            }
            
            $updates_stmt->bind_param("i", $consignment['id']);
            $updates_stmt->execute();
            $updates_result = $updates_stmt->get_result();
            
            while ($row = $updates_result->fetch_assoc()) {
                $tracking_updates[] = $row;
            }
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Shipment - FLEXCEE Logistics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-center text-gray-800 dark:text-white">Track Your Shipment</h1>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Search Form -->
        <div class="max-w-2xl mx-auto mb-8">
            <form action="" method="GET" class="flex gap-4">
                <input type="text" name="tracking_number" value="<?php echo htmlspecialchars($tracking_number); ?>" 
                       placeholder="Enter your tracking number" required
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <button type="submit" 
                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-blue-800 transition">
                    Track
                </button>
            </form>
        </div>

        <?php if ($error): ?>
        <!-- Error Message -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($consignment): ?>
        <!-- Tracking Results -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <!-- Status Banner -->
                <div class="bg-primary text-white p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold">Tracking Number: <?php echo htmlspecialchars($consignment['tracking_number']); ?></h2>
                            <p class="text-sm">Created on <?php echo date('M d, Y', strtotime($consignment['created_at'])); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm">Current Status</p>
                            <p class="text-lg font-semibold"><?php echo htmlspecialchars($consignment['status']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Sender Information -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4 text-primary">Sender Information</h3>
                            <div class="space-y-2">
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Name:</span> <?php echo htmlspecialchars($consignment['sender_name']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Phone:</span> <?php echo htmlspecialchars($consignment['sender_phone']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Pickup Location:</span> <?php echo htmlspecialchars($consignment['pickup_location']); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Receiver Information -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4 text-primary">Receiver Information</h3>
                            <div class="space-y-2">
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Name:</span> <?php echo htmlspecialchars($consignment['receiver_name']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Phone:</span> <?php echo htmlspecialchars($consignment['receiver_phone']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Drop Location:</span> <?php echo htmlspecialchars($consignment['drop_location']); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Item Details -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4 text-primary">Item Details</h3>
                            <div class="space-y-2">
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Description:</span> <?php echo htmlspecialchars($consignment['item_description']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Weight:</span> <?php echo htmlspecialchars($consignment['weight_kg']); ?> kg
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Amount Paid:</span> $<?php echo number_format($consignment['amount_paid'], 2); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Dispatch Information -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4 text-primary">Dispatch Information</h3>
                            <div class="space-y-2">
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Method:</span> <?php echo htmlspecialchars($consignment['dispatch_method']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">From Country:</span> <?php echo htmlspecialchars($consignment['sent_from_country']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">From State:</span> <?php echo htmlspecialchars($consignment['sent_from_state']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Terminal:</span> <?php echo htmlspecialchars($consignment['sent_from_terminal']); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Print Button -->
                    <div class="flex justify-end mt-8 mb-6 no-print">
                        <button onclick="window.print()" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-800 transition">
                            <i class="fas fa-print mr-2"></i> Print Tracking Info
                        </button>
                    </div>

                    <!-- Tracking Timeline -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                        <h3 class="text-xl font-bold mb-6 text-gray-800 dark:text-white">Tracking History</h3>
                        <div class="space-y-6">
                            <?php if (empty($tracking_updates)): ?>
                            <p class="text-gray-500 dark:text-gray-400">No tracking updates available yet.</p>
                            <?php else: ?>
                            <?php foreach ($tracking_updates as $update): ?>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center">
                                        <i class="fas fa-circle text-white text-xs"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <h4 class="text-lg font-medium text-gray-800 dark:text-white">
                                            <?php echo htmlspecialchars($update['status']); ?>
                                        </h4>
                                        <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo date('M d, Y h:i A', strtotime($update['timestamp'])); ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($update['comment'])): ?>
                                    <p class="mt-1 text-gray-600 dark:text-gray-300">
                                        <?php echo htmlspecialchars($update['comment']); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- WhatsApp Support -->
        <div class="max-w-4xl mx-auto mt-8 text-center no-print">
            <a href="https://wa.me/2348065416156" target="_blank" class="text-blue-600 underline hover:text-blue-800">
                Contact Support on WhatsApp
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 shadow-lg mt-8 no-print">
        <div class="container mx-auto px-4 py-6">
            <p class="text-center text-gray-600 dark:text-gray-400">
                &copy; <?php echo date('Y'); ?> FLEXCEE Logistics. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html> 
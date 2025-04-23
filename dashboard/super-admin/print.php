<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Get tracking number from URL
$tracking_number = sanitize_input($_GET['tracking_id'] ?? '');

if (empty($tracking_number)) {
    die('Invalid tracking number');
}

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
        die('Consignment not found');
    }
    
    $consignment = $result->fetch_assoc();
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consignment Label - <?php echo htmlspecialchars($tracking_number); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                page-break-after: always;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Print Button -->
    <div class="no-print fixed top-4 right-4">
        <button onclick="window.print()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-800">
            <i class="fas fa-print mr-2"></i> Print Label
        </button>
    </div>

    <!-- Consignment Label -->
    <div class="max-w-4xl mx-auto bg-white p-8 my-8 shadow-lg">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-primary">FLEXCEE Logistics</h1>
            <p class="text-gray-600">Consignment Label</p>
        </div>

        <!-- Tracking Information -->
        <div class="border-b border-gray-200 pb-4 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Tracking Number</p>
                    <p class="text-xl font-bold text-primary"><?php echo htmlspecialchars($consignment['tracking_number']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="text-lg font-semibold <?php echo $consignment['status'] === 'Delivered' ? 'text-green-600' : 'text-blue-600'; ?>">
                        <?php echo htmlspecialchars($consignment['status']); ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Date</p>
                    <p class="text-lg"><?php echo date('M d, Y', strtotime($consignment['created_at'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Sender and Receiver Information -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <!-- Sender -->
            <div>
                <h2 class="text-lg font-semibold mb-4 text-primary">Sender Information</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Name:</span> <?php echo htmlspecialchars($consignment['sender_name']); ?></p>
                    <p><span class="font-medium">Phone:</span> <?php echo htmlspecialchars($consignment['sender_phone']); ?></p>
                    <p><span class="font-medium">Address:</span> <?php echo htmlspecialchars($consignment['pickup_location']); ?></p>
                </div>
            </div>

            <!-- Receiver -->
            <div>
                <h2 class="text-lg font-semibold mb-4 text-primary">Receiver Information</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Name:</span> <?php echo htmlspecialchars($consignment['receiver_name']); ?></p>
                    <p><span class="font-medium">Phone:</span> <?php echo htmlspecialchars($consignment['receiver_phone']); ?></p>
                    <p><span class="font-medium">Address:</span> <?php echo htmlspecialchars($consignment['drop_location']); ?></p>
                </div>
            </div>
        </div>

        <!-- Agent Information -->
        <div class="border-t border-gray-200 pt-4">
            <h2 class="text-lg font-semibold mb-4 text-primary">Assigned Agent</h2>
            <p><span class="font-medium">Name:</span> <?php echo htmlspecialchars($consignment['agent_name']); ?></p>
        </div>

        <!-- Barcode Section -->
        <div class="mt-8 text-center">
            <div class="inline-block p-4 border-2 border-dashed border-gray-300">
                <p class="text-sm text-gray-600 mb-2">Scan to Track</p>
                <div class="text-2xl font-mono"><?php echo htmlspecialchars($consignment['tracking_number']); ?></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-600">
            <p>Thank you for choosing FLEXCEE Logistics</p>
            <p>For tracking updates, visit www.flexcee.com/track</p>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Uncomment the line below to enable auto-printing
            // window.print();
        };
    </script>
</body>
</html> 
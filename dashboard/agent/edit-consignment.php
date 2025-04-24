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

        // Update sender information
        $sender_sql = "UPDATE senders SET 
            name = ?, 
            phone = ?, 
            address = ? 
            WHERE id = ?";
        $sender_stmt = $conn->prepare($sender_sql);
        if ($sender_stmt === false) {
            throw new Exception("Error preparing sender statement: " . $conn->error);
        }
        
        if (!$sender_stmt->bind_param("sssi", 
            $_POST['sender_name'],
            $_POST['sender_phone'],
            $_POST['sender_address'],
            $consignment['sender_id']
        )) {
            throw new Exception("Error binding sender parameters: " . $sender_stmt->error);
        }
        
        if (!$sender_stmt->execute()) {
            throw new Exception("Error executing sender update: " . $sender_stmt->error);
        }

        // Update receiver information
        $receiver_sql = "UPDATE receivers SET 
            name = ?, 
            phone = ?, 
            address = ? 
            WHERE id = ?";
        $receiver_stmt = $conn->prepare($receiver_sql);
        if ($receiver_stmt === false) {
            throw new Exception("Error preparing receiver statement: " . $conn->error);
        }
        
        if (!$receiver_stmt->bind_param("sssi", 
            $_POST['receiver_name'],
            $_POST['receiver_phone'],
            $_POST['receiver_address'],
            $consignment['receiver_id']
        )) {
            throw new Exception("Error binding receiver parameters: " . $receiver_stmt->error);
        }
        
        if (!$receiver_stmt->execute()) {
            throw new Exception("Error executing receiver update: " . $receiver_stmt->error);
        }

        // Update consignment information
        $consignment_sql = "UPDATE consignments SET 
            weight = ?,
            description = ?,
            dimensions = ?,
            package_type = ?,
            special_instructions = ?,
            amount_paid = ?,
            payment_method = ?,
            payment_status = ?,
            paid_by = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND agent_id = ?";
            
        $consignment_stmt = $conn->prepare($consignment_sql);
        if ($consignment_stmt === false) {
            throw new Exception("Error preparing consignment statement: " . $conn->error);
        }
        
        if (!$consignment_stmt->bind_param("dssssdsssii", 
            $_POST['weight'],
            $_POST['description'],
            $_POST['dimensions'],
            $_POST['package_type'],
            $_POST['special_instructions'],
            $_POST['amount_paid'],
            $_POST['payment_method'],
            $_POST['payment_status'],
            $_POST['paid_by'],
            $consignment_id,
            $agent_id
        )) {
            throw new Exception("Error binding consignment parameters: " . $consignment_stmt->error);
        }
        
        if (!$consignment_stmt->execute()) {
            throw new Exception("Error executing consignment update: " . $consignment_stmt->error);
        }

        // Commit transaction
        $conn->commit();

        // Redirect to view page
        header("Location: view-consignment.php?id=" . $consignment_id);
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error = "Error updating consignment: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Consignment - Agent Dashboard</title>
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
                            Edit Consignment
                        </h3>
                        <a href="view-consignment.php?id=<?php echo $consignment_id; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700">
                            <i class="fas fa-arrow-left mr-2"></i> Back to View
                        </a>
                    </div>

                    <form method="POST" class="space-y-6">
                        <!-- Sender Information -->
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-700">Sender Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="sender_name" class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" name="sender_name" id="sender_name" required
                                        value="<?php echo htmlspecialchars($consignment['sender_name']); ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="sender_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input type="tel" name="sender_phone" id="sender_phone" required
                                        value="<?php echo htmlspecialchars($consignment['sender_phone']); ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="sender_address" class="block text-sm font-medium text-gray-700">Address</label>
                                    <input type="text" name="sender_address" id="sender_address" required
                                        value="<?php echo htmlspecialchars($consignment['sender_address']); ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Receiver Information -->
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-700">Receiver Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="receiver_name" class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" name="receiver_name" id="receiver_name" required
                                        value="<?php echo htmlspecialchars($consignment['receiver_name']); ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="receiver_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input type="tel" name="receiver_phone" id="receiver_phone" required
                                        value="<?php echo htmlspecialchars($consignment['receiver_phone']); ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="receiver_address" class="block text-sm font-medium text-gray-700">Address</label>
                                    <input type="text" name="receiver_address" id="receiver_address" required
                                        value="<?php echo htmlspecialchars($consignment['receiver_address']); ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Package Information -->
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-700">Package Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="weight" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                                    <input type="number" step="0.01" name="weight" id="weight" required
                                        value="<?php echo htmlspecialchars($consignment['weight']); ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="dimensions" class="block text-sm font-medium text-gray-700">Dimensions</label>
                                    <input type="text" name="dimensions" id="dimensions" required
                                        value="<?php echo htmlspecialchars($consignment['dimensions']); ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($consignment['description'] ?? ''); ?></textarea>
                                </div>
                                <div>
                                    <label for="package_type" class="block text-sm font-medium text-gray-700">Package Type</label>
                                    <select name="package_type" id="package_type" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="document" <?php echo $consignment['package_type'] === 'document' ? 'selected' : ''; ?>>Document</option>
                                        <option value="parcel" <?php echo $consignment['package_type'] === 'parcel' ? 'selected' : ''; ?>>Parcel</option>
                                        <option value="package" <?php echo $consignment['package_type'] === 'package' ? 'selected' : ''; ?>>Package</option>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label for="special_instructions" class="block text-sm font-medium text-gray-700">Special Instructions</label>
                                    <textarea name="special_instructions" id="special_instructions" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($consignment['special_instructions'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-700">Payment Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="amount_paid" class="block text-sm font-medium text-gray-700">Amount</label>
                                    <input type="number" step="0.01" name="amount_paid" id="amount_paid" required
                                        value="<?php echo htmlspecialchars($consignment['amount_paid']); ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                                    <select name="payment_method" id="payment_method"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Payment Method</option>
                                        <option value="cash" <?php echo $consignment['payment_method'] === 'cash' ? 'selected' : ''; ?>>Cash</option>
                                        <option value="bank_transfer" <?php echo $consignment['payment_method'] === 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                                        <option value="card" <?php echo $consignment['payment_method'] === 'card' ? 'selected' : ''; ?>>Card</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="payment_status" class="block text-sm font-medium text-gray-700">Payment Status</label>
                                    <select name="payment_status" id="payment_status" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="pending" <?php echo $consignment['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="paid" <?php echo $consignment['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="paid_by" class="block text-sm font-medium text-gray-700">Paid By</label>
                                    <select name="paid_by" id="paid_by"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Payer</option>
                                        <option value="sender" <?php echo $consignment['paid_by'] === 'sender' ? 'selected' : ''; ?>>Sender</option>
                                        <option value="receiver" <?php echo $consignment['paid_by'] === 'receiver' ? 'selected' : ''; ?>>Receiver</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
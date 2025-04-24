<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is an agent
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
    header('Location: ../../login.php');
    exit();
}

$agent_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Insert sender information
        $sender_sql = "INSERT INTO senders (name, phone, address) VALUES (?, ?, ?)";
        $sender_stmt = $conn->prepare($sender_sql);
        $sender_stmt->bind_param("sss", $sender_name, $sender_phone, $pickup_location);
        $sender_stmt->execute();
        $sender_id = $conn->insert_id;

        // Insert receiver information
        $receiver_sql = "INSERT INTO receivers (name, phone, address) VALUES (?, ?, ?)";
        $receiver_stmt = $conn->prepare($receiver_sql);
        $receiver_stmt->bind_param("sss", $receiver_name, $receiver_phone, $drop_location);
        $receiver_stmt->execute();
        $receiver_id = $conn->insert_id;

        // Generate tracking number
        $tracking_number = generateTrackingNumber();

        // Insert consignment information
        $consignment_sql = "INSERT INTO consignments (tracking_number, sender_id, receiver_id, agent_id, weight, dimensions, package_type, special_instructions, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $consignment_stmt = $conn->prepare($consignment_sql);
        $consignment_stmt->bind_param("siiidsss", $tracking_number, $sender_id, $receiver_id, $agent_id, $weight, $dimensions, $package_type, $special_instructions);
        $consignment_stmt->execute();
        $consignment_id = $conn->insert_id;

        // Insert initial tracking history
        $tracking_sql = "INSERT INTO tracking_history (consignment_id, status, location, notes) VALUES (?, 'Pending', ?, 'Consignment created')";
        $tracking_stmt = $conn->prepare($tracking_sql);
        $tracking_stmt->bind_param("is", $consignment_id, $pickup_location);
        $tracking_stmt->execute();

        // Commit transaction
        $conn->commit();

        // Redirect to consignments list page
        header("Location: consignments.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error = "Error creating consignment: " . $e->getMessage();
    }
}

function generateTrackingNumber() {
    $prefix = 'TRK';
    $random = str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
    return $prefix . $random;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Consignment - Agent Dashboard</title>
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
                    <a href="edit-consignment.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-edit mr-2"></i> Edit Consignment
                    </a>
                    <a href="update-status.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-truck mr-2"></i> Update Status
                    </a>
                    <a href="print.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-print mr-2"></i> Print
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
                            Create New Consignment
                        </h3>
                        <a href="consignments.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Consignments
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
                                        value="<?php echo isset($_POST['sender_name']) ? htmlspecialchars($_POST['sender_name']) : ''; ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="sender_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input type="tel" name="sender_phone" id="sender_phone" required
                                        value="<?php echo isset($_POST['sender_phone']) ? htmlspecialchars($_POST['sender_phone']) : ''; ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="pickup_location" class="block text-sm font-medium text-gray-700">Pickup Location</label>
                                    <input type="text" name="pickup_location" id="pickup_location" required
                                        value="<?php echo isset($_POST['pickup_location']) ? htmlspecialchars($_POST['pickup_location']) : ''; ?>"
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
                                        value="<?php echo isset($_POST['receiver_name']) ? htmlspecialchars($_POST['receiver_name']) : ''; ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="receiver_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input type="tel" name="receiver_phone" id="receiver_phone" required
                                        value="<?php echo isset($_POST['receiver_phone']) ? htmlspecialchars($_POST['receiver_phone']) : ''; ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="drop_location" class="block text-sm font-medium text-gray-700">Drop Location</label>
                                    <input type="text" name="drop_location" id="drop_location" required
                                        value="<?php echo isset($_POST['drop_location']) ? htmlspecialchars($_POST['drop_location']) : ''; ?>"
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
                                        value="<?php echo isset($_POST['weight']) ? htmlspecialchars($_POST['weight']) : ''; ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="dimensions" class="block text-sm font-medium text-gray-700">Dimensions</label>
                                    <input type="text" name="dimensions" id="dimensions" required
                                        value="<?php echo isset($_POST['dimensions']) ? htmlspecialchars($_POST['dimensions']) : ''; ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="package_type" class="block text-sm font-medium text-gray-700">Package Type</label>
                                    <select name="package_type" id="package_type" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="Standard">Standard</option>
                                        <option value="Express">Express</option>
                                        <option value="Fragile">Fragile</option>
                                        <option value="Bulk">Bulk</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="special_instructions" class="block text-sm font-medium text-gray-700">Special Instructions</label>
                                    <textarea name="special_instructions" id="special_instructions" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo isset($_POST['special_instructions']) ? htmlspecialchars($_POST['special_instructions']) : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i> Create Consignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
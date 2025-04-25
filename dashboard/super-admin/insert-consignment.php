<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: ../../login.php");
    exit();
}

$is_superadmin = ($_SESSION['user_role'] === 'superadmin');
$is_agent = ($_SESSION['user_role'] === 'agent');

require_once '../../include/db.php';

// Check if user is logged in and is a super admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $sender_name = $_POST['sender_name'];
        $sender_phone = $_POST['sender_phone'];
        $pickup_location = $_POST['pickup_location'];
        $receiver_name = $_POST['receiver_name'];
        $receiver_phone = $_POST['receiver_phone'];
        $drop_location = $_POST['drop_location'];
        $item_description = $_POST['item_description'];
        $weight_kg = $_POST['weight_kg'];
        $package_type = $_POST['package_type'];
        $amount_paid = $_POST['amount_paid'] ?? 0;
        $paid_by = $_POST['paid_by'];
        $payment_method = $_POST['payment_method'];
        $payment_status = $_POST['payment_status'];
        $agent_id = $_POST['agent_id'] ?? null;
        $send_date = $_POST['send_date'];
        $pickup_date = $_POST['pickup_date'];

        // Generate tracking number
        $tracking_number = 'TRK' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // Insert sender information
        $sender_sql = "INSERT INTO senders (name, phone, address) VALUES (?, ?, ?)";
        $sender_stmt = $conn->prepare($sender_sql);
        if ($sender_stmt === false) {
            throw new Exception("Sender preparation failed: " . $conn->error);
        }
        $sender_stmt->bind_param("sss", $sender_name, $sender_phone, $pickup_location);
        if (!$sender_stmt->execute()) {
            throw new Exception("Sender execution failed: " . $sender_stmt->error);
        }
        $sender_id = $conn->insert_id;
        $sender_stmt->close();

        // Insert receiver information
        $receiver_sql = "INSERT INTO receivers (name, phone, address) VALUES (?, ?, ?)";
        $receiver_stmt = $conn->prepare($receiver_sql);
        if ($receiver_stmt === false) {
            throw new Exception("Receiver preparation failed: " . $conn->error);
        }
        $receiver_stmt->bind_param("sss", $receiver_name, $receiver_phone, $drop_location);
        if (!$receiver_stmt->execute()) {
            throw new Exception("Receiver execution failed: " . $receiver_stmt->error);
        }
        $receiver_id = $conn->insert_id;
        $receiver_stmt->close();

        // Insert consignment
        $sql = "INSERT INTO consignments (
                    tracking_number, sender_id, receiver_id, agent_id,
                    weight, dimensions, package_type, special_instructions,
                    status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
        
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Consignment preparation failed: " . $conn->error);
        }
        $stmt->bind_param("siiidsss", 
            $tracking_number, $sender_id, $receiver_id, $agent_id,
            $weight_kg, $item_description, $package_type, $special_instructions
        );

        if ($stmt->execute()) {
        $consignment_id = $conn->insert_id;

            // Add initial tracking status
            $status = "Consignment Created";
            $notes = "Consignment created with tracking number: " . $tracking_number;
            
            $history_sql = "INSERT INTO tracking_history (consignment_id, status, notes, created_at) 
                          VALUES (?, ?, ?, NOW())";
        $history_stmt = $conn->prepare($history_sql);
            $history_stmt->bind_param("iss", $consignment_id, $status, $notes);
            $history_stmt->execute();

            // Add payment status to tracking history if payment information is provided
            if ($amount_paid > 0) {
                $payment_notes = "Amount: ₦" . number_format($amount_paid, 2);
                
                $payment_history_sql = "INSERT INTO tracking_history (consignment_id, status, notes, created_at) 
                                      VALUES (?, 'Payment Information', ?, NOW())";
                $payment_history_stmt = $conn->prepare($payment_history_sql);
                $payment_history_stmt->bind_param("is", $consignment_id, $payment_notes);
                $payment_history_stmt->execute();
            }

            $_SESSION['success'] = "Consignment created successfully with tracking number: " . $tracking_number;
            header("Location: consignments.php");
            exit();
        } else {
            throw new Exception("Error creating consignment: " . $conn->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: create-consignment.php");
        exit();
    }
}

// Get list of agents for the dropdown
$agents_sql = "SELECT id, name FROM users WHERE role = 'agent' AND status = 'active'";
$agents_result = $conn->query($agents_sql);
$agents = $agents_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Consignment - Super Admin Dashboard</title>
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
                    <a href="agents.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-users mr-2"></i> Agents
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
                        <!-- Agent Selection -->
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-700">Assign to Agent</h4>
                            <div>
                                <label for="agent_id" class="block text-sm font-medium text-gray-700">Select Agent</label>
                                <select name="agent_id" id="agent_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select an agent</option>
                                    <?php foreach ($agents as $agent): ?>
                                        <option value="<?php echo $agent['id']; ?>" <?php echo (isset($_POST['agent_id']) && $_POST['agent_id'] == $agent['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($agent['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

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
                                    <label for="weight_kg" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                                    <input type="number" step="0.01" name="weight_kg" id="weight_kg" required
                                        value="<?php echo isset($_POST['weight_kg']) ? htmlspecialchars($_POST['weight_kg']) : ''; ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="item_description" class="block text-sm font-medium text-gray-700">Item Description</label>
                                    <input type="text" name="item_description" id="item_description" required
                                        value="<?php echo isset($_POST['item_description']) ? htmlspecialchars($_POST['item_description']) : ''; ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="send_date" class="block text-sm font-medium text-gray-700">Send Date</label>
                                    <input type="date" name="send_date" id="send_date" required
                                        value="<?php echo isset($_POST['send_date']) ? htmlspecialchars($_POST['send_date']) : ''; ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="pickup_date" class="block text-sm font-medium text-gray-700">Pickup Date</label>
                                    <input type="date" name="pickup_date" id="pickup_date" required
                                        value="<?php echo isset($_POST['pickup_date']) ? htmlspecialchars($_POST['pickup_date']) : ''; ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
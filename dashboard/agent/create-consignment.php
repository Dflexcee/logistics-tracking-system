<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is agent
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'agent') {
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

// Get error message if any
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Consignment - FLEXCEE Logistics</title>
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
        <div class="flex">
            <!-- Sidebar -->
            <div class="w-64 bg-white shadow-lg rounded-lg p-4 mr-8">
                <nav class="space-y-2">
                    <a href="index.php" class="block px-4 py-2 text-gray-600 hover:bg-primary hover:text-white rounded-lg">
                        <i class="fas fa-home mr-2"></i> Dashboard
                    </a>
                    <a href="consignments.php" class="block px-4 py-2 bg-primary text-white rounded-lg">
                        <i class="fas fa-box mr-2"></i> My Consignments
                    </a>
                    <a href="profile.php" class="block px-4 py-2 text-gray-600 hover:bg-primary hover:text-white rounded-lg">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                </nav>
            </div>

            <!-- Create Consignment Form -->
            <div class="flex-1">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-primary">Create New Consignment</h1>
                        <a href="consignments.php" class="text-primary hover:text-blue-800">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Consignments
                        </a>
                    </div>

                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="insert-agent-consignment.php" method="POST" class="space-y-6">
                        <input type="hidden" name="agent_id" value="<?php echo $_SESSION['user_id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Sender Information -->
                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <h3 class="text-lg font-semibold mb-4 text-gray-800">Sender Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sender Name</label>
                                        <input type="text" name="sender_name" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sender Phone</label>
                                        <div class="flex">
                                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                                +234
                                            </span>
                                            <input type="tel" name="sender_phone" required
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-r-md focus:outline-none focus:ring-2 focus:ring-primary"
                                                placeholder="Enter phone number (any format)">
                                        </div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Location</label>
                                        <input type="text" name="pickup_location" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                </div>
                            </div>

                            <!-- Receiver Information -->
                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <h3 class="text-lg font-semibold mb-4 text-gray-800">Receiver Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Receiver Name</label>
                                        <input type="text" name="receiver_name" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Receiver Phone</label>
                                        <div class="flex">
                                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                                +234
                                            </span>
                                            <input type="tel" name="receiver_phone" required
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-r-md focus:outline-none focus:ring-2 focus:ring-primary"
                                                placeholder="Enter phone number (any format)">
                                        </div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Drop Location</label>
                                        <input type="text" name="drop_location" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Send Date</label>
                                        <input type="date" name="send_date" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pickup/Delivery Date</label>
                                        <input type="date" name="pickup_date" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                </div>
                            </div>

                            <!-- Item Details -->
                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <h3 class="text-lg font-semibold mb-4 text-gray-800">Item Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <textarea name="description" required rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                                        <input type="number" name="weight_kg" required min="0.1" step="0.1"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Dimensions</label>
                                        <input type="text" name="dimensions" placeholder="L x W x H"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Package Type</label>
                                        <select name="package_type" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                            <option value="document">Document</option>
                                            <option value="parcel">Parcel</option>
                                            <option value="package">Package</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                                        <textarea name="special_instructions" rows="2"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Dispatch Information -->
                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <h3 class="text-lg font-semibold mb-4 text-gray-800">Dispatch Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Means of Dispatch</label>
                                        <select name="dispatch_method" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                            <option value="">Select Method</option>
                                            <option value="Air">Air</option>
                                            <option value="Land">Land</option>
                                            <option value="Sea">Sea</option>
                                            <option value="Rider">Rider</option>
                                            <option value="Train">Train</option>
                                            <option value="Taxi">Taxi</option>
                                            <option value="Bus">Bus</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sent From Country</label>
                                        <input type="text" name="sent_from_country" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sent From State</label>
                                        <input type="text" name="sent_from_state" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sent From Terminal/Office Address</label>
                                        <input type="text" name="sent_from_terminal" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Information -->
                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                        <select name="payment_method" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Payment Method</option>
                                            <option value="cash">Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="card">Card</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                                        <select name="payment_status" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                            <option value="pending">Pending</option>
                                            <option value="paid">Paid</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid (₦)</label>
                                        <input type="number" name="amount_paid" step="0.01" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Enter amount paid">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Paid By</label>
                                        <select name="paid_by" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select who will pay</option>
                                            <option value="sender">Sender</option>
                                            <option value="receiver">Receiver</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="consignments.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-blue-800">
                                Create Consignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
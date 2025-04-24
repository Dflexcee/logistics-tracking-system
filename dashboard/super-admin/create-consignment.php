<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: ../../login.php");
    exit();
}

$is_superadmin = ($_SESSION['user_role'] === 'superadmin');
$is_agent = ($_SESSION['user_role'] === 'agent');

require_once '../../include/db.php';

// Get all active agents
$agent_sql = "SELECT id, name FROM users WHERE role = 'agent' AND status = 'active' ORDER BY name";
$agent_result = execute_query($agent_sql);

$agents = [];
if ($agent_result) {
    while ($row = $agent_result->fetch_assoc()) {
        $agents[] = $row;
    }
}

// Get error message if any
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Consignment - FLEXCEE Logistics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
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
<body class="h-full bg-gray-100 dark:bg-gray-900">
    <!-- Top Navbar -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg fixed w-full z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-primary dark:text-white">Create New Consignment</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <button id="darkModeToggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-moon dark:hidden text-gray-600"></i>
                        <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                    </button>
                    <!-- User Menu -->
                    <div class="relative">
                        <button class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">
                            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-800 shadow-lg mt-16 transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out z-10">
        <div class="h-full overflow-y-auto py-4">
            <nav class="space-y-2 px-2">
                <a href="index.php" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="agents.php" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-users"></i>
                    <span>Agents</span>
                </a>
                <a href="consignments.php" class="flex items-center space-x-2 text-primary dark:text-blue-400 bg-gray-100 dark:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-box"></i>
                    <span>Consignments</span>
                </a>
                <a href="users.php" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-user-cog"></i>
                    <span>Users</span>
                </a>
                <a href="settings.php" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="../../logout.php" class="flex items-center space-x-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 p-2 rounded-lg">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>
    </aside>

    <!-- Mobile Menu Button -->
    <button id="mobileMenuButton" class="fixed bottom-4 right-4 md:hidden bg-primary text-white p-3 rounded-full shadow-lg z-20">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <main class="md:ml-64 pt-16 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Form Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Create New Consignment</h2>
                    <a href="consignments.php" class="text-primary hover:text-blue-800 dark:hover:text-blue-400">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Consignments
                    </a>
                </div>

                <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>

                <form action="insert-consignment.php" method="POST" class="space-y-6" onsubmit="return validateForm()">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Sender Information -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Sender Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sender Name</label>
                                    <input type="text" name="sender_name" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sender Phone</label>
                                    <input type="tel" name="sender_phone" required pattern="[0-9]{10}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pickup Location</label>
                                    <input type="text" name="pickup_location" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>
                        </div>

                        <!-- Receiver Information -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Receiver Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Receiver Name</label>
                                    <input type="text" name="receiver_name" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Receiver Phone</label>
                                    <input type="tel" name="receiver_phone" required pattern="[0-9]{10}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Drop Location</label>
                                    <input type="text" name="drop_location" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Send Date</label>
                                    <input type="date" name="send_date" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pickup/Delivery Date</label>
                                    <input type="date" name="pickup_date" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>
                        </div>

                        <!-- Item Details -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Item Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Description</label>
                                    <textarea name="item_description" required rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (kg)</label>
                                    <input type="number" name="weight_kg" required min="0.1" step="0.1"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Package Type</label>
                                    <select name="package_type" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                        <option value="Standard">Standard</option>
                                        <option value="Express">Express</option>
                                        <option value="Fragile">Fragile</option>
                                        <option value="Bulk">Bulk</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Dispatch Information -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Dispatch Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Means of Dispatch</label>
                                    <select name="dispatch_method" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
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
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sent From Country</label>
                                    <input type="text" name="sent_from_country" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sent From State</label>
                                    <input type="text" name="sent_from_state" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sent From Terminal/Office Address</label>
                                    <input type="text" name="sent_from_terminal" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Assignment Information</h3>
                            
                            <div>
                                <label for="agent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign Agent</label>
                                <select id="agent_id" name="agent_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Select an agent</option>
                                    <?php foreach ($agents as $agent): ?>
                                    <option value="<?php echo $agent['id']; ?>">
                                        <?php echo htmlspecialchars($agent['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <select id="status" name="status" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="On Transit">On Transit</option>
                                    <option value="Out for Delivery">Out for Delivery</option>
                                    <option value="On Hold">On Hold</option>
                                    <option value="Delivered">Delivered</option>
                                    <option value="Clearance Pending">Clearance Pending</option>
                                </select>
                            </div>
                        </div>

                        <!-- Package Information -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4">Package Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Dimensions (L x W x H)</label>
                                    <input type="text" name="dimensions" class="w-full px-4 py-2 border rounded-lg" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Special Instructions</label>
                                    <textarea name="special_instructions" class="w-full px-4 py-2 border rounded-lg"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Payment Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount Paid</label>
                                    <input type="number" name="amount_paid" required min="0" step="0.01"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Paid By</label>
                                    <select name="paid_by" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                        <option value="sender">Sender</option>
                                        <option value="receiver">Receiver</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                                    <select name="payment_method" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Status</label>
                                    <select name="payment_status" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="consignments.php" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            Create Consignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 shadow-lg mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-gray-600 dark:text-gray-400">
                © FLEXCEE Logistics 2025
            </p>
        </div>
    </footer>

    <script>
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;

        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            html.classList.add('dark');
        }

        darkModeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.setItem('darkMode', html.classList.contains('dark'));
        });

        // Mobile Menu Toggle
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const sidebar = document.querySelector('aside');

        mobileMenuButton.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 768 && 
                !sidebar.contains(e.target) && 
                !mobileMenuButton.contains(e.target)) {
                sidebar.classList.add('-translate-x-full');
            }
        });

        // Form Validation
        function validateForm() {
            const senderPhone = document.getElementById('sender_phone').value;
            const receiverPhone = document.getElementById('receiver_phone').value;

            // Phone validation
            const phoneRegex = /^\+?[\d\s-]{10,}$/;
            if (!phoneRegex.test(senderPhone)) {
                alert('Please enter a valid sender phone number');
                return false;
            }

            if (!phoneRegex.test(receiverPhone)) {
                alert('Please enter a valid receiver phone number');
                return false;
            }

            return true;
        }
    </script>
</body>
</html> 
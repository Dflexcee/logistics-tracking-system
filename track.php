<?php
session_start();
require_once 'include/db.php';

// Get company settings
$company_settings = null;
$sql = "SELECT * FROM company_settings LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $company_settings = $result->fetch_assoc();
}

$tracking_number = isset($_GET['tracking_number']) ? sanitize_input($_GET['tracking_number']) : '';
$consignment = null;
$tracking_updates = [];
$error = null;

if (!empty($tracking_number)) {
    try {
        // Get consignment details with latest status
        $sql = "SELECT c.*, 
                u.name as agent_name,
                s.name as sender_name, s.phone as sender_phone, s.address as pickup_location,
                r.name as receiver_name, r.phone as receiver_phone, r.address as drop_location,
                th.status as current_status,
                th.location as current_location,
                th.created_at as status_date
                FROM consignments c 
                LEFT JOIN users u ON c.agent_id = u.id 
                LEFT JOIN senders s ON c.sender_id = s.id
                LEFT JOIN receivers r ON c.receiver_id = r.id
                LEFT JOIN (
                    SELECT consignment_id, status, location, created_at
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
            throw new Exception("Error preparing query: " . $conn->error);
        }
        
        $stmt->bind_param("s", $tracking_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $consignment = $result->fetch_assoc();
            
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
            
            $tracking_history = [];
            while ($row = $history_result->fetch_assoc()) {
                $tracking_history[] = $row;
            }
        } else {
            $error = "No consignment found with tracking number: " . htmlspecialchars($tracking_number);
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track & Trace - FLEXCEE Logistics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            body {
                background: white !important;
            }
            .tracking-container {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }
        .hero-bg {
            background-image: url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
        }
        .tracking-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
        }
        .status-pending { background-color: #FEF3C7; color: #92400E; }
        .status-transit { background-color: #DBEAFE; color: #1E40AF; }
        .status-delivery { background-color: #FCE7F3; color: #BE185D; }
        .status-delivered { background-color: #D1FAE5; color: #065F46; }
        .status-hold { background-color: #FEE2E2; color: #991B1B; }
    </style>
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
<body class="bg-gray-100 dark:bg-gray-900">
    <!-- Top Bar -->
    <div class="bg-primary text-white py-2 no-print">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex space-x-4">
                <a href="tel:<?php echo htmlspecialchars($company_settings['phone'] ?? '+2348065416156'); ?>" class="flex items-center">
                    <i class="fas fa-phone mr-2"></i>
                    <?php echo htmlspecialchars($company_settings['phone'] ?? '+234 806 541 6156'); ?>
                </a>
                <a href="mailto:<?php echo htmlspecialchars($company_settings['email'] ?? 'support@flexcee.com'); ?>" class="flex items-center">
                    <i class="fas fa-envelope mr-2"></i>
                    <?php echo htmlspecialchars($company_settings['email'] ?? 'support@flexcee.com'); ?>
                </a>
            </div>
            <div class="flex space-x-4">
                <a href="#" class="hover:text-gray-300">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="#" class="hover:text-gray-300">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="hover:text-gray-300">
                    <i class="fab fa-linkedin"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg no-print">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="index.php" class="text-2xl font-bold text-primary dark:text-white">
                    <?php if (!empty($company_settings['logo_url'])): ?>
                        <img src="<?php echo htmlspecialchars($company_settings['logo_url']); ?>" 
                             alt="<?php echo htmlspecialchars($company_settings['company_name']); ?>" 
                             class="h-10">
                    <?php else: ?>
                        FLEXCEE LOGISTICS
                    <?php endif; ?>
                </a>
                <div class="hidden md:flex space-x-8">
                    <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">Home</a>
                    <a href="about.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">About</a>
                    <a href="services.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">Services</a>
                    <a href="contact.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">Contact Us</a>
                    <a href="track.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">Track&Trace</a>
                </div>
                <button id="darkModeToggle" class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:block text-yellow-300"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative hero-bg h-[400px] no-print">
        <div class="absolute inset-0 bg-black opacity-50"></div>
        <div class="relative container mx-auto px-4 h-full flex items-center">
            <div class="text-white max-w-2xl">
                <h1 class="text-4xl font-bold mb-4">Track Your Shipment</h1>
                <p class="text-xl">Enter your tracking number to get real-time updates</p>
            </div>
        </div>
    </div>

    <!-- Tracking Section -->
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg no-print">
                    <form action="" method="GET" class="space-y-6">
                        <div>
                            <label for="tracking_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tracking Number</label>
                            <input type="text" id="tracking_number" name="tracking_number" value="<?php echo htmlspecialchars($tracking_number); ?>"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary" 
                                placeholder="Enter your tracking number" required>
                        </div>
                        <button type="submit" class="w-full bg-primary hover:bg-blue-800 text-white px-8 py-3 rounded-lg">Track Now</button>
                    </form>
                </div>

                <?php if ($error): ?>
                <!-- Error Message -->
                <div class="mt-8">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($consignment): ?>
                <!-- Tracking Result -->
                <div class="mt-8">
                    <div class="tracking-container rounded-lg shadow-lg overflow-hidden">
                        <!-- Print Button -->
                        <div class="p-4 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 no-print">
                            <button onclick="window.print()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition">
                                <i class="fas fa-print mr-2"></i> Print Tracking Info
                            </button>
                        </div>

                        <!-- Status Banner -->
                        <div class="bg-primary text-white p-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h2 class="text-2xl font-bold">Tracking Number: <?php echo htmlspecialchars($consignment['tracking_number']); ?></h2>
                                    <p class="text-sm mt-1">Created on <?php echo date('M d, Y', strtotime($consignment['status_date'])); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm">Current Status</p>
                                    <p class="text-xl font-semibold mt-1"><?php echo htmlspecialchars($consignment['current_status']); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Sender Information -->
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-semibold mb-4 text-primary">Sender Information</h3>
                                    <div class="space-y-3">
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
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-semibold mb-4 text-primary">Receiver Information</h3>
                                    <div class="space-y-3">
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
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-semibold mb-4 text-primary">Item Details</h3>
                                    <div class="space-y-3">
                                        <p class="text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Description:</span> <?php echo htmlspecialchars($consignment['description'] ?? 'N/A'); ?>
                                        </p>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Weight:</span> <?php echo htmlspecialchars($consignment['weight']); ?> kg
                                        </p>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Package Type:</span> <?php echo htmlspecialchars($consignment['package_type'] ?? 'N/A'); ?>
                                        </p>
                                        <?php if (!empty($consignment['special_instructions'])): ?>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Special Instructions:</span> <?php echo htmlspecialchars($consignment['special_instructions']); ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Payment Information -->
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-semibold mb-4 text-primary">Payment Information</h3>
                                    <div class="space-y-3">
                                        <p class="text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Amount:</span> <?php echo htmlspecialchars($consignment['amount_paid'] ?? '0.00'); ?>
                                        </p>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Payment Method:</span> <?php echo htmlspecialchars(ucfirst($consignment['payment_method'] ?? 'N/A')); ?>
                                        </p>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Payment Status:</span> <?php echo htmlspecialchars(ucfirst($consignment['payment_status'] ?? 'N/A')); ?>
                                        </p>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Paid By:</span> <?php echo htmlspecialchars(ucfirst($consignment['paid_by'] ?? 'N/A')); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Dispatch Information -->
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-semibold mb-4 text-primary">Dispatch Information</h3>
                                    <div class="space-y-3">
                                        <p class="text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Current Status:</span> <?php echo htmlspecialchars($consignment['current_status'] ?? 'Pending'); ?>
                                        </p>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Current Location:</span> <?php echo htmlspecialchars($consignment['current_location'] ?? 'N/A'); ?>
                                        </p>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Assigned Agent:</span> <?php echo htmlspecialchars($consignment['agent_name'] ?? 'Unassigned'); ?>
                                        </p>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Created Date:</span> <?php echo date('M d, Y', strtotime($consignment['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Tracking Timeline -->
                            <div class="mt-8">
                                <h3 class="text-xl font-bold mb-6 text-gray-800 dark:text-white">Tracking History</h3>
                                <div class="space-y-6">
                                    <?php if (empty($tracking_history)): ?>
                                    <p class="text-gray-500 dark:text-gray-400">No tracking updates available yet.</p>
                                    <?php else: ?>
                                    <?php foreach ($tracking_history as $history): ?>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center">
                                                <i class="fas fa-circle text-white text-xs"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="flex items-center">
                                                <h4 class="text-lg font-medium text-gray-800 dark:text-white">
                                                    <?php echo htmlspecialchars($history['status']); ?>
                                                </h4>
                                                <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">
                                                    <?php echo date('M d, Y h:i A', strtotime($history['created_at'])); ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($history['notes'])): ?>
                                            <p class="mt-1 text-gray-600 dark:text-gray-300">
                                                <?php echo htmlspecialchars($history['notes']); ?>
                                            </p>
                                            <?php endif; ?>
                                            <?php if (!empty($history['location'])): ?>
                                            <p class="mt-1 text-gray-600 dark:text-gray-300">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <?php echo htmlspecialchars($history['location']); ?>
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
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 no-print">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">About Us</h3>
                    <p class="text-gray-400">Your trusted partner in global logistics and shipping solutions.</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About</a></li>
                        <li><a href="services.php" class="text-gray-400 hover:text-white">Services</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Services</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Air Freight</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Sea Freight</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Road Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Warehousing</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <?php echo htmlspecialchars($company_settings['address'] ?? '88 Broklyn Golden Street. New York'); ?>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            <?php echo htmlspecialchars($company_settings['phone'] ?? '+234 806 541 6156'); ?>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            <?php echo htmlspecialchars($company_settings['email'] ?? 'support@flexcee.com'); ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($company_settings['company_name'] ?? 'FLEXCEE Logistics'); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        darkModeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        });
    </script>
</body>
</html> 
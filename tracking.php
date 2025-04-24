<?php
require_once 'include/db.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Get company settings
$company_settings = null;
$sql = "SELECT * FROM company_settings LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $company_settings = $result->fetch_assoc();
}

$tracking_number = sanitize_input($_GET['tracking_number'] ?? '');
$consignment = null;
$updates = [];
$error = null;

if (!empty($tracking_number)) {
    try {
        // Get consignment details
        $sql = "SELECT c.*, u.name as agent_name,
                s.name as sender_name, s.phone as sender_phone, s.address as sender_address,
                r.name as receiver_name, r.phone as receiver_phone, r.address as receiver_address,
                c.package_type, c.amount_paid, c.payment_method, c.payment_status, c.paid_by
                FROM consignments c 
                LEFT JOIN users u ON c.agent_id = u.id 
                LEFT JOIN senders s ON c.sender_id = s.id
                LEFT JOIN receivers r ON c.receiver_id = r.id
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
            $sql = "SELECT th.* 
                    FROM tracking_history th 
                    WHERE th.consignment_id = ? 
                    ORDER BY th.created_at DESC";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Database error");
            }
            
            $stmt->bind_param("i", $consignment['id']);
            $stmt->execute();
            $updates_result = $stmt->get_result();
            
            while ($row = $updates_result->fetch_assoc()) {
                $updates[] = $row;
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
    <title>Track & Trace - <?php echo htmlspecialchars($company_settings['company_name'] ?? 'FLEXCEE Logistics'); ?></title>
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
            background-image: linear-gradient(135deg, #1a365d 0%, #2d3748 100%);
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
                    <img src="<?php echo htmlspecialchars($company_settings['logo_url']); ?>" alt="<?php echo htmlspecialchars($company_settings['company_name']); ?>" class="h-10">
                    <?php else: ?>
                    <?php echo htmlspecialchars($company_settings['company_name'] ?? 'FLEXCEE LOGISTICS'); ?>
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
                        <!-- Company Branding -->
                        <div class="bg-gradient-to-r from-primary to-blue-800 p-6 text-white">
                            <div class="flex flex-col items-center text-center">
                                <?php if (!empty($company_settings['logo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($company_settings['logo_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($company_settings['company_name']); ?>" 
                                     class="h-20 w-20 rounded-full mb-4">
                                <?php endif; ?>
                                <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($company_settings['company_name'] ?? 'FLEXCEE Logistics'); ?></h2>
                                <div class="flex space-x-4 text-sm">
                                    <span><i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($company_settings['phone'] ?? '+234 806 541 6156'); ?></span>
                                    <span><i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($company_settings['email'] ?? 'support@flexcee.com'); ?></span>
                                </div>
                            </div>
                        </div>

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
                                    <p class="text-sm mt-1">Created on <?php echo date('M d, Y', strtotime($consignment['created_at'])); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm">Current Status</p>
                                    <p class="text-xl font-semibold mt-1"><?php echo htmlspecialchars($consignment['status']); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Sender Information -->
                                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Sender Information</h3>
                                    <div class="space-y-2">
                                        <p><span class="font-medium">Name:</span> <?php echo htmlspecialchars($consignment['sender_name']); ?></p>
                                        <p><span class="font-medium">Phone:</span> <?php echo htmlspecialchars($consignment['sender_phone']); ?></p>
                                        <p><span class="font-medium">Address:</span> <?php echo htmlspecialchars($consignment['sender_address']); ?></p>
                                    </div>
                                </div>

                                <!-- Receiver Information -->
                                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Receiver Information</h3>
                                    <div class="space-y-2">
                                        <p><span class="font-medium">Name:</span> <?php echo htmlspecialchars($consignment['receiver_name']); ?></p>
                                        <p><span class="font-medium">Phone:</span> <?php echo htmlspecialchars($consignment['receiver_phone']); ?></p>
                                        <p><span class="font-medium">Address:</span> <?php echo htmlspecialchars($consignment['receiver_address']); ?></p>
                                    </div>
                                </div>

                                <!-- Payment Information -->
                                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Payment Information</h3>
                                    <div class="space-y-2">
                                        <p><span class="font-medium">Amount:</span> $<?php echo number_format($consignment['amount_paid'], 2); ?></p>
                                        <p><span class="font-medium">Payment Method:</span> <?php echo htmlspecialchars($consignment['payment_method'] ?? 'Not specified'); ?></p>
                                        <p><span class="font-medium">Payment Status:</span> 
                                            <span class="px-2 py-1 rounded-full text-sm <?php 
                                                echo $consignment['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                                    ($consignment['payment_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                            ?>">
                                                <?php echo htmlspecialchars($consignment['payment_status'] ?? 'Not specified'); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Package Information -->
                                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Package Information</h3>
                                    <div class="space-y-2">
                                        <p><span class="font-medium">Description:</span> <?php echo htmlspecialchars($consignment['description']); ?></p>
                                        <p><span class="font-medium">Weight:</span> <?php echo htmlspecialchars($consignment['weight']); ?> kg</p>
                                        <p><span class="font-medium">Dimensions:</span> <?php echo htmlspecialchars($consignment['dimensions']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tracking Timeline -->
                        <div class="mt-8">
                            <h2 class="text-2xl font-bold text-primary mb-6">Tracking History</h2>
                            <div class="bg-white rounded-lg shadow-lg p-6">
                                <?php if (empty($updates)): ?>
                                    <div class="text-center py-8">
                                        <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500">No tracking updates available yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="relative">
                                        <!-- Timeline line -->
                                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                                        
                                        <?php foreach ($updates as $index => $update): ?>
                                            <div class="relative pl-12 pb-8">
                                                <!-- Timeline dot -->
                                                <div class="absolute left-0 w-8 h-8 rounded-full bg-white border-2 border-primary flex items-center justify-center">
                                                    <i class="fas fa-circle text-primary text-xs"></i>
                                                </div>
                                                
                                                <!-- Status badge -->
                                                <div class="mb-2">
                                                    <span class="px-3 py-1 rounded-full text-sm font-medium
                                                        <?php
                                                        switch($update['status']) {
                                                            case 'Delivered':
                                                                echo 'bg-green-100 text-green-800';
                                                                break;
                                                            case 'On Transit':
                                                                echo 'bg-blue-100 text-blue-800';
                                                                break;
                                                            case 'Out for Delivery':
                                                                echo 'bg-yellow-100 text-yellow-800';
                                                                break;
                                                            case 'On Hold':
                                                                echo 'bg-red-100 text-red-800';
                                                                break;
                                                            default:
                                                                echo 'bg-gray-100 text-gray-800';
                                                        }
                                                        ?>">
                                                        <?php echo htmlspecialchars($update['status']); ?>
                                                    </span>
                                                </div>
                                                
                                                <!-- Update content -->
                                                <div class="bg-white rounded-lg shadow p-4">
                                                    <p class="text-gray-700 mb-2"><?php echo htmlspecialchars($update['comment']); ?></p>
                                                    <div class="flex items-center text-sm text-gray-500">
                                                        <i class="fas fa-clock mr-2"></i>
                                                        <span><?php echo date('M d, Y H:i', strtotime($update['created_at'])); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
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
        // Dark Mode Toggle
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
<?php
require_once 'include/db.php';

$tracking_number = '';
$consignment = null;
$tracking_updates = [];
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['tracking'])) {
    $tracking_number = sanitize_input($_POST['tracking'] ?? $_GET['tracking'] ?? '');
    
    if (!empty($tracking_number)) {
        // Query consignment details
        $sql = "SELECT c.*, s.name as sender_name, s.phone as sender_phone 
                FROM consignments c 
                LEFT JOIN senders s ON c.sender_id = s.id 
                WHERE c.tracking_number = ?";
        $result = execute_query($sql, [$tracking_number]);
        
        if ($result && $result->num_rows > 0) {
            $consignment = $result->fetch_assoc();
            
            // Query tracking updates
            $sql = "SELECT * FROM tracking_updates 
                    WHERE consignment_id = ? 
                    ORDER BY timestamp DESC";
            $result = execute_query($sql, [$consignment['id']]);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $tracking_updates[] = $row;
                }
            }
        } else {
            $error = "No shipment found with tracking number: " . htmlspecialchars($tracking_number);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Shipment - Cargorover</title>
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
<body class="bg-gray-100 dark:bg-gray-900">
    <!-- Top Bar -->
    <div class="bg-primary text-white py-2">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex space-x-4">
                <a href="tel:+9288009850" class="flex items-center">
                    <i class="fas fa-phone mr-2"></i>
                    +92 (8800) - 9850
                </a>
                <a href="mailto:support@cargorover.com" class="flex items-center">
                    <i class="fas fa-envelope mr-2"></i>
                    support@cargorover.com
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
    <nav class="bg-white dark:bg-gray-800 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="index.php" class="text-2xl font-bold text-primary dark:text-white">
                    CARGOROVER
                </a>
                <div class="hidden md:flex space-x-8">
                    <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">Home</a>
                    <a href="about.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">About</a>
                    <a href="services.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">Services</a>
                    <a href="contact.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">Contact Us</a>
                    <a href="tracking.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">Track&Trace</a>
                </div>
                <button id="darkModeToggle" class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:block text-yellow-300"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Tracking Form -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto">
                <h1 class="text-4xl font-bold text-center mb-8 text-gray-800 dark:text-white">Track Your Shipment</h1>
                <form method="POST" class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
                    <div class="flex space-x-4">
                        <input type="text" name="tracking" value="<?php echo htmlspecialchars($tracking_number); ?>" 
                               class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white" 
                               placeholder="Enter tracking number" required>
                        <button type="submit" class="bg-primary text-white px-8 py-2 rounded-lg hover:bg-blue-800 transition">
                            Track
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php if ($error): ?>
    <!-- Error Message -->
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($consignment): ?>
    <!-- Tracking Results -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">Shipment Details</h2>
                            <div class="space-y-2">
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-semibold">Tracking Number:</span> 
                                    <?php echo htmlspecialchars($consignment['tracking_number']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-semibold">Sender:</span> 
                                    <?php echo htmlspecialchars($consignment['sender_name']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-semibold">Sender Phone:</span> 
                                    <?php echo htmlspecialchars($consignment['sender_phone']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-semibold">Receiver:</span> 
                                    <?php echo htmlspecialchars($consignment['receiver_name']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-semibold">Current Status:</span> 
                                    <span class="text-primary font-semibold">
                                        <?php echo htmlspecialchars($consignment['status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <button onclick="window.print()" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-800 transition">
                            <i class="fas fa-print mr-2"></i> Print
                        </button>
                    </div>

                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Tracking History</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white dark:bg-gray-700 rounded-lg">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-600">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Comment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                <?php foreach ($tracking_updates as $update): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-300">
                                        <?php echo htmlspecialchars($update['status']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-300">
                                        <?php echo date('M d, Y H:i', strtotime($update['timestamp'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800 dark:text-gray-300">
                                        <?php echo htmlspecialchars($update['comment']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
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
                            88 Broklyn Golden Street. New York
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            +92 (8800) - 9850
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            support@cargorover.com
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">&copy; 2024 Cargorover. All rights reserved.</p>
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
<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: ../../login.php");
    exit();
}

$is_superadmin = ($_SESSION['user_role'] === 'superadmin');
$is_agent = ($_SESSION['user_role'] === 'agent');

require_once '../../include/db.php';

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

$admin_name = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - FLEXCEE Logistics</title>
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
                    <h1 class="text-xl font-bold text-primary dark:text-white">Super Admin Dashboard</h1>
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
                            <span><?php echo htmlspecialchars($admin_name); ?></span>
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
                <a href="index.php" class="flex items-center space-x-2 text-primary dark:text-blue-400 bg-gray-100 dark:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="agents.php" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-users"></i>
                    <span>Agents</span>
                </a>
                <a href="consignments.php" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-box"></i>
                    <span>Consignments</span>
                </a>
                <a href="update-status.php" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-sync-alt"></i>
                    <span>Update Status</span>
                </a>
                <a href="payment-info.php" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Payment Information</span>
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
            <!-- Welcome Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
                    Welcome back, <?php echo htmlspecialchars($admin_name); ?>!
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Here's what's happening with your logistics system today.
                </p>
            </div>

            <!-- Dashboard Content -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Quick Stats Cards -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Total Agents</h3>
                    <p class="text-3xl font-bold text-primary dark:text-blue-400">0</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Active Consignments</h3>
                    <p class="text-3xl font-bold text-primary dark:text-blue-400">0</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Total Users</h3>
                    <p class="text-3xl font-bold text-primary dark:text-blue-400">0</p>
                </div>
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
    </script>
</body>
</html> 
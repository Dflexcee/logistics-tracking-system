<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: ../../login.php");
    exit();
}

$is_superadmin = ($_SESSION['user_role'] === 'superadmin');
$is_agent = ($_SESSION['user_role'] === 'agent');

require_once '../../include/db.php';

// Query agents
$sql = "SELECT id, name, email, status, created_at FROM users WHERE role = 'agent' ORDER BY created_at DESC";
$result = execute_query($sql);

$agents = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $agents[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Agents - FLEXCEE Logistics</title>
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
                    <h1 class="text-xl font-bold text-primary dark:text-white">Manage Agents</h1>
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
                <a href="agents.php" class="flex items-center space-x-2 text-primary dark:text-blue-400 bg-gray-100 dark:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-users"></i>
                    <span>Agents</span>
                </a>
                <a href="#" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-box"></i>
                    <span>Consignments</span>
                </a>
                <a href="#" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                    <i class="fas fa-user-cog"></i>
                    <span>Users</span>
                </a>
                <a href="#" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
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
            <!-- Header with Add Button -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Agents List</h2>
                <a href="create-agent.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition-colors">
                    <i class="fas fa-plus mr-2"></i> Add New Agent
                </a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['success']); ?></span>
            </div>
            <?php unset($_SESSION['success']); endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
            </div>
            <?php unset($_SESSION['error']); endif; ?>

            <!-- Agents Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if (empty($agents)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No agents found
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($agents as $agent): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        <?php echo htmlspecialchars($agent['id']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        <?php echo htmlspecialchars($agent['name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        <?php echo htmlspecialchars($agent['email']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $agent['status'] === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; ?>">
                                            <?php echo htmlspecialchars($agent['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        <?php echo date('M d, Y', strtotime($agent['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="edit-agent.php?id=<?php echo $agent['id']; ?>" class="text-primary hover:text-blue-800 dark:hover:text-blue-400 mr-3">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="delete-agent.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this agent?');">
                                            <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800 dark:hover:text-red-400">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
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

        // Delete Agent Function
        function deleteAgent(id) {
            if (confirm('Are you sure you want to delete this agent?')) {
                // Add delete functionality here
                console.log('Deleting agent:', id);
            }
        }
    </script>
</body>
</html> 
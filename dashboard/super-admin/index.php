<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is a super-admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

$admin_name = $_SESSION['user_name'] ?? 'Admin';

// Get total number of agents
$agents_sql = "SELECT COUNT(*) as total_agents FROM users WHERE role = 'agent'";
$agents_result = $conn->query($agents_sql);
$total_agents = $agents_result ? $agents_result->fetch_assoc()['total_agents'] : 0;

// Get total number of active consignments
$consignments_sql = "SELECT COUNT(*) as active_consignments FROM consignments 
                    WHERE status NOT IN ('Delivered', 'Cancelled')";
$consignments_result = $conn->query($consignments_sql);
$active_consignments = $consignments_result ? $consignments_result->fetch_assoc()['active_consignments'] : 0;

// Get total number of users
$users_sql = "SELECT COUNT(*) as total_users FROM users";
$users_result = $conn->query($users_sql);
$total_users = $users_result ? $users_result->fetch_assoc()['total_users'] : 0;

// Get recent activity logs with proper error handling
$activity_sql = "SELECT al.*, u.name as user_name, al.description as activity_description 
                FROM activity_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                ORDER BY al.created_at DESC 
                LIMIT 5";
$activity_result = $conn->query($activity_sql);
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-card {
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: #ffffff;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .activity-item {
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .activity-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .nav-link {
            transition: all 0.2s ease;
            color: #4b5563;
        }

        .nav-link:hover {
            background-color: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .nav-link.active {
            background-color: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }
    </style>
</head>
<body class="h-full bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full z-10">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-truck text-blue-600 mr-2"></i>
                        Logistics Dashboard
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                        <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
                        <a href="../../logout.php" class="text-red-600 hover:text-red-800 flex items-center">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex pt-16">
        <!-- Sidebar -->
        <aside class="w-64 fixed h-full bg-white shadow-lg border-r border-gray-200">
            <div class="p-4">
                <nav class="space-y-2">
                    <a href="index.php" class="nav-link active flex items-center px-4 py-3 rounded-lg">
                        <i class="fas fa-home mr-3"></i> Dashboard
                    </a>
                    <a href="consignments.php" class="nav-link flex items-center px-4 py-3 rounded-lg">
                        <i class="fas fa-box mr-3"></i> Consignments
                    </a>
                    <a href="agents.php" class="nav-link flex items-center px-4 py-3 rounded-lg">
                        <i class="fas fa-users mr-3"></i> Agents
                    </a>
                    <a href="tickets.php" class="nav-link flex items-center px-4 py-3 rounded-lg">
                        <i class="fas fa-ticket-alt mr-3"></i> Tickets
                    </a>
                    <a href="settings.php" class="nav-link flex items-center px-4 py-3 rounded-lg">
                        <i class="fas fa-cog mr-3"></i> Settings
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Content Area -->
        <div class="flex-1 ml-64 p-8">
            <!-- Welcome Section -->
            <div class="dashboard-card mb-8 p-6 rounded-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">
                    Welcome back, <?php echo htmlspecialchars($admin_name); ?>!
                </h2>
                <p class="text-gray-600">
                    Here's what's happening with your logistics system today.
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Agents Card -->
                <div class="dashboard-card rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100">
                            <i class="fas fa-users text-2xl text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-600">Total Agents</h3>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $total_agents; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Active Consignments Card -->
                <div class="dashboard-card rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100">
                            <i class="fas fa-box text-2xl text-green-600"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-600">Active Consignments</h3>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $active_consignments; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Total Users Card -->
                <div class="dashboard-card rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100">
                            <i class="fas fa-user-shield text-2xl text-purple-600"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-600">Total Users</h3>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $total_users; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-card rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Recent Activity
                    </h3>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php if ($activity_result && $activity_result->num_rows > 0): ?>
                        <?php while ($activity = $activity_result->fetch_assoc()): ?>
                            <div class="activity-item p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-circle text-blue-500 text-xs"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($activity['user_name'] ?? 'System'); ?>
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($activity['activity_description'] ?? 'No description available'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-4 text-center text-gray-500">
                            No recent activity to display
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
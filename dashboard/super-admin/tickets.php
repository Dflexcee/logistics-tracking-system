<?php
session_start();
require_once '../../include/db.php';

// Check if user is logged in and is a super-admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit();
}

// Get company details
$company_sql = "SELECT * FROM company_details LIMIT 1";
$company_result = $conn->query($company_sql);

if (!$company_result) {
    die("Error fetching company details: " . $conn->error);
}

$company = $company_result->fetch_assoc();
if (!$company) {
    die("Company details not found. Please set up company details first.");
}

// Get all contact messages
$messages_sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$messages_result = $conn->query($messages_sql);

if (!$messages_result) {
    die("Error fetching messages: " . $conn->error);
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $message_id = $_POST['message_id'];
    $reply_message = $_POST['reply_message'];
    
    // Get the original message
    $get_message_sql = "SELECT * FROM contact_messages WHERE id = ?";
    $stmt = $conn->prepare($get_message_sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $message_id);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }
    
    $message = $stmt->get_result()->fetch_assoc();
    if (!$message) {
        die("Message not found");
    }
    
    // Send email reply
    $to = $message['email'];
    $subject = "Re: Your Contact Message - " . $company['company_name'];
    $headers = "From: " . $company['email'] . "\r\n";
    $headers .= "Reply-To: " . $company['email'] . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $email_content = "
    <html>
    <body>
        <img src='" . $company['logo'] . "' alt='" . $company['company_name'] . "' style='max-width: 200px;'><br>
        <h2>Thank you for contacting " . $company['company_name'] . "</h2>
        <p>We have received your message and here is our response:</p>
        <div style='background-color: #f5f5f5; padding: 15px; margin: 10px 0;'>
            " . nl2br(htmlspecialchars($reply_message)) . "
        </div>
        <p>If you have any further questions, please don't hesitate to contact us.</p>
        <p>Best regards,<br>" . $company['company_name'] . "<br>Phone: " . $company['phone'] . "</p>
    </body>
    </html>";
    
    if (mail($to, $subject, $email_content, $headers)) {
        $success = "Reply sent successfully!";
    } else {
        $error = "Failed to send reply. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Super Admin</title>
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
                    <a href="consignments.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-box mr-2"></i> Consignments
                    </a>
                    <a href="agents.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-users mr-2"></i> Agents
                    </a>
                    <a href="tickets.php" class="block px-4 py-2 bg-blue-50 text-blue-600 rounded-lg">
                        <i class="fas fa-ticket-alt mr-2"></i> Tickets
                    </a>
                    <a href="settings.php" class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                </nav>
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 p-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Contact Messages
                        </h3>
                    </div>

                    <?php if (isset($success)): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-4">
                        <?php while ($message = $messages_result->fetch_assoc()): ?>
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium"><?php echo htmlspecialchars($message['name']); ?></h4>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($message['email']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></p>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                </div>
                                <div class="mt-4">
                                    <form method="POST" class="space-y-2">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <textarea name="reply_message" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Type your reply here..."></textarea>
                                        <button type="submit" name="reply" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                            <i class="fas fa-reply mr-2"></i> Reply
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
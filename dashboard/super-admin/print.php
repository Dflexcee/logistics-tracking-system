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

// Get company settings
$company_settings = null;
$sql = "SELECT * FROM company_settings LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $company_settings = $result->fetch_assoc();
}

// Get tracking number from URL
$tracking_number = sanitize_input($_GET['tracking_id'] ?? '');

if (empty($tracking_number)) {
    die('Invalid tracking number');
}

try {
    // Get consignment details
    $sql = "SELECT c.*, u.name as agent_name 
            FROM consignments c 
            LEFT JOIN users u ON c.agent_id = u.id 
            WHERE c.tracking_number = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Database error");
    }
    
    $stmt->bind_param("s", $tracking_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die('Consignment not found');
    }
    
    $consignment = $result->fetch_assoc();
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consignment Label - <?php echo htmlspecialchars($tracking_number); ?></title>
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
                margin: 0;
                padding: 0;
                background: white !important;
            }
            .page-break {
                page-break-after: always;
            }
            .label-container {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                margin: 0 !important;
                padding: 0.5cm !important;
            }
        }
        .branding-bg {
            background-image: linear-gradient(135deg, #1a365d 0%, #2d3748 100%);
        }
        .label-container {
            width: 21cm;
            min-height: 29.7cm;
            padding: 1cm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .info-section {
            background: #f8fafc;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
        }
        .info-section h3 {
            color: #1a365d;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0.25rem;
        }
        .info-item {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        .info-label {
            font-weight: 500;
            color: #4a5568;
        }
        .info-value {
            color: #2d3748;
        }
        .barcode-section {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            text-align: center;
            margin-top: 1rem;
        }
        .barcode-number {
            font-family: monospace;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a365d;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Print Button -->
    <div class="no-print fixed top-4 right-4">
        <button onclick="window.print()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-800">
            <i class="fas fa-print mr-2"></i> Print Label
        </button>
    </div>

    <!-- Consignment Label -->
    <div class="label-container">
        <!-- Company Branding -->
        <div class="branding-bg text-white p-4 rounded-t-lg mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <?php if (!empty($company_settings['logo_url'])): ?>
                    <img src="<?php echo htmlspecialchars($company_settings['logo_url']); ?>" 
                         alt="<?php echo htmlspecialchars($company_settings['company_name']); ?>" 
                         class="h-12 w-12 rounded-full">
                    <?php endif; ?>
                    <div>
                        <h1 class="text-xl font-bold"><?php echo htmlspecialchars($company_settings['company_name'] ?? 'FLEXCEE Logistics'); ?></h1>
                        <div class="text-sm">
                            <span><i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($company_settings['phone'] ?? '+234 806 541 6156'); ?></span>
                            <span class="ml-4"><i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($company_settings['email'] ?? 'support@flexcee.com'); ?></span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm">Tracking Number</div>
                    <div class="text-lg font-bold"><?php echo htmlspecialchars($consignment['tracking_number']); ?></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="info-grid">
            <!-- Sender Information -->
            <div class="info-section">
                <h3>Sender Information</h3>
                <div class="info-item">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($consignment['sender_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($consignment['sender_phone']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Address:</span>
                    <span class="info-value"><?php echo htmlspecialchars($consignment['pickup_location']); ?></span>
                </div>
            </div>

            <!-- Receiver Information -->
            <div class="info-section">
                <h3>Receiver Information</h3>
                <div class="info-item">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($consignment['receiver_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($consignment['receiver_phone']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Address:</span>
                    <span class="info-value"><?php echo htmlspecialchars($consignment['drop_location']); ?></span>
                </div>
            </div>

            <!-- Item Details -->
            <div class="info-section">
                <h3>Item Details</h3>
                <div class="info-item">
                    <span class="info-label">Description:</span>
                    <span class="info-value"><?php echo htmlspecialchars($consignment['item_description']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Weight:</span>
                    <span class="info-value"><?php echo htmlspecialchars($consignment['weight_kg']); ?> kg</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Amount Paid:</span>
                    <span class="info-value">$<?php echo number_format($consignment['amount_paid'], 2); ?></span>
                </div>
            </div>

            <!-- Dispatch Information -->
            <div class="info-section">
                <h3>Dispatch Information</h3>
                <div class="info-item">
                    <span class="info-label">Method:</span>
                    <span class="info-value"><?php echo htmlspecialchars($consignment['dispatch_method']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">From:</span>
                    <span class="info-value"><?php echo htmlspecialchars($consignment['sent_from_country'] . ', ' . $consignment['sent_from_state']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Agent:</span>
                    <span class="info-value"><?php echo htmlspecialchars($consignment['agent_name']); ?></span>
                </div>
            </div>
        </div>

        <!-- Barcode Section -->
        <div class="barcode-section">
            <div class="text-sm text-gray-600 mb-2">Scan to Track</div>
            <div class="barcode-number"><?php echo htmlspecialchars($consignment['tracking_number']); ?></div>
        </div>

        <!-- Footer -->
        <div class="mt-4 text-center text-sm text-gray-600">
            <p>Thank you for choosing <?php echo htmlspecialchars($company_settings['company_name'] ?? 'FLEXCEE Logistics'); ?></p>
            <p>For tracking updates, visit www.flexcee.com/track</p>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Uncomment the line below to enable auto-printing
            // window.print();
        };
    </script>
</body>
</html> 
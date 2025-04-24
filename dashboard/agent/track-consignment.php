// Get consignment details with sender and receiver information
if (!isset($tracking_number) || empty($tracking_number)) {
    echo "<p class='text-red-600'>Please provide a valid tracking number.</p>";
    exit();
}

$sql = "SELECT c.*, s.name as sender_name, s.phone as sender_phone, s.address as pickup_location,
        r.name as receiver_name, r.phone as receiver_phone, r.address as drop_location
        FROM consignments c
        JOIN senders s ON c.sender_id = s.id
        JOIN receivers r ON c.receiver_id = r.id
        WHERE c.tracking_number = ?";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("s", $tracking_number);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $consignment = $result->fetch_assoc();
        
        // Get tracking history with error handling
        $history_sql = "SELECT * FROM tracking_history WHERE consignment_id = ? ORDER BY created_at DESC";
        $history_stmt = $conn->prepare($history_sql);
        if (!$history_stmt) {
            throw new Exception("Failed to prepare history statement: " . $conn->error);
        }
        
        $history_stmt->bind_param("i", $consignment['id']);
        if (!$history_stmt->execute()) {
            throw new Exception("Failed to execute history query: " . $history_stmt->error);
        }
        
        $history_result = $history_stmt->get_result();
        
        // Display consignment details with proper escaping
        echo "<div class='consignment-details bg-white shadow rounded-lg p-6'>";
        echo "<h2 class='text-2xl font-bold mb-4'>Consignment Details</h2>";
        
        // Item Details Section
        echo "<div class='mb-8'>";
        echo "<h3 class='text-xl font-semibold mb-3 text-gray-800'>Item Details</h3>";
        echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg'>";
        echo "<p><strong>Weight:</strong> " . htmlspecialchars($consignment['weight']) . " kg</p>";
        echo "<p><strong>Dimensions:</strong> " . htmlspecialchars($consignment['dimensions']) . "</p>";
        echo "<p><strong>Package Type:</strong> " . htmlspecialchars($consignment['package_type']) . "</p>";
        echo "<p><strong>Status:</strong> <span class='font-semibold text-blue-600'>" . htmlspecialchars($consignment['status']) . "</span></p>";
        if (!empty($consignment['special_instructions'])) {
            echo "<div class='col-span-2'>";
            echo "<strong>Special Instructions:</strong><br>";
            echo "<p class='text-gray-700'>" . nl2br(htmlspecialchars($consignment['special_instructions'])) . "</p>";
            echo "</div>";
        }
        echo "</div>";
        echo "</div>";

        // Payment Details Section
        echo "<div class='mb-8'>";
        echo "<h3 class='text-xl font-semibold mb-3 text-gray-800'>Payment Details</h3>";
        echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg'>";
        
        // Amount and Payment Status
        echo "<div class='flex justify-between items-center p-3 bg-white rounded shadow-sm'>";
        echo "<div>";
        echo "<p class='text-sm text-gray-600'>Amount Paid</p>";
        echo "<p class='text-lg font-semibold'>" . number_format($consignment['amount_paid'], 2) . " USD</p>";
        echo "</div>";
        echo "<div class='text-right'>";
        echo "<p class='text-sm text-gray-600'>Payment Status</p>";
        $status_color = $consignment['payment_status'] === 'paid' ? 'text-green-600' : 'text-yellow-600';
        echo "<p class='text-lg font-semibold capitalize {$status_color}'>" . 
             ($consignment['payment_status'] ? htmlspecialchars($consignment['payment_status']) : 'Pending') . "</p>";
        echo "</div>";
        echo "</div>";

        // Payment Method and Payer
        echo "<div class='flex justify-between items-center p-3 bg-white rounded shadow-sm'>";
        echo "<div>";
        echo "<p class='text-sm text-gray-600'>Payment Method</p>";
        echo "<p class='text-lg font-semibold capitalize'>" . 
             ($consignment['payment_method'] ? htmlspecialchars(str_replace('_', ' ', $consignment['payment_method'])) : 'Not specified') . "</p>";
        echo "</div>";
        echo "<div class='text-right'>";
        echo "<p class='text-sm text-gray-600'>Paid By</p>";
        echo "<p class='text-lg font-semibold capitalize'>" . 
             ($consignment['paid_by'] ? htmlspecialchars($consignment['paid_by']) : 'Not specified') . "</p>";
        echo "</div>";
        echo "</div>";

        // Payment Date if available
        if (!empty($consignment['payment_date'])) {
            echo "<div class='col-span-2 p-3 bg-white rounded shadow-sm'>";
            echo "<p class='text-sm text-gray-600'>Payment Date</p>";
            echo "<p class='text-lg font-semibold'>" . date('F j, Y, g:i a', strtotime($consignment['payment_date'])) . "</p>";
            echo "</div>";
        }

        // Additional Payment Information if available
        if (!empty($consignment['payment_reference'])) {
            echo "<div class='col-span-2 p-3 bg-white rounded shadow-sm'>";
            echo "<p class='text-sm text-gray-600'>Payment Reference</p>";
            echo "<p class='text-lg font-semibold'>" . htmlspecialchars($consignment['payment_reference']) . "</p>";
            echo "</div>";
        }

        echo "</div>"; // End of Payment Details grid
        echo "</div>"; // End of Payment Details section

        // Continue with Sender Information
        echo "<div class='mb-8'>";
        echo "<h3 class='text-xl font-semibold mb-3 text-gray-800'>Sender Information</h3>";
        echo "<p><strong>Name:</strong> " . htmlspecialchars($consignment['sender_name']) . "</p>";
        echo "<p><strong>Phone:</strong> " . htmlspecialchars($consignment['sender_phone']) . "</p>";
        echo "<p><strong>Pickup Location:</strong> " . htmlspecialchars($consignment['pickup_location']) . "</p>";
        
        echo "<h3>Receiver Information</h3>";
        echo "<p><strong>Name:</strong> " . htmlspecialchars($consignment['receiver_name']) . "</p>";
        echo "<p><strong>Phone:</strong> " . htmlspecialchars($consignment['receiver_phone']) . "</p>";
        echo "<p><strong>Drop Location:</strong> " . htmlspecialchars($consignment['drop_location']) . "</p>";
        
        echo "<h3>Tracking History</h3>";
        echo "<table class='tracking-history'>";
        echo "<tr><th>Date</th><th>Status</th><th>Location</th><th>Notes</th></tr>";
        
        while ($history = $history_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($history['created_at']) . "</td>";
            echo "<td>" . htmlspecialchars($history['status']) . "</td>";
            echo "<td>" . htmlspecialchars($history['location']) . "</td>";
            echo "<td>" . htmlspecialchars($history['notes']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
    } else {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative'>";
        echo "<p>No consignment found with tracking number: " . htmlspecialchars($tracking_number) . "</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative'>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
} 
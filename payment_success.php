<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get booking ID
if (!isset($_GET['booking_id'])) {
    header('Location: mainManager.php');
    exit();
}

$booking_id = intval($_GET['booking_id']);

// Get booking details
$query = "SELECT b.*, 
          CASE 
              WHEN b.resource_type = 'desk' THEN d.desk_no
              ELSE r.room_no
          END as resource_name,
          CASE 
              WHEN b.resource_type = 'desk' THEN d.rent_per_hour
              ELSE r.rent_per_hour
          END as rent_per_hour
          FROM bookings b
          LEFT JOIN desks d ON b.resource_type = 'desk' AND b.resource_id = d.id
          LEFT JOIN rooms r ON b.resource_type = 'room' AND b.resource_id = r.id
          WHERE b.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $booking_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    header('Location: mainManager.php?error=invalid_booking');
    exit();
}

// Calculate total amount
$start_time = new DateTime($booking['start_time']);
$end_time = new DateTime($booking['end_time']);
$hours = $end_time->diff($start_time)->h;
$total_amount = $hours * $booking['rent_per_hour'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Coworking Space</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="mainManager.php" class="text-xl font-bold text-gray-800">Coworking Space</a>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-600 mr-4"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="text-gray-600 hover:text-gray-800">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-center mb-8">
                <i class="fas fa-check-circle text-green-500 text-6xl mb-4"></i>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment Successful!</h1>
                <p class="text-gray-600">Your booking has been confirmed.</p>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Booking Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Booking ID</p>
                        <p class="font-medium"><?php echo $booking['id']; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Resource</p>
                        <p class="font-medium"><?php echo ucfirst($booking['resource_type']) . ' ' . $booking['resource_name']; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Start Time</p>
                        <p class="font-medium"><?php echo date('M d, Y H:i', strtotime($booking['start_time'])); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">End Time</p>
                        <p class="font-medium"><?php echo date('M d, Y H:i', strtotime($booking['end_time'])); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Payment Method</p>
                        <p class="font-medium"><?php echo ucfirst($booking['payment_method']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Total Amount</p>
                        <p class="font-medium">$<?php echo number_format($total_amount, 2); ?></p>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="mainManager.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html> 
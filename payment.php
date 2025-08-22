<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get booking details
if (!isset($_GET['booking_id'])) {
    header("Location: mainManager.php");
    exit();
}

$booking_id = $_GET['booking_id'];
$username = $_SESSION['username'];

// Get booking details with user verification
$booking_query = "SELECT b.*, u.username, 
                 CASE 
                    WHEN b.resource_type = 'desk' THEN d.desk_no
                    WHEN b.resource_type = 'room' THEN r.room_no
                 END as resource_name
                 FROM bookings b
                 JOIN users u ON b.user_id = u.id
                 LEFT JOIN desks d ON b.resource_id = d.id AND b.resource_type = 'desk'
                 LEFT JOIN rooms r ON b.resource_id = r.id AND b.resource_type = 'room'
                 WHERE b.id = ? AND u.username = ?";
$stmt = mysqli_prepare($conn, $booking_query);
mysqli_stmt_bind_param($stmt, "is", $booking_id, $username);
mysqli_stmt_execute($stmt);
$booking_result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($booking_result);

if (!$booking) {
    header("Location: mainManager.php?error=invalid_booking");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - DeskFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="nav-bg fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <img src="./logo.png" alt="logo" class="h-8 w-auto">
                </div>
                <div class="flex items-center space-x-6">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($username); ?></span>
                    <a href="mainManager.php" class="btn btn-secondary">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-16 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="card p-6" data-aos="fade-up">
                <h2 class="section-title">Payment Details</h2>
                
                <!-- Booking Summary -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Booking Summary</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600">Resource</p>
                            <p class="font-medium"><?php echo htmlspecialchars($booking['resource_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600">Booking ID</p>
                            <p class="font-medium">#<?php echo $booking['id']; ?></p>
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
                            <p class="text-gray-600">Total Amount</p>
                            <p class="font-medium text-xl">₹<?php echo number_format($booking['total_amount'], 2); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600">Status</p>
                            <span class="badge <?php echo $booking['status'] == 'completed' ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <form action="process_payment.php" method="POST" class="space-y-6">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <div class="mt-2 space-y-4">
                            <div class="flex items-center">
                                <input type="radio" name="payment_method" value="cash" class="h-4 w-4 text-blue-600" required>
                                <label class="ml-3 block text-sm font-medium text-gray-700">
                                    Cash Payment
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="payment_method" value="online" class="h-4 w-4 text-blue-600">
                                <label class="ml-3 block text-sm font-medium text-gray-700">
                                    Online Payment
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            Complete Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    </script>
</body>
</html> 
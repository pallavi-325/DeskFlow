<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$sql = "SELECT position FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($user['position'] != 'admin') {
    header("Location: mainManager.php");
    exit();
}

// Handle booking status update
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    $update_query = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $status, $booking_id);
    mysqli_stmt_execute($stmt);
    header("Location: admin_bookings.php?success=status_updated");
    exit();
}

// Fetch all bookings with user and resource details
$bookings_query = "SELECT b.*, u.username, 
                  CASE 
                    WHEN b.resource_type = 'desk' THEN d.desk_no
                    WHEN b.resource_type = 'room' THEN r.room_no
                  END as resource_name
                  FROM bookings b
                  JOIN users u ON b.user_id = u.id
                  LEFT JOIN desks d ON b.resource_id = d.id AND b.resource_type = 'desk'
                  LEFT JOIN rooms r ON b.resource_id = r.id AND b.resource_type = 'room'
                  ORDER BY b.created_at DESC";
$bookings_result = mysqli_query($conn, $bookings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - DeskFlow</title>
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
                    <a href="mainAdmin.php" class="btn btn-secondary">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-16 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Bookings List -->
            <div class="card p-6" data-aos="fade-up">
                <h2 class="section-title">Manage Bookings</h2>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">Booking ID</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">User</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">Resource</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">Start Time</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">End Time</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">Amount</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">Status</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($booking = mysqli_fetch_assoc($bookings_result)): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-900">#<?php echo $booking['id']; ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <i class="fas fa-user text-indigo-600"></i>
                                        </div>
                                        <span class="font-medium text-gray-900"><?php echo htmlspecialchars($booking['username']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                                            <i class="<?php echo $booking['resource_type'] == 'desk' ? 'fas fa-desktop' : 'fas fa-door-open'; ?> text-gray-600"></i>
                                        </div>
                                        <span class="text-gray-900"><?php echo $booking['resource_type'] == 'desk' ? 'Desk ' : 'Room '; ?><?php echo htmlspecialchars($booking['resource_name']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-gray-900"><?php echo date('M d, Y', strtotime($booking['start_time'])); ?></span>
                                        <span class="text-gray-500 text-sm"><?php echo date('H:i', strtotime($booking['start_time'])); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-gray-900"><?php echo date('M d, Y', strtotime($booking['end_time'])); ?></span>
                                        <span class="text-gray-500 text-sm"><?php echo date('H:i', strtotime($booking['end_time'])); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-900">₹ <?php echo number_format($booking['total_amount'], 2); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full <?php 
                                        echo $booking['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                            ($booking['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                    ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <form action="admin_bookings.php" method="POST" class="inline">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <select name="status" 
                                                class="form-select rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                                onchange="this.form.submit()">
                                        <select name="status" class="form-select" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
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
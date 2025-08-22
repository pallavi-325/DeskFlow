<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['position'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get all bookings with user and resource details
$bookings_query = "SELECT b.*, u.username,
                  CASE 
                      WHEN b.resource_type = 'desk' THEN d.desk_no
                      WHEN b.resource_type = 'room' THEN r.room_no
                  END as resource_name,
                  CASE 
                      WHEN b.resource_type = 'desk' THEN d.rent_per_hour
                      WHEN b.resource_type = 'room' THEN r.rent_per_hour
                  END as rate_per_hour,
                  b.resource_type
                  FROM bookings b
                  JOIN users u ON b.user_id = u.id
                  LEFT JOIN desks d ON b.resource_type = 'desk' AND b.resource_id = d.id
                  LEFT JOIN rooms r ON b.resource_type = 'room' AND b.resource_id = r.id
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
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body class="gradient-bg min-h-screen">
    <!-- Navigation Bar -->
    <nav class="glass-effect sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <img src="logo.png" alt="DeskFlow Logo" class="h-10 w-auto">
                    <span class="text-xl font-bold text-gray-900">Manage Bookings</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="mainAdmin.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-8 px-4">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stats-card" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stats-label">Total Bookings</p>
                        <h3 class="stats-number"><?php echo mysqli_num_rows($bookings_result); ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                        <i class="fas fa-calendar-check text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="stats-card" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stats-label">Pending Bookings</p>
                        <h3 class="stats-number"><?php 
                            $pending = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
                            $pending_count = mysqli_fetch_assoc($pending)['count'];
                            echo $pending_count;
                        ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="stats-card" data-aos="fade-up" data-aos-delay="300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stats-label">Completed Bookings</p>
                        <h3 class="stats-number"><?php 
                            $completed = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status = 'completed'");
                            $completed_count = mysqli_fetch_assoc($completed)['count'];
                            echo $completed_count;
                        ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="card p-6" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table id="bookingsTable" class="min-w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Resource</th>
                            <th>Type</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php mysqli_data_seek($bookings_result, 0); // Reset pointer to beginning
                        while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                <td><?php echo htmlspecialchars($booking['resource_name']); ?></td>
                                <td><?php echo ucfirst($booking['resource_type']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($booking['start_time'])); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($booking['end_time'])); ?></td>
                                <td>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $booking['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                            ($booking['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                            'bg-red-100 text-red-800'); ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td class="space-x-2">
                                    <?php if ($booking['status'] == 'pending'): ?>
                                        <button onclick="approveBooking(<?php echo $booking['id']; ?>)"
                                                class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button onclick="rejectBooking(<?php echo $booking['id']; ?>)"
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="viewDetails(<?php echo $booking['id']; ?>)"
                                            class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="module">
        import { initScene } from './js/initScene.js';
        
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 50,
            delay: 100
        });

        // Initialize 3D scene
        const workspace = initScene();

        // Initialize DataTables
        $(document).ready(function() {
            $('#bookingsTable').DataTable({
                "order": [[0, "desc"]],
                "pageLength": 10,
                "language": {
                    "search": "Search bookings:",
                    "lengthMenu": "Show _MENU_ bookings per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ bookings",
                    "infoEmpty": "No bookings found",
                    "infoFiltered": "(filtered from _MAX_ total bookings)"
                }
            });
        });
    </script>

    <script>
        function approveBooking(id) {
            if (confirm('Are you sure you want to approve this booking?')) {
                window.location.href = `process_booking.php?action=approve&id=${id}`;
            }
        }

        function rejectBooking(id) {
            if (confirm('Are you sure you want to reject this booking?')) {
                window.location.href = `process_booking.php?action=reject&id=${id}`;
            }
        }

        function viewDetails(id) {
            window.location.href = `booking_details.php?id=${id}`;
        }
    </script>
</body>
</html> 
<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is manager
if (!isset($_SESSION['username']) || $_SESSION['position'] != 'manager') {
    header("Location: login.php");
    exit();
}

// Get user information
$username = $_SESSION['username'];
$sql = "SELECT position FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Double check position
if ($user['position'] != 'manager') {
    header("Location: login.php");
    exit();
}

// Get user ID
$user_query = "SELECT id FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
$_SESSION['user_id'] = $user['id'];

// Get recent bookings
$bookings_query = "SELECT b.*, u.username,
                  CASE 
                      WHEN b.resource_type = 'desk' THEN d.desk_no
                      WHEN b.resource_type = 'room' THEN r.room_no
                  END as resource_name,
                  CASE 
                      WHEN b.resource_type = 'desk' THEN 'desk'
                      WHEN b.resource_type = 'room' THEN 'room'
                  END as resource_type
                  FROM bookings b
                  JOIN users u ON b.user_id = u.id
                  LEFT JOIN desks d ON b.resource_type = 'desk' AND b.resource_id = d.id
                  LEFT JOIN rooms r ON b.resource_type = 'room' AND b.resource_id = r.id
                  ORDER BY b.created_at DESC LIMIT 5";
$bookings_result = mysqli_query($conn, $bookings_query);

// Get available resources
$desks_query = "SELECT d.id, d.desk_no, d.rent_per_hour, d.status 
                FROM desks d 
                WHERE d.status = 'available'";
$rooms_query = "SELECT r.id, r.room_no, r.room_type, r.capacity, r.rent_per_hour, r.status 
                FROM rooms r 
                WHERE r.status = 'available'";
$desks_result = mysqli_query($conn, $desks_query);
$rooms_result = mysqli_query($conn, $rooms_query);

// Fetch pending bills
$pending_bills_query = "SELECT b.*, 
                       CASE 
                           WHEN b.resource_type = 'desk' THEN d.desk_no
                           WHEN b.resource_type = 'room' THEN r.room_no
                       END as resource_name,
                       b.resource_type,
                       b.status
                       FROM bookings b
                       LEFT JOIN desks d ON b.resource_id = d.id AND b.resource_type = 'desk'
                       LEFT JOIN rooms r ON b.resource_id = r.id AND b.resource_type = 'room'
                       WHERE b.user_id = ? AND b.status = 'pending'
                       ORDER BY b.created_at DESC";
$stmt = mysqli_prepare($conn, $pending_bills_query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$pending_bills_result = mysqli_stmt_get_result($stmt);

// Get announcements
$announcements_query = "SELECT a.*, u.username, u.position 
                       FROM announcements a 
                       JOIN users u ON a.user_id = u.id 
                       ORDER BY a.created_at DESC 
                       LIMIT 5";
$announcements_result = mysqli_query($conn, $announcements_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - DeskFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body class="gradient-bg min-h-screen">
    <!-- Navigation Bar -->
    <nav class="glass-effect sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <img src="logo.png" alt="DeskFlow Logo" class="h-10 w-auto">
                    <span class="text-xl font-bold text-gray-900">Manager Dashboard</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-8 px-4">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="stats-card" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stats-label">My Bookings</p>
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
                        <p class="stats-label">Available Desks</p>
                        <h3 class="stats-number"><?php echo mysqli_num_rows($desks_result); ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-desktop text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="stats-card" data-aos="fade-up" data-aos-delay="300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stats-label">Available Rooms</p>
                        <h3 class="stats-number"><?php echo mysqli_num_rows($rooms_result); ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-door-open text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Bookings -->
        <div class="card p-6 mb-8" data-aos="fade-up">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">My Bookings</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resource</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($booking['resource_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($booking['resource_type']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y H:i', strtotime($booking['start_time'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y H:i', strtotime($booking['end_time'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $booking['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($booking['status'] == 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" 
                                       class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-credit-card"></i> Pay
                                    </a>
                                <?php endif; ?>
                                <button onclick="cancelBooking(<?php echo $booking['id']; ?>)" 
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Management Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="create_booking.php?type=desk" class="card p-6 hover-lift" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <i class="fas fa-desktop text-indigo-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Book a Desk</h3>
                        <p class="text-gray-600">Reserve a workspace desk</p>
                    </div>
                </div>
            </a>
            <a href="create_booking.php?type=room" class="card p-6 hover-lift" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-door-open text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Book a Room</h3>
                        <p class="text-gray-600">Reserve a meeting room</p>
                    </div>
                </div>
            </a>
            <a href="announcements.php" class="card p-6 hover-lift" data-aos="fade-up" data-aos-delay="300">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-bullhorn text-yellow-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Announcements</h3>
                        <p class="text-gray-600">View important updates</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Announcements Section -->
        <div class="card p-6 mb-8 mt-6" data-aos="fade-up">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Announcements</h2>
                <button onclick="openAnnouncementModal()" class="btn btn-primary rounded-full">
                    <i class="fas fa-plus mr-2"></i>Create Announcement
                </button>
            </div>
            <div class="space-y-4">
                <?php while ($announcement = mysqli_fetch_assoc($announcements_result)): ?>
                    <div class="p-4 bg-white rounded-lg shadow-sm">
                        <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($announcement['title']); ?></h4>
                        <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($announcement['content']); ?></p>
                        <p class="text-sm text-gray-400 mt-2">Posted on: <?php echo date('M d, Y', strtotime($announcement['created_at'])); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Create Announcement Modal -->
        <div id="announcementModal" class="fixed inset-0 bg-black bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 w-full max-w-md">
                <div class="card p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-900">Create Announcement</h3>
                        <button onclick="closeAnnouncementModal()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="createAnnouncementForm" action="process_announcement.php" method="POST" class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <input type="text" id="title" name="title" required
                                   class="form-input w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Enter announcement title">
                        </div>
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <textarea id="content" name="content" required rows="4"
                                    class="form-input w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Enter announcement content"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeAnnouncementModal()" 
                                    class="btn btn-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
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
    </script>

    <script>
        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                window.location.href = `process_booking.php?action=cancel&id=${bookingId}`;
            }
        }

        function openAnnouncementModal() {
            document.getElementById('announcementModal').classList.remove('hidden');
        }

        function closeAnnouncementModal() {
            document.getElementById('announcementModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('announcementModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAnnouncementModal();
            }
        });
    </script>
</body>
</html> 
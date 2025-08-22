<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['position'] != 'admin') {
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

// Get announcements
$announcements_query = "SELECT a.*, u.username, u.position 
                       FROM announcements a 
                       JOIN users u ON a.user_id = u.id 
                       ORDER BY a.created_at DESC 
                       LIMIT 5";
$announcements_result = mysqli_query($conn, $announcements_query);

// Get statistics
$users_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$bookings_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];
$desks_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM desks"))['count'];
$rooms_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM rooms"))['count'];

// Get recent bookings
$bookings_query = "SELECT b.*, u.username, 
                  CASE 
                    WHEN b.resource_type = 'desk' THEN d.desk_no
                    WHEN b.resource_type = 'room' THEN r.room_no
                  END as resource_name
                  FROM bookings b
                  JOIN users u ON b.user_id = u.id
                  LEFT JOIN desks d ON b.resource_id = d.id AND b.resource_type = 'desk'
                  LEFT JOIN rooms r ON b.resource_id = r.id AND b.resource_type = 'room'
                  ORDER BY b.created_at DESC LIMIT 5";
$bookings_result = mysqli_query($conn, $bookings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DeskFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #f6f8fc 0%, #e9f0f7 100%);
        }
        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .priority-high {
            border-left: 4px solid #ef4444;
        }
        .priority-medium {
            border-left: 4px solid #f59e0b;
        }
        .priority-low {
            border-left: 4px solid #10b981;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <!-- Navigation Bar -->
    <nav class="glass-effect sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <img src="logo.png" alt="DeskFlow Logo" class="h-10 w-auto">
                    <span class="text-xl font-bold text-gray-900">Admin Dashboard</span>
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

    <div class="max-w-7xl mx-auto py-8 px-4 relative z-10">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card glass-effect p-6" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Total Users</h3>
                        <p class="text-2xl font-bold text-blue-600"><?php echo $users_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="card glass-effect p-6" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Total Bookings</h3>
                        <p class="text-2xl font-bold text-green-600"><?php echo $bookings_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="card glass-effect p-6" data-aos="fade-up" data-aos-delay="300">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-desktop text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Total Desks</h3>
                        <p class="text-2xl font-bold text-purple-600"><?php echo $desks_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="card glass-effect p-6" data-aos="fade-up" data-aos-delay="400">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-door-open text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Total Rooms</h3>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo $rooms_count; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <a href="manage_users.php" class="card glass-effect p-6 hover-lift" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Manage Users</h3>
                        <p class="text-gray-600">Add, edit, or remove users</p>
                    </div>
                </div>
            </a>
            <a href="manage_bookings.php" class="card glass-effect p-6 hover-lift" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Manage Bookings</h3>
                        <p class="text-gray-600">View and manage bookings</p>
                    </div>
                </div>
            </a>
            <a href="settings.php" class="card glass-effect p-6 hover-lift" data-aos="fade-up" data-aos-delay="300">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <i class="fas fa-cog text-indigo-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Settings</h3>
                        <p class="text-gray-600">Configure system settings</p>
                    </div>
                </div>
            </a>
            <a href="manage_desks.php" class="card glass-effect p-6 hover-lift" data-aos="fade-up" data-aos-delay="400">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-desktop text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Manage Desks</h3>
                        <p class="text-gray-600">Add, edit, or remove desks</p>
                    </div>
                </div>
            </a>
            <a href="manage_rooms.php" class="card glass-effect p-6 hover-lift" data-aos="fade-up" data-aos-delay="500">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-door-open text-yellow-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Manage Rooms</h3>
                        <p class="text-gray-600">Add, edit, or remove rooms</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Announcements Section -->
        <div class="card glass-effect p-6 mb-8" data-aos="fade-up">
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
                        <p class="text-sm text-gray-400 mt-2">
                            Posted by <?php echo htmlspecialchars($announcement['username']); ?> 
                            on <?php echo date('M d, Y', strtotime($announcement['created_at'])); ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Create Announcement Modal -->
        <div id="announcementModal" class="fixed inset-0 bg-black bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 w-full max-w-md">
                <div class="card glass-effect p-6">
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
                                   class="form-input w-full rounded-lg"
                                   placeholder="Enter announcement title">
                        </div>
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <textarea id="content" name="content" required rows="4"
                                    class="form-input w-full rounded-lg"
                                    placeholder="Enter announcement content"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
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
        initScene();
    </script>

    <script>
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
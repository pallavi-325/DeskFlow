<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get announcements
$announcements_query = "SELECT a.*, u.username, u.position 
                       FROM announcements a 
                       JOIN users u ON a.user_id = u.id 
                       ORDER BY a.created_at DESC";
$announcements_result = mysqli_query($conn, $announcements_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Coworking Space</title>
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
                    <span class="text-xl font-bold text-gray-900">Announcements</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="mainManager.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="space-y-6">
            <?php while ($announcement = mysqli_fetch_assoc($announcements_result)): ?>
                <div class="card p-6" data-aos="fade-up">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                <?php echo htmlspecialchars($announcement['title']); ?>
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Posted by <?php echo htmlspecialchars($announcement['username']); ?> 
                                (<?php echo htmlspecialchars($announcement['position']); ?>)
                                on <?php echo date('M d, Y H:i', strtotime($announcement['created_at'])); ?>
                            </p>
                        </div>
                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                            <?php echo $announcement['priority'] == 'high' ? 'bg-red-100 text-red-800' : 
                                ($announcement['priority'] == 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                            <?php echo ucfirst($announcement['priority']); ?> Priority
                        </span>
                    </div>
                    <div class="mt-4 text-gray-700">
                        <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 50
        });
    </script>
</body>
</html> 
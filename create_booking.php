<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is manager
if (!isset($_SESSION['username']) || $_SESSION['position'] != 'manager') {
    header("Location: login.php");
    exit();
}

// Get booking type from URL
$type = isset($_GET['type']) ? $_GET['type'] : '';
if ($type != 'desk' && $type != 'room') {
    header("Location: mainManager.php?error=invalid_type");
    exit();
}

// Get available resources
if ($type == 'desk') {
    $resources_query = "SELECT d.id, d.desk_no as name, d.rent_per_hour 
                       FROM desks d 
                       WHERE d.status = 'available'";
} else {
    $resources_query = "SELECT r.id, r.room_no as name, r.rent_per_hour 
                       FROM rooms r 
                       WHERE r.status = 'available'";
}
$resources_result = mysqli_query($conn, $resources_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo ucfirst($type); ?> - Coworking Space</title>
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
                    <span class="text-xl font-bold text-gray-900">Book <?php echo ucfirst($type); ?></span>
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
        <div class="card p-6" data-aos="fade-up">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Book a <?php echo ucfirst($type); ?></h2>
            
            <form action="process_booking.php" method="POST" class="space-y-6">
                <input type="hidden" name="resource_type" value="<?php echo $type; ?>">
                
                <!-- Resource Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select <?php echo ucfirst($type); ?>
                    </label>
                    <select name="resource_id" required
                            class="form-select w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Choose a <?php echo $type; ?></option>
                        <?php while ($resource = mysqli_fetch_assoc($resources_result)): ?>
                            <option value="<?php echo $resource['id']; ?>">
                                <?php echo htmlspecialchars($resource['name']); ?> 
                                (₹<?php echo number_format($resource['rent_per_hour'], 2); ?>/hour)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Date and Time Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Start Time
                        </label>
                        <input type="datetime-local" name="start_time" required
                               class="form-input w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            End Time
                        </label>
                        <input type="datetime-local" name="end_time" required
                               class="form-input w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" 
                            class="btn btn-primary">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Book Now
                    </button>
                </div>
            </form>
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

        // Validate date and time
        document.querySelector('form').addEventListener('submit', function(e) {
            const startTime = new Date(document.querySelector('input[name="start_time"]').value);
            const endTime = new Date(document.querySelector('input[name="end_time"]').value);
            
            if (startTime >= endTime) {
                e.preventDefault();
                alert('End time must be after start time');
            }
        });
    </script>
</body>
</html> 
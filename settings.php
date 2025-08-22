<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['position'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $site_name = trim($_POST['site_name']);
    $min_duration = intval($_POST['min_booking_duration']);
    $max_duration = intval($_POST['max_booking_duration']);
    
    // Validate input
    if (empty($site_name) || $min_duration <= 0 || $max_duration <= 0 || $min_duration > $max_duration) {
        $error = "Invalid input values";
    } else {
        // Update settings
        $update_query = "UPDATE settings SET 
                        site_name = ?,
                        min_booking_duration = ?,
                        max_booking_duration = ?
                        WHERE id = 1";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sii", $site_name, $min_duration, $max_duration);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Settings updated successfully!";
        } else {
            $error = "Error updating settings: " . mysqli_error($conn);
        }
    }
}

// Get current settings
$settings_query = "SELECT * FROM settings WHERE id = 1";
$settings_result = mysqli_query($conn, $settings_query);
$settings = mysqli_fetch_assoc($settings_result);

if (!$settings) {
    // If no settings exist, create default settings
    $insert_query = "INSERT INTO settings (site_name, min_booking_duration, max_booking_duration) 
                    VALUES ('Workspace Management System', 30, 480)";
    if (mysqli_query($conn, $insert_query)) {
        $settings = [
            'site_name' => 'Workspace Management System',
            'min_booking_duration' => 30,
            'max_booking_duration' => 480
        ];
    } else {
        $error = "Error creating default settings: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo htmlspecialchars($settings['site_name']); ?></title>
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
                <div class="flex items-center">
                    <span class="text-xl font-bold text-gray-900">System Settings</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="mainAdmin.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-8 px-4 relative z-10">
        <div class="card glass-effect p-6" data-aos="fade-up">
            <?php if (isset($error)): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Site Name
                    </label>
                    <input type="text" class="form-input w-full rounded-lg" id="site_name" name="site_name" 
                           value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                </div>
                
                <div>
                    <label for="min_booking_duration" class="block text-sm font-medium text-gray-700 mb-2">
                        Minimum Booking Duration (minutes)
                    </label>
                    <input type="number" class="form-input w-full rounded-lg" id="min_booking_duration" 
                           name="min_booking_duration" min="1" 
                           value="<?php echo $settings['min_booking_duration']; ?>" required>
                </div>
                
                <div>
                    <label for="max_booking_duration" class="block text-sm font-medium text-gray-700 mb-2">
                        Maximum Booking Duration (minutes)
                    </label>
                    <input type="number" class="form-input w-full rounded-lg" id="max_booking_duration" 
                           name="max_booking_duration" min="1" 
                           value="<?php echo $settings['max_booking_duration']; ?>" required>
                </div>
                
                <div class="flex space-x-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Save Settings
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
        initScene();
    </script>
</body>
</html> 
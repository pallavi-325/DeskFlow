<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['position'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get total revenue
$revenue_query = "SELECT SUM(total_amount) as total_revenue FROM bookings WHERE status = 'completed'";
$revenue_result = mysqli_query($conn, $revenue_query);
$total_revenue = mysqli_fetch_assoc($revenue_result)['total_revenue'];

// Get revenue by resource type
$resource_revenue_query = "SELECT resource_type, SUM(total_amount) as revenue 
                         FROM bookings 
                         WHERE status = 'completed' 
                         GROUP BY resource_type";
$resource_revenue_result = mysqli_query($conn, $resource_revenue_query);

// Get bookings by status
$status_query = "SELECT status, COUNT(*) as count 
                FROM bookings 
                GROUP BY status";
$status_result = mysqli_query($conn, $status_query);

// Get most booked resources
$popular_resources_query = "SELECT 
    CASE 
        WHEN b.resource_type = 'desk' THEN d.desk_no
        WHEN b.resource_type = 'room' THEN r.room_no
    END as resource_name,
    b.resource_type,
    COUNT(*) as booking_count
    FROM bookings b
    LEFT JOIN desks d ON b.resource_type = 'desk' AND b.resource_id = d.id
    LEFT JOIN rooms r ON b.resource_type = 'room' AND b.resource_id = r.id
    GROUP BY b.resource_id, b.resource_type
    ORDER BY booking_count DESC
    LIMIT 5";
$popular_resources_result = mysqli_query($conn, $popular_resources_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - DeskFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="gradient-bg min-h-screen">
    <!-- Navigation Bar -->
    <nav class="glass-effect sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <img src="logo.png" alt="DeskFlow Logo" class="h-10 w-auto">
                    <span class="text-xl font-bold text-gray-900">Reports & Analytics</span>
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
        <!-- Revenue Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="card p-6" data-aos="fade-up">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Revenue Overview</h2>
                <div class="text-3xl font-bold text-indigo-600 mb-2">
                    ₹<?php echo number_format($total_revenue, 2); ?>
                </div>
                <p class="text-gray-600">Total Revenue from Completed Bookings</p>
                <div class="mt-4">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <div class="card p-6" data-aos="fade-up">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Booking Status</h2>
                <div class="mt-4">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Popular Resources -->
        <div class="card p-6 mb-8" data-aos="fade-up">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Most Booked Resources</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resource</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Bookings</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($resource = mysqli_fetch_assoc($popular_resources_result)): ?>
                            <tr>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($resource['resource_name']); ?></td>
                                <td class="px-6 py-4"><?php echo ucfirst($resource['resource_type']); ?></td>
                                <td class="px-6 py-4"><?php echo $resource['booking_count']; ?></td>
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

        // Initialize Charts
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: [<?php 
                    mysqli_data_seek($resource_revenue_result, 0);
                    $labels = [];
                    $data = [];
                    while ($row = mysqli_fetch_assoc($resource_revenue_result)) {
                        $labels[] = "'" . ucfirst($row['resource_type']) . "'";
                        $data[] = $row['revenue'];
                    }
                    echo implode(',', $labels);
                ?>],
                datasets: [{
                    label: 'Revenue by Resource Type',
                    data: [<?php echo implode(',', $data); ?>],
                    backgroundColor: ['rgba(99, 102, 241, 0.5)', 'rgba(59, 130, 246, 0.5)'],
                    borderColor: ['rgb(99, 102, 241)', 'rgb(59, 130, 246)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php 
                    mysqli_data_seek($status_result, 0);
                    $labels = [];
                    $data = [];
                    while ($row = mysqli_fetch_assoc($status_result)) {
                        $labels[] = "'" . ucfirst($row['status']) . "'";
                        $data[] = $row['count'];
                    }
                    echo implode(',', $labels);
                ?>],
                datasets: [{
                    data: [<?php echo implode(',', $data); ?>],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.5)',
                        'rgba(234, 179, 8, 0.5)',
                        'rgba(239, 68, 68, 0.5)'
                    ],
                    borderColor: [
                        'rgb(34, 197, 94)',
                        'rgb(234, 179, 8)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html> 
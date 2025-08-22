<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['position'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all desks
$desks_query = "SELECT * FROM desks ORDER BY desk_no";
$desks_result = mysqli_query($conn, $desks_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Desks - DeskFlow</title>
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
                    <span class="text-xl font-bold text-gray-900">Manage Desks</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="mainAdmin.php" class="btn btn-secondary">
                        Back to Dashboard
                    </a>
                    <button onclick="openAddDeskModal()" class="btn btn-primary flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Add New Desk</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-8 px-4">
        <div class="card p-6 card-hover-effect" data-aos="fade-up">
            <h2 class="section-title flex items-center space-x-2 mb-6">
                <i class="fas fa-desktop text-indigo-500"></i>
                <span>All Desks</span>
            </h2>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">Desk No</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">Rent/Hour</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($desk = mysqli_fetch_assoc($desks_result)): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                        <i class="fas fa-desktop text-indigo-600"></i>
                                    </div>
                                    <span class="font-medium text-gray-900">Desk <?php echo htmlspecialchars($desk['desk_no']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-900">₹ <?php echo number_format($desk['rent_per_hour'], 2); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full <?php 
                                    echo $desk['status'] == 'available' ? 'bg-green-100 text-green-800' : 
                                        ($desk['status'] == 'booked' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                ?>">
                                    <?php echo ucfirst($desk['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <button onclick="editDesk(<?php echo $desk['id']; ?>)" 
                                            class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="delete_desk.php" method="POST" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this desk?');">
                                        <input type="hidden" name="desk_id" value="<?php echo $desk['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Desk Modal -->
    <div id="addDeskModal" class="fixed inset-0 bg-black bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 w-full max-w-md">
            <div class="card glass-effect p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">Add New Desk</h3>
                    <button onclick="closeAddDeskModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="process_desk.php" method="POST" class="space-y-4">
                    <div>
                        <label for="desk_no" class="block text-sm font-medium text-gray-700 mb-2">Desk Number</label>
                        <input type="text" id="desk_no" name="desk_no" required
                               class="form-input w-full rounded-lg"
                               placeholder="Enter desk number">
                    </div>
                    <div>
                        <label for="rent_per_hour" class="block text-sm font-medium text-gray-700 mb-2">Rent per Hour</label>
                        <input type="number" id="rent_per_hour" name="rent_per_hour" required step="0.01"
                               class="form-input w-full rounded-lg"
                               placeholder="Enter rent per hour">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status" name="status" required
                                class="form-select w-full rounded-lg">
                            <option value="available">Available</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeAddDeskModal()" 
                                class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Desk</button>
                    </div>
                </form>
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

        function openAddDeskModal() {
            document.getElementById('addDeskModal').classList.remove('hidden');
        }

        function closeAddDeskModal() {
            document.getElementById('addDeskModal').classList.add('hidden');
        }

        function editDesk(id) {
            // Implement edit functionality
        }

        // Make functions available globally
        window.openAddDeskModal = openAddDeskModal;
        window.closeAddDeskModal = closeAddDeskModal;
        window.editDesk = editDesk;
    </script>
</body>
</html> 
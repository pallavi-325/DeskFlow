<?php
session_start();
include 'dbconnect.php';

// Get available resources
$desks_query = "SELECT COUNT(*) as count FROM desks WHERE status = 'available'";
$rooms_query = "SELECT COUNT(*) as count FROM rooms WHERE status = 'available'";
$desks_result = mysqli_query($conn, $desks_query);
$rooms_result = mysqli_query($conn, $rooms_query);
$available_desks = mysqli_fetch_assoc($desks_result)['count'];
$available_rooms = mysqli_fetch_assoc($rooms_result)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeskFlow - Coworking Space Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Space Grotesk', sans-serif;
        }
        body {
            background: transparent;
            color: #1a1a1a;
        }
        .nav-bg {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .card {
            background: rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            backdrop-filter: none;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }
        .btn-transition {
            transition: all 0.3s ease;
        }
        .btn-transition:hover {
            transform: translateY(-2px);
        }
        .hero-gradient {
            background: transparent;
            position: relative;
            z-index: 1;
            min-height: 100vh;
            margin-bottom: 200px;
        }
        .section-divider {
            height: 100px;
            background: linear-gradient(to bottom right, transparent 49%, rgba(246, 248, 252, 0.3) 50%);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-bottom: 1.5rem;
            background: rgba(246, 248, 252, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            backdrop-filter: none;
        }
        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }
        section {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: none;
            margin: 200px 0;
            padding: 100px 0;
        }
        footer {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: none;
            margin-top: 200px;
        }
        .text-container {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: none;
            padding: 2rem;
            border-radius: 1rem;
        }
        .spacing {
            height: 200px;
        }
        .timeline-item {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 2rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 2px;
            height: 100%;
            background: rgba(99, 102, 241, 0.3);
        }
        .timeline-dot {
            position: absolute;
            left: -0.5rem;
            top: 0.5rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: #6366f1;
        }
        .pricing-card {
            transition: all 0.3s ease;
        }
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .feature-highlight {
            position: relative;
            overflow: hidden;
        }
        .feature-highlight::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(99, 102, 241, 0.1), rgba(129, 140, 248, 0.1));
            z-index: -1;
        }
    </style>
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
                    <a href="#about" class="text-gray-700 hover:text-blue-600 transition-colors">About</a>
                    <a href="#features" class="text-gray-700 hover:text-blue-600 transition-colors">Features</a>
                    <a href="#resources" class="text-gray-700 hover:text-blue-600 transition-colors">Resources</a>
                    <?php if(isset($_SESSION['username'])): ?>
                        <a href="<?php echo $_SESSION['position'] == 'admin' ? 'mainAdmin.php' : 'mainManager.php'; ?>" 
                           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Dashboard
                        </a>
                        <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-gradient pt-16 min-h-screen flex items-center justify-center relative">
        <div class="text-center max-w-4xl px-4" data-aos="fade-up" data-aos-duration="1000">
            <h1 class="text-6xl font-bold text-gray-900 mb-6">Welcome to DeskFlow</h1>
            <p class="text-xl text-gray-600 mb-8">Your Modern Coworking Space Management Solution</p>
            <?php if(!isset($_SESSION['username'])): ?>
                <a href="login.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg hover:bg-blue-700 btn-transition inline-flex items-center">
                    Get Started
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            <?php endif; ?>
        </div>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down text-gray-400 text-2xl"></i>
        </div>
    </div>

    <!-- Enhanced About Section -->
    <section id="about" class="py-20">
        <div class="spacing"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-container" data-aos="fade-up" data-aos-duration="1000">
                <h2 class="text-4xl font-bold mb-6 text-gray-900">About DeskFlow</h2>
                <p class="text-gray-600 mb-4 text-lg">
                    DeskFlow is a modern coworking space management system designed to streamline workspace booking and management. 
                    Our platform provides an intuitive interface for both workspace administrators and users, making it easy to 
                    manage and book desks and meeting rooms.
                </p>
                <p class="text-gray-600 text-lg mb-8">
                    Whether you're a freelancer looking for a productive workspace or a company managing multiple resources, 
                    DeskFlow has the tools you need to optimize your workspace utilization.
                </p>
                
                <!-- Timeline -->
                <div class="mt-12">
                    <h3 class="text-2xl font-bold mb-6 text-gray-900">How It Works</h3>
                    <div class="timeline-item" data-aos="fade-right">
                        <div class="timeline-dot"></div>
                        <h4 class="text-xl font-bold mb-2 text-gray-900">1. Choose Your Space</h4>
                        <p class="text-gray-600">Browse through available desks and meeting rooms that suit your needs.</p>
                    </div>
                    <div class="timeline-item" data-aos="fade-right" data-aos-delay="100">
                        <div class="timeline-dot"></div>
                        <h4 class="text-xl font-bold mb-2 text-gray-900">2. Book Instantly</h4>
                        <p class="text-gray-600">Select your preferred time slot and book with just a few clicks.</p>
                    </div>
                    <div class="timeline-item" data-aos="fade-right" data-aos-delay="200">
                        <div class="timeline-dot"></div>
                        <h4 class="text-xl font-bold mb-2 text-gray-900">3. Manage Your Bookings</h4>
                        <p class="text-gray-600">Easily view, modify, or cancel your bookings through your dashboard.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="spacing"></div>
    </section>

    <!-- Enhanced Features Section -->
    <section id="features" class="py-20">
        <div class="spacing"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900" data-aos="fade-up">Key Features</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card p-8 rounded-lg feature-highlight" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Easy Booking</h3>
                    <p class="text-gray-600">Simple and intuitive booking system for desks and meeting rooms.</p>
                    <ul class="mt-4 space-y-2">
                        <li class="flex items-center text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Real-time availability
                        </li>
                        <li class="flex items-center text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Instant confirmation
                        </li>
                    </ul>
                </div>
                <div class="card p-8 rounded-lg feature-highlight" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Real-time Analytics</h3>
                    <p class="text-gray-600">Track and analyze workspace utilization with detailed insights.</p>
                    <ul class="mt-4 space-y-2">
                        <li class="flex items-center text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Usage patterns
                        </li>
                        <li class="flex items-center text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Peak hours analysis
                        </li>
                    </ul>
                </div>
                <div class="card p-8 rounded-lg feature-highlight" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="fas fa-bell text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Smart Notifications</h3>
                    <p class="text-gray-600">Stay informed with real-time updates about your bookings.</p>
                    <ul class="mt-4 space-y-2">
                        <li class="flex items-center text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Booking reminders
                        </li>
                        <li class="flex items-center text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Availability alerts
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="spacing"></div>
    </section>

    <!-- Resources Section -->
    <section id="resources" class="py-20">
        <div class="spacing"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900" data-aos="fade-up">Available Resources</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="card p-8 rounded-lg" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold mb-2 text-gray-900">Desks</h3>
                            <p class="text-gray-600">Individual workspaces for focused work</p>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-blue-600"><?php echo $available_desks; ?></p>
                            <p class="text-gray-600">Available</p>
                        </div>
                    </div>
                </div>
                <div class="card p-8 rounded-lg" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold mb-2 text-gray-900">Meeting Rooms</h3>
                            <p class="text-gray-600">Spaces for collaboration and meetings</p>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-green-600"><?php echo $available_rooms; ?></p>
                            <p class="text-gray-600">Available</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="spacing"></div>
    </section>

    <!-- Enhanced Footer -->
    <footer class="py-12 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 text-gray-900">DeskFlow</h3>
                    <p class="text-gray-600">Smart workspace management for the modern workplace.</p>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4 text-gray-900">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#about" class="text-gray-600 hover:text-blue-600 transition">About</a></li>
                        <li><a href="#features" class="text-gray-600 hover:text-blue-600 transition">Features</a></li>
                        <li><a href="#resources" class="text-gray-600 hover:text-blue-600 transition">Resources</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4 text-gray-900">Contact</h4>
                    <ul class="space-y-2">
                        <li class="text-gray-600"><i class="fas fa-envelope mr-2"></i>aa2037768@gmail.com</li>
                        <li class="text-gray-600"><i class="fas fa-phone mr-2"></i> +1 (555) 123-4567(fake)</li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-gray-200 text-center text-gray-600">
                <p>&copy; 2024 DeskFlow. All rights reserved.Created by Ayush Agrawal|Registration No:12322587</p>
            </div>
        </div>
    </footer>

    <script type="module">
        import { initScene } from './js/initScene.js';
        
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Initialize 3D scene
        initScene();

        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>

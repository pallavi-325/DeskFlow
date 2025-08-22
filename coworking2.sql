-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 17, 2025 at 06:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `coworking2`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `priority` enum('high','medium','low') NOT NULL DEFAULT 'medium',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `user_id`, `title`, `content`, `priority`, `created_at`) VALUES
(1, 6, 'welcome', 'welcome to deskflow', 'high', '2025-04-16 16:06:09'),
(2, 8, 'ayush', 'ayush agrawal', 'medium', '2025-04-16 17:28:13');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `resource_type` enum('desk','room') NOT NULL,
  `resource_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_id` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `resource_type_id` (`resource_type`,`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `resource_type`, `resource_id`, `start_time`, `end_time`, `total_amount`, `status`, `created_at`, `payment_method`, `payment_id`) VALUES
(14, 4, 'desk', 4, '2025-04-17 20:17:00', '2025-04-18 20:17:00', 24000.00, 'completed', '2025-04-16 14:48:45', 'cash', 'PAY67ffc3a880112'),
(15, 4, 'desk', 4, '2025-04-24 20:20:00', '2025-04-25 20:20:00', 24000.00, 'completed', '2025-04-16 14:50:48', 'cash', 'PAY67ffc3d013d0b'),
(16, 8, 'desk', 4, '2025-04-19 22:48:00', '2025-04-19 22:52:00', 66.67, 'completed', '2025-04-16 17:19:02', 'cash', 'PAY67ffe68d7b69d'),
(17, 8, 'desk', 6, '2025-04-20 10:17:00', '2025-04-21 10:17:00', 0.00, 'completed', '2025-04-17 04:50:01', 'cash', 'PAY6800889aa5362'),
(18, 8, 'room', 2, '2025-04-19 10:21:00', '2025-04-20 10:21:00', 0.00, 'pending', '2025-04-17 04:51:36', NULL, NULL),
(19, 8, 'room', 1, '2025-04-29 10:23:00', '2025-04-30 10:23:00', 2400.00, 'pending', '2025-04-17 04:53:15', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `desks`
--

CREATE TABLE `desks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `desk_no` varchar(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `rent_per_hour` decimal(10,2) NOT NULL,
  `status` enum('available','booked','maintenance') DEFAULT 'available',
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `desk_no` (`desk_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `desks`
--

INSERT INTO `desks` (`id`, `desk_no`, `rent_per_hour`, `status`, `created_at`, `updated_at`) VALUES
(1, '1', 100.00, 'booked', '2025-04-16 09:15:09', '2025-04-16 14:33:55'),
(4, '4', 1000.00, 'available', '2025-04-16 14:46:39', '2025-04-16 17:19:09'),
(5, '10', 100.00, 'available', '2025-04-16 17:45:20', '2025-04-16 17:45:20'),
(6, '20', 200.00, 'available', '2025-04-17 03:53:50', '2025-04-17 03:53:50');

-- --------------------------------------------------------

--
-- Table structure for table `payment_attempts`
--

CREATE TABLE `payment_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `razorpay_order_id` varchar(255) DEFAULT NULL,
  `razorpay_payment_id` varchar(255) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('desk','room') NOT NULL,
  `status` enum('available','booked','maintenance') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `name`, `type`, `status`, `created_at`) VALUES
(1, 'Desk A1', 'desk', 'available', '2025-04-16 14:27:00');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_no` varchar(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `room_type` enum('meeting','conference','training') NOT NULL,
  `capacity` int(11) NOT NULL,
  `rent_per_hour` decimal(10,2) NOT NULL,
  `status` enum('available','booked','maintenance') DEFAULT 'available',
  `description` text DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_no` (`room_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_no`, `room_type`, `capacity`, `rent_per_hour`, `status`, `created_at`, `updated_at`) VALUES
(1, '505', 'meeting', 10, 100.00, 'available', '2025-04-16 17:46:15', '2025-04-16 17:46:15'),
(2, '603', 'meeting', 60, 1000.00, 'available', '2025-04-17 03:54:14', '2025-04-17 03:54:14');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) NOT NULL DEFAULT 'Workspace Management System',
  `site_email` varchar(255) DEFAULT NULL,
  `site_phone` varchar(20) DEFAULT NULL,
  `min_booking_duration` int(11) NOT NULL DEFAULT 30,
  `max_booking_duration` int(11) NOT NULL DEFAULT 480,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `timezone` varchar(50) NOT NULL DEFAULT 'Asia/Kolkata',
  `maintenance_mode` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`site_name`, `site_email`, `site_phone`, `min_booking_duration`, `max_booking_duration`, `currency`, `timezone`) VALUES
('Workspace Management System', 'admin@workspace.com', '+91 1234567890', 30, 480, 'INR', 'Asia/Kolkata');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` enum('admin','manager') NOT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `phone`, `position`) VALUES
('admin', 'admin@workspace.com', '$2y$10$e1hzBEUd1un8zf/SfOKc1eaM8csyGFZfNHaoaX7ryAQkviSajMWlS', 'System Administrator', '+91 1234567890', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_attempts`
--
ALTER TABLE `payment_attempts`
  ADD CONSTRAINT `payment_attempts_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

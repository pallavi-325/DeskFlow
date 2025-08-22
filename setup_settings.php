<?php
include 'dbconnect.php';

// SQL to create settings table
$sql = "CREATE TABLE IF NOT EXISTS `settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `site_name` varchar(255) NOT NULL DEFAULT 'Workspace Management System',
    `min_booking_duration` int(11) NOT NULL DEFAULT 30,
    `max_booking_duration` int(11) NOT NULL DEFAULT 480,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings if not exists
INSERT INTO `settings` (`site_name`, `min_booking_duration`, `max_booking_duration`)
SELECT 'Workspace Management System', 30, 480
WHERE NOT EXISTS (SELECT 1 FROM `settings` LIMIT 1);";

// Execute the SQL
if (mysqli_multi_query($conn, $sql)) {
    echo "Settings table created successfully!";
    
    // Clear any remaining results
    while (mysqli_next_result($conn)) {
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    }
} else {
    echo "Error creating settings table: " . mysqli_error($conn);
}

mysqli_close($conn);
?> 
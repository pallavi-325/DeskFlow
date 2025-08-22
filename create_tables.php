<?php
include 'dbconnect.php';

// Create users table
$users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    position ENUM('admin', 'manager') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp()
)";

// Create resources table (parent table for desks and rooms)
$resources_table = "CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('desk', 'room') NOT NULL,
    status ENUM('available', 'booked', 'maintenance') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Create desks table
$desks_table = "CREATE TABLE IF NOT EXISTS desks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    floor INT NOT NULL,
    rent_per_hour DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
)";

// Create rooms table
$rooms_table = "CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    capacity INT NOT NULL,
    rent_per_hour DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
)";

// Create bookings table
$bookings_table = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_method ENUM('cash', 'online') DEFAULT NULL,
    payment_id VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
)";

// Create announcements table
$announcements_table = "CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('high', 'medium', 'low') NOT NULL DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Execute table creation
$tables = [
    'users' => $users_table,
    'resources' => $resources_table,
    'desks' => $desks_table,
    'rooms' => $rooms_table,
    'bookings' => $bookings_table,
    'announcements' => $announcements_table
];

foreach ($tables as $table_name => $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Table '$table_name' created successfully<br>";
    } else {
        echo "Error creating table '$table_name': " . mysqli_error($conn) . "<br>";
    }
}

// Insert sample data if tables are empty
$check_users = "SELECT COUNT(*) as count FROM users";
$result = mysqli_query($conn, $check_users);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    // Insert sample admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO users (username, password, position) VALUES ('admin', '$admin_password', 'admin')";
    mysqli_query($conn, $insert_admin);
    echo "Sample admin user created<br>";
}

// Check if resources exist
$check_resources = "SELECT COUNT(*) as count FROM resources";
$result = mysqli_query($conn, $check_resources);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    // Insert sample resources
    $sample_resources = [
        // Desks
        ['Desk A1', 'desk'],
        ['Desk A2', 'desk'],
        ['Desk B1', 'desk'],
        ['Desk B2', 'desk'],
        // Rooms
        ['Meeting Room 1', 'room'],
        ['Meeting Room 2', 'room']
    ];

    foreach ($sample_resources as $resource) {
        // First insert into resources table
        $insert_resource = "INSERT INTO resources (name, type) VALUES ('{$resource[0]}', '{$resource[1]}')";
        if (mysqli_query($conn, $insert_resource)) {
            $resource_id = mysqli_insert_id($conn);
            
            // Then insert into the appropriate child table
            if ($resource[1] == 'desk') {
                $insert_desk = "INSERT INTO desks (resource_id, floor, rent_per_hour) VALUES ($resource_id, 1, 50.00)";
                if (!mysqli_query($conn, $insert_desk)) {
                    echo "Error inserting desk: " . mysqli_error($conn) . "<br>";
                }
            } else {
                $insert_room = "INSERT INTO rooms (resource_id, capacity, rent_per_hour) VALUES ($resource_id, 10, 200.00)";
                if (!mysqli_query($conn, $insert_room)) {
                    echo "Error inserting room: " . mysqli_error($conn) . "<br>";
                }
            }
        } else {
            echo "Error inserting resource: " . mysqli_error($conn) . "<br>";
        }
    }
    echo "Sample resources created<br>";
}

echo "Database setup completed!";
?> 
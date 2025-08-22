<?php
include 'dbconnect.php';

// Reset admin password
$admin_password = 'admin123';
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// First check if admin exists
$check_query = "SELECT * FROM users WHERE username = 'admin'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    // Update existing admin
    $update_query = "UPDATE users SET password = ? WHERE username = 'admin'";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "s", $hashed_password);
    if (mysqli_stmt_execute($stmt)) {
        echo "Admin password reset successfully!<br>";
        echo "New password: " . $admin_password . "<br>";
        echo "Hashed password: " . $hashed_password . "<br>";
        
        // Verify the password
        $verify_query = "SELECT password FROM users WHERE username = 'admin'";
        $verify_result = mysqli_query($conn, $verify_query);
        $row = mysqli_fetch_assoc($verify_result);
        echo "Verification: " . (password_verify($admin_password, $row['password']) ? 'Password verified' : 'Password verification failed');
    } else {
        echo "Error resetting password: " . mysqli_error($conn);
    }
} else {
    // Create new admin
    $insert_query = "INSERT INTO users (username, password, position) VALUES ('admin', ?, 'admin')";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "s", $hashed_password);
    if (mysqli_stmt_execute($stmt)) {
        echo "Admin user created successfully!<br>";
        echo "Password: " . $admin_password . "<br>";
        echo "Hashed password: " . $hashed_password . "<br>";
        
        // Verify the password
        $verify_query = "SELECT password FROM users WHERE username = 'admin'";
        $verify_result = mysqli_query($conn, $verify_query);
        $row = mysqli_fetch_assoc($verify_result);
        echo "Verification: " . (password_verify($admin_password, $row['password']) ? 'Password verified' : 'Password verification failed');
    } else {
        echo "Error creating admin: " . mysqli_error($conn);
    }
}
?> 
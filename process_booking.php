<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Handle POST request for new bookings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $resource_type = $_POST['resource_type'];
    $resource_id = $_POST['resource_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $user_id = $_SESSION['user_id'];

    // Validate input
    if (empty($resource_type) || empty($resource_id) || empty($start_time) || empty($end_time)) {
        header("Location: create_booking.php?type=" . $resource_type . "&error=missing_fields");
        exit();
    }

    // Check if resource exists and is available
    $resource_query = "SELECT * FROM " . ($resource_type == 'desk' ? 'desks' : 'rooms') . " 
                      WHERE id = ? AND status = 'available'";
    $stmt = mysqli_prepare($conn, $resource_query);
    mysqli_stmt_bind_param($stmt, "i", $resource_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        header("Location: create_booking.php?type=" . $resource_type . "&error=resource_unavailable");
        exit();
    }

    // Check for overlapping bookings
    $overlap_query = "SELECT * FROM bookings 
                     WHERE resource_type = ? 
                     AND resource_id = ? 
                     AND status != 'cancelled' 
                     AND ((start_time <= ? AND end_time > ?) 
                     OR (start_time < ? AND end_time >= ?) 
                     OR (start_time >= ? AND end_time <= ?))";
    $stmt = mysqli_prepare($conn, $overlap_query);
    mysqli_stmt_bind_param($stmt, "sissssss", 
        $resource_type, 
        $resource_id, 
        $end_time, 
        $start_time, 
        $end_time, 
        $start_time, 
        $start_time, 
        $end_time
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        header("Location: create_booking.php?type=" . $resource_type . "&error=time_overlap");
        exit();
    }

    // Get resource details for amount calculation
    if ($resource_type == 'desk') {
        $resource_query = "SELECT id, rent_per_hour FROM desks WHERE id = ?";
    } else {
        $resource_query = "SELECT id, rent_per_hour FROM rooms WHERE id = ?";
    }
    $stmt = mysqli_prepare($conn, $resource_query);
    mysqli_stmt_bind_param($stmt, "i", $resource_id);
    mysqli_stmt_execute($stmt);
    $resource_result = mysqli_stmt_get_result($stmt);
    $resource = mysqli_fetch_assoc($resource_result);
    
    if (!$resource) {
        header("Location: create_booking.php?type=" . $resource_type . "&error=resource_not_found");
        exit();
    }
    
    // Calculate duration and amount
    $start = new DateTime($start_time);
    $end = new DateTime($end_time);
    $interval = $start->diff($end);
    $duration = $interval->h + ($interval->days * 24); // Convert days to hours
    
    // Calculate total amount
    $amount = $duration * $resource['rent_per_hour'];

    // Create booking
    $insert_query = "INSERT INTO bookings (user_id, resource_type, resource_id, start_time, end_time, total_amount, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "isissd", $user_id, $resource_type, $resource['id'], $start_time, $end_time, $amount);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: mainManager.php?success=booking_created");
    } else {
        header("Location: create_booking.php?type=" . $resource_type . "&error=db_error");
    }
    exit();
}

// Handle GET request for booking management (approve/reject/cancel)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $booking_id = $_GET['id'];
    
    // Validate booking exists
    $check_query = "SELECT * FROM bookings WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $booking_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        header("Location: mainManager.php?error=invalid_booking");
        exit();
    }

    $booking = mysqli_fetch_assoc($result);
    
    // Check permissions
    if ($_SESSION['position'] == 'manager' && $booking['user_id'] != $_SESSION['user_id']) {
        header("Location: mainManager.php?error=unauthorized");
        exit();
    }
    
    // Update booking status based on action
    $new_status = '';
    switch ($action) {
        case 'approve':
            $new_status = 'completed';
            break;
        case 'reject':
            $new_status = 'rejected';
            break;
        case 'cancel':
            $new_status = 'cancelled';
            break;
        default:
            header("Location: mainManager.php?error=invalid_action");
            exit();
    }
    
    $update_query = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $booking_id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: mainManager.php?success=booking_" . $action . "d");
    } else {
        header("Location: mainManager.php?error=db_error");
    }
} else {
    header("Location: mainManager.php");
}
exit();
?> 
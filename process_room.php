<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['position'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_no = mysqli_real_escape_string($conn, $_POST['room_no']);
    $capacity = intval($_POST['capacity']);
    $rent_per_hour = floatval($_POST['rent_per_hour']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Check if room number already exists
    $check_query = "SELECT id FROM rooms WHERE room_no = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $room_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Room number already exists!";
        header("Location: manage_rooms.php");
        exit();
    }

    // Insert new room
    $insert_query = "INSERT INTO rooms (room_no, capacity, rent_per_hour, status) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "sids", $room_no, $capacity, $rent_per_hour, $status);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Room added successfully!";
    } else {
        $_SESSION['error'] = "Error adding room: " . mysqli_error($conn);
    }

    header("Location: manage_rooms.php");
    exit();
} else {
    header("Location: manage_rooms.php");
    exit();
} 
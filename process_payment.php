<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get user ID
$username = $_SESSION['username'];
$user_query = "SELECT id FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);

if (!$user) {
    header('Location: mainManager.php?error=invalid_user');
    exit();
}

$user_id = $user['id'];

// Validate input
if (!isset($_POST['booking_id']) || !isset($_POST['payment_method'])) {
    header('Location: mainManager.php?error=invalid_payment');
    exit();
}

$booking_id = intval($_POST['booking_id']);
$payment_method = $_POST['payment_method'];

// Verify booking belongs to user and is pending
$query = "SELECT id, resource_type, resource_id FROM bookings WHERE id = ? AND user_id = ? AND status = 'pending'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header('Location: mainManager.php?error=invalid_booking');
    exit();
}

$booking = mysqli_fetch_assoc($result);

// Generate a unique payment ID
$payment_id = uniqid('PAY');

// Update booking status to completed
$update_query = "UPDATE bookings SET status = 'completed', payment_id = ?, payment_method = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($stmt, "ssi", $payment_id, $payment_method, $booking_id);
mysqli_stmt_execute($stmt);

// Update resource status to available
if ($booking['resource_type'] == 'desk') {
    $resource_query = "UPDATE desks SET status = 'available' WHERE id = ?";
} else {
    $resource_query = "UPDATE rooms SET status = 'available' WHERE id = ?";
}
$stmt = mysqli_prepare($conn, $resource_query);
mysqli_stmt_bind_param($stmt, "i", $booking['resource_id']);
mysqli_stmt_execute($stmt);

// Redirect to success page
header("Location: payment_success.php?booking_id=" . $booking_id);
exit(); 
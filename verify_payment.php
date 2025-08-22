<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Get POST data
$booking_id = $_POST['booking_id'];
$razorpay_payment_id = $_POST['razorpay_payment_id'];
$razorpay_order_id = $_POST['razorpay_order_id'];
$razorpay_signature = $_POST['razorpay_signature'];

// Validate input
if (empty($booking_id) || empty($razorpay_payment_id) || empty($razorpay_order_id) || empty($razorpay_signature)) {
    echo json_encode(['error' => 'Invalid payment data']);
    exit();
}

// Initialize Razorpay
require 'vendor/autoload.php';
use Razorpay\Api\Api;

$api = new Api('YOUR_RAZORPAY_KEY_ID', 'YOUR_RAZORPAY_KEY_SECRET');

// Verify payment signature
$attributes = array(
    'razorpay_order_id' => $razorpay_order_id,
    'razorpay_payment_id' => $razorpay_payment_id,
    'razorpay_signature' => $razorpay_signature
);

try {
    $api->utility->verifyPaymentSignature($attributes);
    
    // Update booking status
    $update_query = "UPDATE bookings SET 
                    payment_status = 'paid',
                    booking_status = 'confirmed',
                    razorpay_payment_id = ?
                    WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $razorpay_payment_id, $booking_id);
    mysqli_stmt_execute($stmt);

    // Record payment attempt
    $payment_query = "INSERT INTO payment_attempts (booking_id, payment_id, status) 
                      VALUES (?, ?, 'success')";
    $stmt = mysqli_prepare($conn, $payment_query);
    mysqli_stmt_bind_param($stmt, "is", $booking_id, $razorpay_payment_id);
    mysqli_stmt_execute($stmt);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Record failed payment attempt
    $payment_query = "INSERT INTO payment_attempts (booking_id, payment_id, status) 
                      VALUES (?, ?, 'failed')";
    $stmt = mysqli_prepare($conn, $payment_query);
    mysqli_stmt_bind_param($stmt, "is", $booking_id, $razorpay_payment_id);
    mysqli_stmt_execute($stmt);

    echo json_encode(['error' => 'Payment verification failed']);
}
?> 
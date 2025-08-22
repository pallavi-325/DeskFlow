<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['position'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (!isset($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit();
}

$room_id = $_GET['id'];
$sql = "SELECT * FROM rooms WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $room_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$room = mysqli_fetch_assoc($result);

if (!$room) {
    header('HTTP/1.1 404 Not Found');
    exit();
}

header('Content-Type: application/json');
echo json_encode($room); 
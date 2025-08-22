<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is manager
if (!isset($_SESSION['username']) || $_SESSION['position'] != 'manager') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    
    if (empty($title) || empty($content)) {
        header("Location: mainManager.php?error=empty_fields");
        exit();
    }
    
    // Insert announcement
    $sql = "INSERT INTO announcements (title, content, user_id, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: mainManager.php?success=announcement_created");
    } else {
        header("Location: mainManager.php?error=db_error");
    }
} else {
    header("Location: mainManager.php");
}
exit(); 
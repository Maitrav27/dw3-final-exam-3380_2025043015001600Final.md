<?php
require_once 'db.php';
require_once 'session.php';
requireLogin();

if (isset($_GET['id'])) {
    $songId = $_GET['id'];
    
    // First, get the cover image filename
    $stmt = $pdo->prepare("SELECT cover_image FROM songs WHERE id = ? AND user_id = ?");
    $stmt->execute([$songId, $_SESSION['user_id']]);
    $song = $stmt->fetch();
    
    if ($song) {
        // Delete the file
        $filePath = 'uploads/' . $song['cover_image'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete the database record
        $stmt = $pdo->prepare("DELETE FROM songs WHERE id = ? AND user_id = ?");
        $stmt->execute([$songId, $_SESSION['user_id']]);
    }
}

header('Location: dashboard.php');
exit();
?> 
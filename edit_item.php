<?php
require_once 'db.php';
require_once 'session.php';
requireLogin();

$error = '';
$success = '';

// Get song details
if (isset($_GET['id'])) {
    $songId = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM songs WHERE id = ? AND user_id = ?");
    $stmt->execute([$songId, $_SESSION['user_id']]);
    $song = $stmt->fetch();

    if (!$song) {
        header('Location: dashboard.php');
        exit();
    }
} else {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $artist = trim($_POST['artist'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    
    // Validate input
    if (empty($title) || empty($artist) || empty($genre)) {
        $error = "All fields are required";
    } else {
        try {
            // If a new image is uploaded
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['cover_image'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxFileSize = 5 * 1024 * 1024; // 5MB
                
                if ($file['size'] > $maxFileSize) {
                    $error = "File size must be less than 5MB";
                } elseif (!in_array($file['type'], $allowedTypes)) {
                    $error = "Only JPG, PNG, and GIF images are allowed";
                } else {
                    $uploadDir = 'uploads/';
                    $fileName = uniqid() . '_' . basename($file['name']);
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        // Delete old image
                        $oldImagePath = $uploadDir . $song['cover_image'];
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                        
                        // Update with new image
                        $stmt = $pdo->prepare("UPDATE songs SET title = ?, artist = ?, genre = ?, cover_image = ? WHERE id = ? AND user_id = ?");
                        $stmt->execute([$title, $artist, $genre, $fileName, $songId, $_SESSION['user_id']]);
                        $success = "Song updated successfully!";
                    } else {
                        $error = "Failed to upload new image. Please try again.";
                    }
                }
            } else {
                // Update without changing image
                $stmt = $pdo->prepare("UPDATE songs SET title = ?, artist = ?, genre = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$title, $artist, $genre, $songId, $_SESSION['user_id']]);
                $success = "Song updated successfully!";
            }
        } catch (PDOException $e) {
            $error = "Failed to update song. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Song - Music Playlist</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="form-container">
        <h1>Edit Song</h1>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Song Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($song['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="artist">Artist:</label>
                <input type="text" id="artist" name="artist" value="<?php echo htmlspecialchars($song['artist']); ?>" required>
            </div>
            <div class="form-group">
                <label for="genre">Genre:</label>
                <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($song['genre']); ?>" required>
            </div>
            <div class="form-group">
                <label>Current Album Cover:</label>
                <img src="uploads/<?php echo htmlspecialchars($song['cover_image']); ?>" alt="Current Album Cover" class="current-image">
            </div>
            <div class="form-group">
                <label for="cover_image">New Album Cover (optional):</label>
                <input type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/gif">
                <small class="file-info">Leave empty to keep current image. Supported formats: JPG, PNG, GIF</small>
            </div>
            <div class="form-actions">
                <button type="submit">Update Song</button>
                <a href="dashboard.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html> 
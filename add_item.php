<?php
require_once 'db.php';
require_once 'session.php';
requireLogin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $artist = trim($_POST['artist'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    
    // Validate input
    if (empty($title) || empty($artist) || empty($genre)) {
        $error = "All fields are required";
    } elseif (!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select an album cover image";
    } else {
        $file = $_FILES['cover_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        // Check file size
        if ($file['size'] > $maxFileSize) {
            $error = "File size must be less than 5MB";
        } elseif (!in_array($file['type'], $allowedTypes)) {
            $error = "Only JPG, PNG, and GIF images are allowed";
        } else {
            $uploadDir = 'uploads/';
            
            // Ensure upload directory exists and is writable
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (!is_writable($uploadDir)) {
                $error = "Upload directory is not writable. Please check permissions.";
            } else {
                $fileName = uniqid() . '_' . basename($file['name']);
                $targetPath = $uploadDir . $fileName;
                
                // Debug information
                error_log("Attempting to upload file: " . $targetPath);
                error_log("File type: " . $file['type']);
                error_log("File size: " . $file['size']);
                error_log("Temporary file: " . $file['tmp_name']);
                
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO songs (user_id, title, artist, genre, cover_image) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$_SESSION['user_id'], $title, $artist, $genre, $fileName]);
                        header('Location: dashboard.php?added=1');
                        exit();
                    } catch (PDOException $e) {
                        // If database insert fails, remove the uploaded file
                        if (file_exists($targetPath)) {
                            unlink($targetPath);
                        }
                        $error = "Failed to add song to database. Please try again.";
                        error_log("Database error: " . $e->getMessage());
                    }
                } else {
                    $error = "Failed to upload image. Error: " . $file['error'];
                    error_log("Upload error: " . $file['error']);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Song - Music Playlist</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="form-container">
        <h1>Add New Song</h1>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Song Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="artist">Artist:</label>
                <input type="text" id="artist" name="artist" value="<?php echo htmlspecialchars($_POST['artist'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="genre">Genre:</label>
                <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($_POST['genre'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="cover_image">Album Cover (max 5MB):</label>
                <input type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/gif" required>
                <small class="file-info">Supported formats: JPG, PNG, GIF</small>
            </div>
            <div class="form-actions">
                <button type="submit">Add Song</button>
                <a href="dashboard.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html> 
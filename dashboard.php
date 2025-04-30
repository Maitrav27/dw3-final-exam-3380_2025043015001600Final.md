<?php
require_once 'db.php';
require_once 'session.php';
requireLogin();

$stmt = $pdo->prepare("SELECT * FROM songs WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$songs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Music Playlist</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <div class="header-title">
                <svg class="apple-logo" viewBox="0 0 24 24" width="24" height="24">
                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                </svg>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </header>
        
        <?php if (isset($_GET['added'])): ?>
            <div class="success">Song added successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="success">Song updated successfully!</div>
        <?php endif; ?>
        
        <div class="add-song">
            <a href="add_item.php" class="btn">Add New Song</a>
        </div>

        <div class="songs-grid">
            <?php if (empty($songs)): ?>
                <div class="no-songs">
                    <p>No songs in your playlist yet. Add your first song!</p>
                </div>
            <?php else: ?>
                <?php foreach ($songs as $song): ?>
                    <div class="song-card">
                        <img src="uploads/<?php echo htmlspecialchars($song['cover_image']); ?>" alt="Album Cover">
                        <h3><?php echo htmlspecialchars($song['title']); ?></h3>
                        <p>Artist: <?php echo htmlspecialchars($song['artist']); ?></p>
                        <p>Genre: <?php echo htmlspecialchars($song['genre']); ?></p>
                        <div class="actions">
                            <a href="edit_item.php?id=<?php echo $song['id']; ?>" class="edit-btn">Edit</a>
                            <a href="delete_item.php?id=<?php echo $song['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this song?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 
<?php
require_once __DIR__ . '/../config.php';
require_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    csrf_verify();
    try {
        $newId = handle_upload('file', current_user()['id']);
        if ($newId) {
            flash_set('success', 'File uploaded.');
        } else {
            $errors[] = 'No file was selected.';
        }
        if (empty($errors)) {
            redirect('media.php');
        }
    } catch (RuntimeException $e) {
        $errors[] = $e->getMessage();
    }
}

$pageTitle = 'Media Library';
require __DIR__ . '/includes/header.php';

$media = db()->query(
    "SELECT media.*, users.username FROM media
     LEFT JOIN users ON media.uploaded_by = users.id
     ORDER BY media.uploaded_at DESC"
)->fetchAll();
?>

<h1>Media Library</h1>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><?= h($e) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="inline-form">
    <?= csrf_field() ?>
    <input type="file" name="file" required>
    <button type="submit" name="upload" value="1" class="btn">Upload</button>
</form>
<p class="hint">Allowed: jpg, jpeg, png, gif, webp, pdf. Max 5MB.</p>

<div class="media-grid">
<?php foreach ($media as $m): ?>
    <div class="media-item">
        <?php if (in_array(pathinfo($m['filename'], PATHINFO_EXTENSION), ['jpg','jpeg','png','gif','webp'])): ?>
            <img src="<?= h(UPLOAD_URL . '/' . $m['filename']) ?>" alt="<?= h($m['original_name']) ?>">
        <?php else: ?>
            <div class="media-file-icon">📄</div>
        <?php endif; ?>
        <div class="media-name" title="<?= h($m['original_name']) ?>"><?= h($m['original_name']) ?></div>
        <div class="media-meta"><?= h(round($m['size_bytes'] / 1024)) ?> KB &middot; <?= h($m['username'] ?? 'Unknown') ?></div>
        <div class="media-url">
            <input type="text" readonly value="<?= h(UPLOAD_URL . '/' . $m['filename']) ?>" onclick="this.select()">
        </div>
        <form method="post" action="media_delete.php" onsubmit="return confirm('Delete this file?');">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $m['id'] ?>">
            <button type="submit" class="link-danger">Delete</button>
        </form>
    </div>
<?php endforeach; ?>
<?php if (empty($media)): ?>
    <p>No media uploaded yet.</p>
<?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

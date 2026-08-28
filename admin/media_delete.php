<?php
require_once __DIR__ . '/../config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);

    $stmt = db()->prepare('SELECT filename FROM media WHERE id = ?');
    $stmt->execute([$id]);
    $filename = $stmt->fetchColumn();

    if ($filename) {
        $path = UPLOAD_DIR . '/' . $filename;
        if (is_file($path)) {
            unlink($path);
        }
        $stmt = db()->prepare('DELETE FROM media WHERE id = ?');
        $stmt->execute([$id]);
        flash_set('success', 'File deleted.');
    }
}

redirect('media.php');

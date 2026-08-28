<?php
require_once __DIR__ . '/../config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = db()->prepare('DELETE FROM pages WHERE id = ?');
    $stmt->execute([$id]);
    flash_set('success', 'Page deleted.');
}

redirect('pages.php');

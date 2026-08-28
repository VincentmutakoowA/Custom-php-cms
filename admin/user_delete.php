<?php
require_once __DIR__ . '/../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);

    if ($id === current_user()['id']) {
        flash_set('error', 'You cannot delete your own account.');
    } else {
        $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        flash_set('success', 'User deleted.');
    }
}

redirect('users.php');

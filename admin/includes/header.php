<?php
require_once __DIR__ . '/../../config.php';
require_login();
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' - ' : '' ?>Admin - <?= h(SITE_NAME) ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body class="admin-body">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-brand"><?= h(SITE_NAME) ?></div>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="posts.php">Posts</a>
                <a href="pages.php">Pages</a>
                <a href="categories.php">Categories</a>
                <a href="media.php">Media</a>
                <?php if (is_admin()): ?>
                    <a href="users.php">Users</a>
                <?php endif; ?>
                <hr>
                <a href="../index.php" target="_blank">View Site &#8599;</a>
                <a href="logout.php">Logout (<?= h($user['username']) ?>)</a>
            </nav>
        </aside>
        <main class="admin-main">
            <?php $flash = flash_get(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
            <?php endif; ?>
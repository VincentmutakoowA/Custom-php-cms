<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' - ' : '' ?><?= h(SITE_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="wrap">
        <a class="brand" href="index.php"><?= h(SITE_NAME) ?></a>
        <nav>
            <a href="index.php">Home</a>
            <?php
            $navPages = db()->query(
                "SELECT title, slug FROM pages WHERE status = 'published' ORDER BY title ASC"
            )->fetchAll();
            foreach ($navPages as $navPage):
            ?>
                <a href="page.php?slug=<?= urlencode($navPage['slug']) ?>"><?= h($navPage['title']) ?></a>
            <?php endforeach; ?>
            <?php if (is_logged_in()): ?>
                <a href="admin/index.php">Admin</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main 
/*class="wrap"*/
>
<?php $flash = flash_get(); ?>
<?php if ($flash): ?>
    <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
<?php endif; ?>

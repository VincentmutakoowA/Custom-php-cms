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

            <div>
                <input type="checkbox" id="active" >
                <label for="active" class="menu-btn"><span></span></label>
                <label for="active" class="close"></label>
                <nav class="wrapper">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="posts.php">Posts</a></li>
                        <?php if (is_logged_in()): ?>
                            <li><a href="admin/index.php">Admin</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>

        </div>
    </header>
    <main /*class="wrap" * />
    <?php $flash = flash_get(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
    <?php endif; ?>
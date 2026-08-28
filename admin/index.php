<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';

$counts = [
    'posts' => db()->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
    'published' => db()->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn(),
    'drafts' => db()->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn(),
    'pages' => db()->query('SELECT COUNT(*) FROM pages')->fetchColumn(),
    'categories' => db()->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
    'media' => db()->query('SELECT COUNT(*) FROM media')->fetchColumn(),
    'users' => db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
];

$recentPosts = db()->query('SELECT id, title, status, created_at FROM posts ORDER BY created_at DESC LIMIT 5')->fetchAll();
?>

<h1>Dashboard</h1>
<p>Welcome back, <?= h(current_user()['username']) ?>.</p>

<div class="stat-grid">
    <div class="stat-card"><span class="stat-num"><?= $counts['posts'] ?></span><span>Total Posts</span></div>
    <div class="stat-card"><span class="stat-num"><?= $counts['published'] ?></span><span>Published</span></div>
    <div class="stat-card"><span class="stat-num"><?= $counts['drafts'] ?></span><span>Drafts</span></div>
    <div class="stat-card"><span class="stat-num"><?= $counts['pages'] ?></span><span>Pages</span></div>
    <div class="stat-card"><span class="stat-num"><?= $counts['categories'] ?></span><span>Categories</span></div>
    <div class="stat-card"><span class="stat-num"><?= $counts['media'] ?></span><span>Media Files</span></div>
    <?php if (is_admin()): ?>
    <div class="stat-card"><span class="stat-num"><?= $counts['users'] ?></span><span>Users</span></div>
    <?php endif; ?>
</div>

<h2>Recent Posts</h2>
<table class="admin-table">
    <thead><tr><th>Title</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($recentPosts as $p): ?>
        <tr>
            <td><a href="post_form.php?id=<?= $p['id'] ?>"><?= h($p['title']) ?></a></td>
            <td><span class="badge badge-<?= h($p['status']) ?>"><?= h($p['status']) ?></span></td>
            <td><?= h(date('M j, Y', strtotime($p['created_at']))) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($recentPosts)): ?>
        <tr><td colspan="3">No posts yet. <a href="post_form.php">Create one</a>.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/includes/footer.php'; ?>

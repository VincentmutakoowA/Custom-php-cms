<?php
$pageTitle = 'Posts';
require __DIR__ . '/includes/header.php';

$posts = db()->query(
    "SELECT posts.*, users.username AS author_name, categories.name AS category_name
     FROM posts
     LEFT JOIN users ON posts.author_id = users.id
     LEFT JOIN categories ON posts.category_id = categories.id
     ORDER BY posts.created_at DESC"
)->fetchAll();
?>

<div class="admin-toolbar">
    <h1>Posts</h1>
    <a class="btn" href="post_form.php">+ New Post</a>
</div>

<table class="admin-table">
    <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Status</th><th>Date</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($posts as $p): ?>
        <tr>
            <td><a href="post_form.php?id=<?= $p['id'] ?>"><?= h($p['title']) ?></a></td>
            <td><?= h($p['author_name'] ?? '—') ?></td>
            <td><?= h($p['category_name'] ?? '—') ?></td>
            <td><span class="badge badge-<?= h($p['status']) ?>"><?= h($p['status']) ?></span></td>
            <td><?= h(date('M j, Y', strtotime($p['created_at']))) ?></td>
            <td class="actions">
                <a href="post_form.php?id=<?= $p['id'] ?>">Edit</a>
                <form method="post" action="post_delete.php" onsubmit="return confirm('Delete this post?');" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="link-danger">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($posts)): ?>
        <tr><td colspan="6">No posts yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/includes/footer.php'; ?>

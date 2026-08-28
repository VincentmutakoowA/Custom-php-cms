<?php
$pageTitle = 'Pages';
require __DIR__ . '/includes/header.php';

$pages = db()->query(
    "SELECT pages.*, users.username AS author_name FROM pages
     LEFT JOIN users ON pages.author_id = users.id
     ORDER BY pages.created_at DESC"
)->fetchAll();
?>

<div class="admin-toolbar">
    <h1>Pages</h1>
    <a class="btn" href="page_form.php">+ New Page</a>
</div>

<table class="admin-table">
    <thead><tr><th>Title</th><th>Author</th><th>Status</th><th>Date</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($pages as $p): ?>
        <tr>
            <td><a href="page_form.php?id=<?= $p['id'] ?>"><?= h($p['title']) ?></a></td>
            <td><?= h($p['author_name'] ?? '—') ?></td>
            <td><span class="badge badge-<?= h($p['status']) ?>"><?= h($p['status']) ?></span></td>
            <td><?= h(date('M j, Y', strtotime($p['created_at']))) ?></td>
            <td class="actions">
                <a href="page_form.php?id=<?= $p['id'] ?>">Edit</a>
                <form method="post" action="page_delete.php" onsubmit="return confirm('Delete this page?');" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="link-danger">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($pages)): ?>
        <tr><td colspan="5">No pages yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/includes/footer.php'; ?>

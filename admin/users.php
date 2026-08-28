<?php
require_once __DIR__ . '/../config.php';
require_admin();

$pageTitle = 'Users';
require __DIR__ . '/includes/header.php';

$users = db()->query('SELECT id, username, email, role, created_at FROM users ORDER BY created_at ASC')->fetchAll();
?>

<div class="admin-toolbar">
    <h1>Users</h1>
    <a class="btn" href="user_form.php">+ New User</a>
</div>

<table class="admin-table">
    <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Joined</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><a href="user_form.php?id=<?= $u['id'] ?>"><?= h($u['username']) ?></a></td>
            <td><?= h($u['email']) ?></td>
            <td><span class="badge"><?= h($u['role']) ?></span></td>
            <td><?= h(date('M j, Y', strtotime($u['created_at']))) ?></td>
            <td class="actions">
                <a href="user_form.php?id=<?= $u['id'] ?>">Edit</a>
                <?php if ($u['id'] != current_user()['id']): ?>
                <form method="post" action="user_delete.php" onsubmit="return confirm('Delete this user?');" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button type="submit" class="link-danger">Delete</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/includes/footer.php'; ?>

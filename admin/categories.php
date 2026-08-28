<?php
require_once __DIR__ . '/../config.php';
require_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $errors[] = 'Category name is required.';
    } else {
        $slug = unique_slug('categories', slugify($name));
        try {
            $stmt = db()->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
            $stmt->execute([$name, $slug]);
            flash_set('success', 'Category created.');
            redirect('categories.php');
        } catch (PDOException $e) {
            $errors[] = 'A category with that name already exists.';
        }
    }
}

$pageTitle = 'Categories';
require __DIR__ . '/includes/header.php';

$categories = db()->query(
    "SELECT categories.*, (SELECT COUNT(*) FROM posts WHERE posts.category_id = categories.id) AS post_count
     FROM categories ORDER BY categories.name ASC"
)->fetchAll();
?>

<h1>Categories</h1>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><?= h($e) ?></div>
<?php endforeach; ?>

<form method="post" class="inline-form">
    <?= csrf_field() ?>
    <input type="text" name="name" placeholder="New category name" required>
    <button type="submit" class="btn">Add Category</button>
</form>

<table class="admin-table">
    <thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($categories as $c): ?>
        <tr>
            <td><a href="category_form.php?id=<?= $c['id'] ?>"><?= h($c['name']) ?></a></td>
            <td><?= h($c['slug']) ?></td>
            <td><?= $c['post_count'] ?></td>
            <td class="actions">
                <a href="category_form.php?id=<?= $c['id'] ?>">Edit</a>
                <form method="post" action="category_delete.php" onsubmit="return confirm('Delete this category? Posts in it will become uncategorized.');" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button type="submit" class="link-danger">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($categories)): ?>
        <tr><td colspan="4">No categories yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/includes/footer.php'; ?>

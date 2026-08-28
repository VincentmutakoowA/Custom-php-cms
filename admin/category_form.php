<?php
require_once __DIR__ . '/../config.php';
require_login();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM categories WHERE id = ?');
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    flash_set('error', 'Category not found.');
    redirect('categories.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $errors[] = 'Category name is required.';
    } else {
        $slug = unique_slug('categories', slugify($name), $id);
        $stmt = db()->prepare('UPDATE categories SET name=?, slug=? WHERE id=?');
        $stmt->execute([$name, $slug, $id]);
        flash_set('success', 'Category updated.');
        redirect('categories.php');
    }
}

$pageTitle = 'Edit Category';
require __DIR__ . '/includes/header.php';
?>

<h1>Edit Category</h1>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><?= h($e) ?></div>
<?php endforeach; ?>

<form method="post" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <label>Name
        <input type="text" name="name" value="<?= h($category['name']) ?>" required>
    </label>
    <div class="form-actions">
        <button type="submit" class="btn">Save</button>
        <a href="categories.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>

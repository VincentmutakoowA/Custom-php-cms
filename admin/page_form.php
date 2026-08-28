<?php
require_once __DIR__ . '/../config.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : null);
$page = ['id' => null, 'title' => '', 'content' => '', 'status' => 'draft'];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM pages WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash_set('error', 'Page not found.');
        redirect('pages.php');
    }
    $page = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $page['title'] = trim($_POST['title'] ?? '');
    $page['content'] = $_POST['content'] ?? '';
    $page['status'] = in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'draft';

    if ($page['title'] === '') {
        $errors[] = 'Title is required.';
    }

    if (empty($errors)) {
        if ($id) {
            $slug = unique_slug('pages', slugify($page['title']), $id);
            $stmt = db()->prepare("UPDATE pages SET title=?, slug=?, content=?, status=?, updated_at=datetime('now') WHERE id=?");
            $stmt->execute([$page['title'], $slug, $page['content'], $page['status'], $id]);
            flash_set('success', 'Page updated.');
        } else {
            $slug = unique_slug('pages', slugify($page['title']));
            $stmt = db()->prepare('INSERT INTO pages (title, slug, content, status, author_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$page['title'], $slug, $page['content'], $page['status'], current_user()['id']]);
            flash_set('success', 'Page created.');
        }
        redirect('pages.php');
    }
}

$pageTitle = $id ? 'Edit Page' : 'New Page';
require __DIR__ . '/includes/header.php';
?>

<h1><?= $id ? 'Edit Page' : 'New Page' ?></h1>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><?= h($e) ?></div>
<?php endforeach; ?>

<form method="post" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= h((string) $id) ?>">

    <label>Title
        <input type="text" name="title" value="<?= h($page['title']) ?>" required>
    </label>

    <label>Content (HTML allowed)
        <textarea name="content" rows="14"><?= h($page['content']) ?></textarea>
    </label>

    <label>Status
        <select name="status">
            <option value="draft" <?= $page['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="published" <?= $page['status'] === 'published' ? 'selected' : '' ?>>Published</option>
        </select>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn">Save Page</button>
        <a href="pages.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>

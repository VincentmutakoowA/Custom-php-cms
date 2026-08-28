<?php
require_once __DIR__ . '/../config.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : null);
$post = ['id' => null, 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'status' => 'draft', 'category_id' => null, 'featured_image' => null];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash_set('error', 'Post not found.');
        redirect('posts.php');
    }
    $post = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $post['title'] = trim($_POST['title'] ?? '');
    $post['excerpt'] = trim($_POST['excerpt'] ?? '');
    $post['content'] = $_POST['content'] ?? '';
    $post['status'] = in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
    $post['category_id'] = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;

    if ($post['title'] === '') {
        $errors[] = 'Title is required.';
    }

    if (empty($errors)) {
        try {
            $newImageId = handle_upload('featured_image', current_user()['id']);
            $featuredImage = $post['featured_image'];
            if ($newImageId) {
                $stmt = db()->prepare('SELECT filename FROM media WHERE id = ?');
                $stmt->execute([$newImageId]);
                $featuredImage = $stmt->fetchColumn();
            }

            if ($id) {
                $baseSlug = slugify($post['title']);
                $slug = unique_slug('posts', $baseSlug, $id);
                $stmt = db()->prepare(
                    'UPDATE posts SET title=?, slug=?, excerpt=?, content=?, status=?, category_id=?, featured_image=?, updated_at=datetime(\'now\') WHERE id=?'
                );
                $stmt->execute([$post['title'], $slug, $post['excerpt'], $post['content'], $post['status'], $post['category_id'], $featuredImage, $id]);
                flash_set('success', 'Post updated.');
            } else {
                $baseSlug = slugify($post['title']);
                $slug = unique_slug('posts', $baseSlug);
                $stmt = db()->prepare(
                    'INSERT INTO posts (title, slug, excerpt, content, status, category_id, author_id, featured_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$post['title'], $slug, $post['excerpt'], $post['content'], $post['status'], $post['category_id'], current_user()['id'], $featuredImage]);
                flash_set('success', 'Post created.');
            }
            redirect('posts.php');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$categories = db()->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
$pageTitle = $id ? 'Edit Post' : 'New Post';
require __DIR__ . '/includes/header.php';
?>

<h1><?= $id ? 'Edit Post' : 'New Post' ?></h1>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><?= h($e) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= h((string) $id) ?>">

    <label>Title
        <input type="text" name="title" value="<?= h($post['title']) ?>" required>
    </label>

    <label>Category
        <select name="category_id">
            <option value="">— None —</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $post['category_id'] == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Excerpt (optional — auto-generated if left blank)
        <textarea name="excerpt" rows="2"><?= h($post['excerpt']) ?></textarea>
    </label>

    <label>Content (HTML allowed)
        <textarea name="content" rows="14"><?= h($post['content']) ?></textarea>
    </label>

    <label>Featured Image
        <input type="file" name="featured_image" accept="image/*">
        <?php if (!empty($post['featured_image'])): ?>
            <div class="current-image">
                <img src="<?= h(UPLOAD_URL . '/' . $post['featured_image']) ?>" alt="">
                <span>Current image (upload a new one to replace)</span>
            </div>
        <?php endif; ?>
    </label>

    <label>Status
        <select name="status">
            <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
        </select>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn">Save Post</button>
        <a href="posts.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>

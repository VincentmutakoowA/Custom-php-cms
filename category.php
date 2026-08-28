<?php
require_once __DIR__ . '/config.php';

$slug = $_GET['slug'] ?? '';
$catStmt = db()->prepare('SELECT * FROM categories WHERE slug = ?');
$catStmt->execute([$slug]);
$category = $catStmt->fetch();

if (!$category) {
    http_response_code(404);
    $pageTitle = 'Not Found';
    require __DIR__ . '/includes/site_header.php';
    echo '<h1>Category not found</h1>';
    require __DIR__ . '/includes/site_footer.php';
    exit;
}

$stmt = db()->prepare(
    "SELECT posts.*, users.username AS author_name
     FROM posts LEFT JOIN users ON posts.author_id = users.id
     WHERE posts.category_id = ? AND posts.status = 'published'
     ORDER BY posts.created_at DESC"
);
$stmt->execute([$category['id']]);
$posts = $stmt->fetchAll();

$pageTitle = 'Category: ' . $category['name'];
require __DIR__ . '/includes/site_header.php';
?>

<h1>Category: <?= h($category['name']) ?></h1>

<?php if (empty($posts)): ?>
    <p>No posts in this category yet.</p>
<?php endif; ?>

<div class="post-grid">
<?php foreach ($posts as $post): ?>
    <article class="post-card">
        <h2><a href="post.php?slug=<?= urlencode($post['slug']) ?>"><?= h($post['title']) ?></a></h2>
        <div class="meta">
            By <?= h($post['author_name'] ?? 'Unknown') ?> &middot; <?= h(date('M j, Y', strtotime($post['created_at']))) ?>
        </div>
        <p><?= h($post['excerpt'] ?: make_excerpt($post['content'])) ?></p>
        <a class="read-more" href="post.php?slug=<?= urlencode($post['slug']) ?>">Read more &rarr;</a>
    </article>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/site_footer.php'; ?>

<?php
require_once __DIR__ . '/config.php';

$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare(
    "SELECT posts.*, users.username AS author_name, categories.name AS category_name, categories.slug AS category_slug
     FROM posts
     LEFT JOIN users ON posts.author_id = users.id
     LEFT JOIN categories ON posts.category_id = categories.id
     WHERE posts.slug = ? AND posts.status = 'published'"
);
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Not Found';
    require __DIR__ . '/includes/site_header.php';
    echo '<h1>Post not found</h1><p>The post you are looking for does not exist or is not published.</p>';
    require __DIR__ . '/includes/site_footer.php';
    exit;
}

$pageTitle = $post['title'];
require __DIR__ . '/includes/site_header.php';
?>

<article class="single-post">
    <h1><?= h($post['title']) ?></h1>
    <div class="meta">
        By <?= h($post['author_name'] ?? 'Unknown') ?>
        <?php if ($post['category_name']): ?>
            in <a href="category.php?slug=<?= urlencode($post['category_slug']) ?>"><?= h($post['category_name']) ?></a>
        <?php endif; ?>
        &middot; <?= h(date('M j, Y', strtotime($post['created_at']))) ?>
    </div>
    <?php if ($post['featured_image']): ?>
        <img class="featured" src="<?= h(UPLOAD_URL . '/' . $post['featured_image']) ?>" alt="<?= h($post['title']) ?>">
    <?php endif; ?>
    <div class="content"><?= $post['content'] ?></div>
</article>

<p><a href="index.php">&larr; Back to all posts</a></p>

<?php require __DIR__ . '/includes/site_footer.php'; ?>

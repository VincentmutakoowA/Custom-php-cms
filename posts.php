<?php
require_once __DIR__ . '/config.php';

$perPage = 6;
$page = max(1, (int) ($_GET['p'] ?? 1));
$offset = ($page - 1) * $perPage;

$total = db()->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();

$stmt = db()->prepare(
    "SELECT posts.*, users.username AS author_name, categories.name AS category_name, categories.slug AS category_slug
     FROM posts
     LEFT JOIN users ON posts.author_id = users.id
     LEFT JOIN categories ON posts.category_id = categories.id
     WHERE posts.status = 'published'
     ORDER BY posts.created_at DESC
     LIMIT ? OFFSET ?"
);
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

$pageTitle = 'Home';
require __DIR__ . '/includes/site_header.php';
?>

<div class="wrap">
    <h1>All Posts</h1>

    <?php if (empty($posts)): ?>
        <p>No posts published yet.</p>
    <?php endif; ?>

    <div class="post-grid">
        <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <?php if ($post['featured_image']): ?>
                    <a href="post.php?slug=<?= urlencode($post['slug']) ?>">
                        <img src="<?= h(UPLOAD_URL . '/' . $post['featured_image']) ?>" alt="<?= h($post['title']) ?>">
                    </a>
                <?php endif; ?>
                <h2><a href="post.php?slug=<?= urlencode($post['slug']) ?>"><?= h($post['title']) ?></a></h2>
                <div class="meta">
                    By <?= h($post['author_name'] ?? 'Unknown') ?>
                    <?php if ($post['category_name']): ?>
                        in <a
                            href="category.php?slug=<?= urlencode($post['category_slug']) ?>"><?= h($post['category_name']) ?></a>
                    <?php endif; ?>
                    &middot; <?= h(date('M j, Y', strtotime($post['created_at']))) ?>
                </div>
                <p><?= h($post['excerpt'] ?: make_excerpt($post['content'])) ?></p>
                <a class="read-more" href="post.php?slug=<?= urlencode($post['slug']) ?>">Read more &rarr;</a>
            </article>
        <?php endforeach; ?>
    </div>

    <?php $totalPages = (int) ceil($total / $perPage);
    if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?p=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/site_footer.php'; ?>
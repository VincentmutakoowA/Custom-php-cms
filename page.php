<?php
require_once __DIR__ . '/config.php';

$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    http_response_code(404);
    $pageTitle = 'Not Found';
    require __DIR__ . '/includes/site_header.php';
    echo '<h1>Page not found</h1>';
    require __DIR__ . '/includes/site_footer.php';
    exit;
}

$pageTitle = $page['title'];
require __DIR__ . '/includes/site_header.php';
?>

<div class="wrap">
    <article class="single-post">
        <h1><?= h($page['title']) ?></h1>
        <div class="content"><?= $page['content'] ?></div>
    </article>
</div>

<?php require __DIR__ . '/includes/site_footer.php'; ?>
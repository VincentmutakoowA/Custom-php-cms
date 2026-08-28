<?php
/** Escape a string for safe HTML output. */
function h(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/** Turn a title into a URL-safe, unique-enough slug. */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : bin2hex(random_bytes(4));
}

/** Ensure a slug is unique within a table, appending -2, -3, ... if needed. */
function unique_slug(string $table, string $base, ?int $excludeId = null): string
{
    $slug = $base;
    $i = 2;
    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = ?" . ($excludeId ? " AND id != ?" : "");
        $params = $excludeId ? [$slug, $excludeId] : [$slug];
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $i;
        $i++;
    }
}

/** CSRF token helpers */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid CSRF token. Please go back and try again.');
    }
}

/** Flash messages across a redirect */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Handle a single file upload from $_FILES[$field]. Returns the media row id
 * on success, or null if no file was submitted. Throws on validation failure.
 */
function handle_upload(string $field, int $uploadedBy): ?int
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed with error code ' . $file['error']);
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('File exceeds the 5MB limit.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_UPLOAD_EXT, true)) {
        throw new RuntimeException('File type .' . h($ext) . ' is not allowed.');
    }

    // Validate actual content type, not just the extension.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $safeName = bin2hex(random_bytes(8)) . '.' . $ext;
    $destination = UPLOAD_DIR . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save uploaded file.');
    }

    $stmt = db()->prepare(
        'INSERT INTO media (filename, original_name, mime_type, size_bytes, uploaded_by) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$safeName, $file['name'], $mime, $file['size'], $uploadedBy]);

    return (int) db()->lastInsertId();
}

/** Simple excerpt generator from HTML/plain content. */
function make_excerpt(string $content, int $length = 160): string
{
    $text = trim(strip_tags($content));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

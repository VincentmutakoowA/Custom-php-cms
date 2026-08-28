<?php
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $user = null;
    if ($user !== null) {
        return $user;
    }
    $stmt = db()->prepare('SELECT id, username, email, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;
    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin';
}

/** Redirects to login if not authenticated. Call at the top of protected pages. */
function require_login(): void
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

/** Redirects with an error if the current user isn't an admin. */
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        flash_set('error', 'You do not have permission to access that page.');
        redirect('index.php');
    }
}

function attempt_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    return true;
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}

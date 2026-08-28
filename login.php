<?php
require_once __DIR__ . '/config.php';

if (is_logged_in()) {
    redirect('admin/index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (attempt_login($username, $password)) {
        redirect('admin/index.php');
    } else {
        $error = 'Invalid username/email or password.';
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/site_header.php';
?>

<div class="auth-box">
    <h1>Login</h1>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= h($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <label>Username or Email
            <input type="text" name="username" required autofocus>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit">Log In</button>
    </form>
    <p class="hint">Default admin: <code>admin</code> / <code>ChangeMe123!</code> &mdash; change this immediately after first login.</p>
</div>

<?php require __DIR__ . '/includes/site_footer.php'; ?>

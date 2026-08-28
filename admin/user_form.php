<?php
require_once __DIR__ . '/../config.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : null);
$editUser = ['id' => null, 'username' => '', 'email' => '', 'role' => 'editor'];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT id, username, email, role FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash_set('error', 'User not found.');
        redirect('users.php');
    }
    $editUser = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $editUser['username'] = trim($_POST['username'] ?? '');
    $editUser['email'] = trim($_POST['email'] ?? '');
    $editUser['role'] = in_array($_POST['role'] ?? '', ['admin', 'editor'], true) ? $_POST['role'] : 'editor';
    $password = $_POST['password'] ?? '';

    if ($editUser['username'] === '') {
        $errors[] = 'Username is required.';
    }
    if ($editUser['email'] === '' || !filter_var($editUser['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if (!$id && $password === '') {
        $errors[] = 'Password is required for new users.';
    }
    if ($password !== '' && strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if (empty($errors)) {
        try {
            if ($id) {
                if ($password !== '') {
                    $stmt = db()->prepare('UPDATE users SET username=?, email=?, role=?, password_hash=? WHERE id=?');
                    $stmt->execute([$editUser['username'], $editUser['email'], $editUser['role'], password_hash($password, PASSWORD_DEFAULT), $id]);
                } else {
                    $stmt = db()->prepare('UPDATE users SET username=?, email=?, role=? WHERE id=?');
                    $stmt->execute([$editUser['username'], $editUser['email'], $editUser['role'], $id]);
                }
                flash_set('success', 'User updated.');
            } else {
                $stmt = db()->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$editUser['username'], $editUser['email'], password_hash($password, PASSWORD_DEFAULT), $editUser['role']]);
                flash_set('success', 'User created.');
            }
            redirect('users.php');
        } catch (PDOException $e) {
            $errors[] = 'That username or email is already taken.';
        }
    }
}

$pageTitle = $id ? 'Edit User' : 'New User';
require __DIR__ . '/includes/header.php';
?>

<h1><?= $id ? 'Edit User' : 'New User' ?></h1>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><?= h($e) ?></div>
<?php endforeach; ?>

<form method="post" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= h((string) $id) ?>">

    <label>Username
        <input type="text" name="username" value="<?= h($editUser['username']) ?>" required>
    </label>

    <label>Email
        <input type="email" name="email" value="<?= h($editUser['email']) ?>" required>
    </label>

    <label>Role
        <select name="role">
            <option value="editor" <?= $editUser['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
            <option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
    </label>

    <label><?= $id ? 'New Password (leave blank to keep current)' : 'Password' ?>
        <input type="password" name="password" <?= $id ? '' : 'required' ?> minlength="8">
    </label>

    <div class="form-actions">
        <button type="submit" class="btn">Save User</button>
        <a href="users.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * Returns a shared PDO connection to the SQLite database.
 * Initializes the schema and a default admin user on first run.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $isNew = !file_exists(DB_PATH);

    if (!is_dir(dirname(DB_PATH))) {
        mkdir(dirname(DB_PATH), 0755, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($isNew) {
        $schema = file_get_contents(SCHEMA_PATH);
        $pdo->exec($schema);
        seed_default_admin($pdo);
    }

    return $pdo;
}

function seed_default_admin(PDO $pdo): void
{
    $count = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        return;
    }
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([
        'admin',
        'admin@example.com',
        password_hash('ChangeMe123!', PASSWORD_DEFAULT),
        'admin',
    ]);
}

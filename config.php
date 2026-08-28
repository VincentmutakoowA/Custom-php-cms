<?php
/**
 * Core configuration.
 * Adjust SITE_NAME / BASE_URL for your deployment.
 */

if (session_status() === PHP_SESSION_NONE) {
    // Harden session cookie settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    // Uncomment if serving over HTTPS:
    // ini_set('session.cookie_secure', 1);
    session_start();
}

define('SITE_NAME', 'My CMS');
define('BASE_PATH', __DIR__);
define('DB_PATH', BASE_PATH . '/database/cms.sqlite');
define('SCHEMA_PATH', BASE_PATH . '/database/schema.sql');
define('UPLOAD_DIR', BASE_PATH . '/uploads');
define('UPLOAD_URL', 'uploads');
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024); // 5MB
define('ALLOWED_UPLOAD_EXT', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf']);

date_default_timezone_set('UTC');

require_once BASE_PATH . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/auth.php';

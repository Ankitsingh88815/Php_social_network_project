<?php
// config.php - basic configuration (edit DB credentials)
session_start();

$dbOptions = [
    'host' => 'localhost',
    'dbname' => 'social_network',
    'user' => 'root',
    'pass' => 'Ankit*#9555',
];

// Where uploaded files are saved (public/uploads)
define('UPLOAD_DIR', __DIR__ . '/public/uploads/');
define('UPLOAD_URL', '/social_network/public/uploads/');

// File upload limits
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
$ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/gif'];

// Ensure upload dir exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

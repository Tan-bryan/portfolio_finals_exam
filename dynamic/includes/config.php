<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change this to your database username
define('DB_PASS', ''); // Change this to your database password
define('DB_NAME', 'portfolio_db');

// Application Configuration
define('BASE_URL', 'http://localhost/js/Finals Exam/dynamic/'); // Change this to your base URL
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Allowed file types
define('ALLOWED_IMAGES', ['jpg', 'jpeg', 'png', 'gif']);

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
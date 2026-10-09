<?php
// db.php - Database connection helper
// Credentials are read from environment variables.
// For local development, you can use a .env file loaded by your server
// or set the constants below in a local config file that is NOT committed.

// Load from environment variables (set in .env or server config)
// Fallback values below are for LOCAL DEVELOPMENT ONLY.
// On production, always set these via environment variables or server config.
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'globetrek_db');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Log the error server-side; never expose details to the browser
    error_log('Database connection failed: ' . $e->getMessage());
    $pdo = null;
}

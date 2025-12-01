<?php
// db.php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env if it exists (local development)
// safeLoad() prevents fatal error if .env is missing (e.g., on Render)
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Read environment variables, provide defaults if needed
$DB_HOST = $_ENV['DB_HOST'] ?? 'localhost';
$DB_PORT = $_ENV['DB_PORT'] ?? 3306; // default MySQL port
$DB_NAME = $_ENV['DB_NAME'] ?? '';
$DB_USER = $_ENV['DB_USER'] ?? '';
$DB_PASS = $_ENV['DB_PASS'] ?? '';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($mysqli->connect_errno) {
    http_response_code(500);
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Fetch products helper function
function db_fetch_products(): array
{
    global $mysqli;

    $sql = "SELECT id, slug, title, description FROM products";
    $result = $mysqli->query($sql);

    if (!$result) {
        return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $result->free();

    return $rows;
}

<?php
session_start();
include __DIR__ . '/db.php'; // Your database connection

// Check required parameters
if (!isset($_GET['company_id']) || !isset($_GET['url'])) {
    die('Invalid request.');
}

$companyId = (int)$_GET['company_id'];
$url = $_GET['url'];

// Optional: Validate that the URL is allowed (prevent open redirect attacks)
$allowedUrls = [
    1 => 'https://php-mysql-hosting-project.onrender.com',
    2 => 'https://lambertnguyen.cloud/',
    3 => 'https://partner2.com'
];

if (!isset($allowedUrls[$companyId]) || $allowedUrls[$companyId] !== $url) {
    die('Invalid or unallowed URL.');
}

// Get user ID if logged in (optional)
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Record the click in the database
$stmt = $mysqli->prepare("
    INSERT INTO user_activity (user_id, company_id, page_type, page_id, visited_at)
    VALUES (?, ?, 'external', NULL, NOW())
");
$stmt->bind_param("ii", $userId, $companyId);
$stmt->execute();
$stmt->close();

// Redirect to the partner URL
header("Location: " . $url);
exit;

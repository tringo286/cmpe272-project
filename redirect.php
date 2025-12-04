<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/db.php'; // Database connection

// =========================
// Check required parameters
// =========================
if (!isset($_GET['company_id']) || !isset($_GET['url'])) {
    die('Invalid request.');
}

$companyId = (int)$_GET['company_id'];
$url = $_GET['url'];

// =========================
// Redirect to login if user not logged in
// =========================
if (!isset($_SESSION['user_id'])) {
    // Optionally, store intended URL to redirect after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// =========================
// Allowed URLs (prevent open redirect attacks)
// =========================
$allowedUrls = [
    1 => 'https://php-mysql-hosting-project.onrender.com',
    2 => 'https://lambertnguyen.cloud/',
    3 => 'http://anukrithimyadala.42web.io/'
];

if (!isset($allowedUrls[$companyId]) || $allowedUrls[$companyId] !== $url) {
    die('Invalid or unallowed URL.');
}

// =========================
// Record the click in user_activity table
// =========================
$stmt = $mysqli->prepare("
    INSERT INTO user_activity (user_id, company_id, page_type, page_id, visited_at)
    VALUES (?, ?, 'external', NULL, NOW())
");

$stmt->bind_param("ii", $userId, $companyId);
$stmt->execute();
$stmt->close();

// =========================
// Redirect user safely
// =========================
header("Location: " . $url);
exit;

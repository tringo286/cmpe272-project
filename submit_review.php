<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in.");
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
$user_id = $_SESSION['user_id'];

if ($product_id <= 0 || $rating < 1 || $rating > 5 || empty($review_text)) {
    die("Invalid review submission.");
}

// Optional: prevent duplicate reviews
$stmtCheck = $mysqli->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
$stmtCheck->bind_param("ii", $product_id, $user_id);
$stmtCheck->execute();
$stmtCheck->store_result();
if ($stmtCheck->num_rows > 0) {
    die("You have already reviewed this product.");
}
$stmtCheck->close();

// Insert review
$stmt = $mysqli->prepare("
    INSERT INTO reviews (product_id, user_id, rating, review_text)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiis", $product_id, $user_id, $rating, $review_text);

if (!$stmt->execute()) {
    die("Error submitting review: " . $stmt->error);
}

$stmt->close();

// Redirect back to product page
header("Location: product.php?id=" . $product_id);
exit;

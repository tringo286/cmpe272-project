<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in.");
}

$product_id = $_POST['product_id'];
$user_id = $_SESSION['user_id'];
$rating = $_POST['rating'];
$review_text = trim($_POST['review_text']);

$stmt = $mysqli->prepare("
    INSERT INTO reviews (product_id, user_id, rating, review_text)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiis", $product_id, $user_id, $rating, $review_text);
$stmt->execute();

header("Location: product.php?id=" . $product_id);
exit;

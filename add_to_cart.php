<?php
session_start();
include __DIR__ . '/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $userId = $_SESSION['user_id'];
    $productId = intval($_POST['product_id']);

    // Insert or update quantity
    $stmt = $mysqli->prepare("
        INSERT INTO cart (user_id, product_id, quantity) 
        VALUES (?, ?, 1) 
        ON DUPLICATE KEY UPDATE quantity = quantity + 1
    ");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $stmt->close();

    // Redirect back to cart page
    header('Location: cart.php');
    exit;
}
?>

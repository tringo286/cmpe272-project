<?php
session_start();
include __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Check POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

$fullname = trim($_POST['fullname'] ?? '');
$address  = trim($_POST['address'] ?? '');
$city     = trim($_POST['city'] ?? '');
$paymentMethod = $_POST['payment_method'] ?? 'card';

if (!$fullname || !$address || !$city) {
    die('Please fill all required fields.');
}

// Fetch cart items
$cartItems = [];
$stmt = $mysqli->prepare("
    SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}
$stmt->close();

if (empty($cartItems)) {
    die('Your cart is empty.');
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = 5.99;       // fixed shipping fee
$tax = $subtotal * 0.08; // 8% tax
$total = $subtotal + $shipping + $tax;

// Insert order
$stmt = $mysqli->prepare("
    INSERT INTO orders (user_id, fullname, address, city, payment_method, subtotal, shipping, tax, total, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("issssdddd", $userId, $fullname, $address, $city, $paymentMethod, $subtotal, $shipping, $tax, $total);
$stmt->execute();
$orderId = $stmt->insert_id;
$stmt->close();

// Insert order items
foreach ($cartItems as $item) {
    $stmtItem = $mysqli->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    $stmtItem->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
    $stmtItem->execute();
    $stmtItem->close();
}

// Clear cart
$stmt = $mysqli->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

// Redirect to thank-you page
header("Location: thank_you.php?order_id=" . $orderId);
exit;

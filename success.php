<?php
session_start();

require __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/db.php';

// Always load .env (safe, fast, reliable)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Use Stripe key from $_ENV (NOT getenv)
$stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? null;

if (!$stripeSecret) {
    die("FATAL ERROR: Stripe secret key not loaded in create_checkout_session.php");
}

// Set Stripe API key
\Stripe\Stripe::setApiKey($stripeSecret);

$sessionId = $_GET['session_id'] ?? null;

if (!$sessionId) {
    die('Invalid session.');
}

$session = \Stripe\Checkout\Session::retrieve($sessionId);

// Get saved shipping info
$fullname = $_SESSION['checkout_fullname'] ?? '';
$address  = $_SESSION['checkout_address'] ?? '';
$city     = $_SESSION['checkout_city'] ?? '';
$userId   = $_SESSION['user_id'] ?? null;

if (!$fullname || !$address || !$city || !$userId) {
    die('Missing session data.');
}

// Get cart items
$stmt = $mysqli->prepare("
    SELECT c.quantity, p.id AS product_id, p.price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$subtotal = 0;

while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
    $subtotal += $row['price'] * $row['quantity'];
}

$shipping = 5.99;
$tax = $subtotal * 0.08;
$total = $subtotal + $shipping + $tax;

// Insert order
$stmt = $mysqli->prepare("
    INSERT INTO orders (user_id, fullname, address, city, payment_method, subtotal, shipping, tax, total, created_at)
    VALUES (?, ?, ?, ?, 'Stripe', ?, ?, ?, ?, NOW())
");
$stmt->bind_param("isssdddd", $userId, $fullname, $address, $city, $subtotal, $shipping, $tax, $total);
$stmt->execute();
$orderId = $stmt->insert_id;
$stmt->close();

// Insert order items
foreach ($cartItems as $item) {
    $stmt2 = $mysqli->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    $stmt2->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
    $stmt2->execute();
    $stmt2->close();
}

// Clear cart
$stmt = $mysqli->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();

// Clear temp session
unset($_SESSION['checkout_fullname'], $_SESSION['checkout_address'], $_SESSION['checkout_city']);

// Redirect to thank you page
header("Location: thank_you.php?order_id=" . $orderId);
exit;

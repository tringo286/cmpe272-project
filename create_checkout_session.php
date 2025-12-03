<?php
session_start();
require 'vendor/autoload.php';
include __DIR__ . '/db.php';

// Only load .env if STRIPE_SECRET_KEY isn't already defined
if (getenv('STRIPE_SECRET_KEY') === false) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Use the Stripe secret key from environment
$stripeSecret = getenv('STRIPE_SECRET_KEY');
\Stripe\Stripe::setApiKey($stripeSecret);

// Validate input
$fullname = $_POST['fullname'] ?? '';
$address  = $_POST['address'] ?? '';
$city     = $_POST['city'] ?? '';

if (!$fullname || !$address || !$city) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Store these temporarily so success.php can use them
$_SESSION['checkout_fullname'] = $fullname;
$_SESSION['checkout_address']  = $address;
$_SESSION['checkout_city']     = $city;

$userId = $_SESSION['user_id'];

// Get cart items
$stmt = $mysqli->prepare("
    SELECT c.quantity, p.title, p.price, p.id
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$line_items = [];
$subtotal = 0;

while ($row = $result->fetch_assoc()) {
    $line_items[] = [
        'price_data' => [
            'currency' => 'usd',
            'unit_amount' => intval($row['price'] * 100),
            'product_data' => [
                'name' => $row['title'],
                'images' => ["https://cmpe272-project.onrender.com/assets/images/{$row['id']}.png"] // updated URL
            ],
        ],
        'quantity' => $row['quantity']
    ];
    $subtotal += $row['price'] * $row['quantity'];
}

$shipping = 5.99;
$tax = $subtotal * 0.08;

// Add shipping
$line_items[] = [
    'price_data' => [
        'currency' => 'usd',
        'unit_amount' => intval($shipping * 100),
        'product_data' => ['name' => 'Shipping']
    ],
    'quantity' => 1
];

// Create Stripe checkout session
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'mode' => 'payment',
    'line_items' => $line_items,
    'success_url' => 'https://cmpe272-project.onrender.com/success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url'  => 'https://cmpe272-project.onrender.com/checkout.php'
]);

echo json_encode(['sessionId' => $session->id]);
exit;

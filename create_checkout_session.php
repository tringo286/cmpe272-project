<?php
session_start();

// Set JSON response header FIRST
header('Content-Type: application/json');

require __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/db.php';

// Load .env for local development (only if keys aren't already set)
if (empty($_ENV['STRIPE_SECRET_KEY'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Get Stripe keys from $_ENV
$stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? null;
if (!$stripeSecret) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe API key not configured']);
    exit;
}


// Set Stripe API key
\Stripe\Stripe::setApiKey($stripeSecret);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Validate input
$fullname = $_POST['fullname'] ?? '';
$address  = $_POST['address'] ?? '';
$city     = $_POST['city'] ?? '';

if (!$fullname || !$address || !$city) {
    http_response_code(400);
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
                "images" => ["https://cmpe272-project.onrender.com/assets/images/{$row['id']}.png"]
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

$baseUrl = "https://cmpe272-project.onrender.com";

// Use direct cURL JSON request instead of stripe-php (avoids header/body encoding issue)
try {
    $payload = [
        'payment_method_types' => ['card'],
        'mode' => 'payment',
        'line_items' => $line_items,
        'success_url' => $baseUrl . '/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'  => $baseUrl . '/checkout.php'
    ];

    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    $jsonBody = json_encode($payload);

    $headers = [
        'Authorization: Bearer ' . $stripeSecret,
        'Content-Type: application/json',
        'User-Agent: MarketHub/1.0'
    ];

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonBody,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        http_response_code(500);
        echo json_encode(['error' => 'cURL Error: ' . $curlError]);
        exit;
    }

    $decoded = json_decode($response, true);

    if ($httpCode >= 400) {
        http_response_code($httpCode);
        $errorMsg = $decoded['error']['message'] ?? 'Stripe API Error';
        echo json_encode(['error' => $errorMsg]);
        exit;
    }

    if (!isset($decoded['id'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid Stripe response']);
        exit;
    }

    echo json_encode(['sessionId' => $decoded['id']]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
}
exit;


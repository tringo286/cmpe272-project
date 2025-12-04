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

// --- DEBUG: optional raw curl test (enable with DEBUG_STRIPE=1) ---
if (!empty($_ENV['DEBUG_STRIPE']) || getenv('DEBUG_STRIPE')) {
    $debugLog = '/tmp/stripe_debug.log';
    $testParams = [
        'payment_method_types' => ['card'],
        'mode' => 'payment',
        // pass minimal fake line_items to test encoding
        'line_items' => $line_items,
    ];

    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    $headers = [
        'Authorization: Bearer ' . $stripeSecret,
        'Stripe-Version: ' . (isset($_ENV['STRIPE_API_VERSION']) ? $_ENV['STRIPE_API_VERSION'] : \Stripe\Stripe::getApiVersion()),
        'Content-Type: application/x-www-form-urlencoded'
    ];

    // build a flattened form body similar to stripe-php v1 encoding
    $flat = http_build_query($testParams);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $flat);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('/tmp/stripe_curl_verbose.log', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if (is_resource($verbose)) {
        fclose($verbose);
    }

    $out = [
        'response' => $resp,
        'curl_info' => $info,
        'curl_error' => $err,
        'body_preview' => substr($flat, 0, 1000),
    ];

    file_put_contents($debugLog, json_encode($out, JSON_PRETTY_PRINT));
    echo json_encode(['debug' => 'wrote ' . $debugLog, 'out' => $out]);
    exit;
}

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'mode' => 'payment',
        'line_items' => $line_items,
        'success_url' => $baseUrl . '/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'  => $baseUrl . '/checkout.php'
    ]);
    
    echo json_encode(['sessionId' => $session->id]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Stripe Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
}
exit;
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}


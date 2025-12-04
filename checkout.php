<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/db.php';

// Load .env only if missing
if (empty($_ENV['STRIPE_SECRET_KEY'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

// Use environment variables from $_ENV
$stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? null;
$stripePub    = $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? null;

if (!$stripeSecret || !$stripePub) {
    die("Stripe API keys are not set.");
}

\Stripe\Stripe::setApiKey($stripeSecret);

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch cart items
$cartItems = [];
$stmt = $mysqli->prepare("
    SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.title, p.price, p.slug
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
    header('Location: cart.php');
    exit;
}

include __DIR__ . '/includes/header.php';

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 5.99;
$tax = $subtotal * 0.08;
$total = $subtotal + $shipping + $tax;
?>

<section class="checkout-section">
    <h2>Checkout</h2>
    <div class="checkout-wrapper">
        <!-- LEFT: Shipping & Stripe -->
        <div class="checkout-left">
            <h3>Shipping Address</h3>
            <label>
                Full Name:<br>
                <input type="text" name="fullname" required>
            </label>
            <label>
                Address:<br>
                <input type="text" name="address" required>
            </label>
            <label>
                City:<br>
                <input type="text" name="city" required>
            </label>

            <h3>Order Summary</h3>
            <div class="order-summary">
                <?php foreach ($cartItems as $item): ?>
                    <div class="summary-item">
                        <span><?php echo htmlspecialchars($item['title']); ?> x <?php echo $item['quantity']; ?></span>
                        <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="summary-total"><span>Subtotal:</span><span>$<?php echo number_format($subtotal, 2); ?></span></div>
                <div class="summary-total"><span>Shipping:</span><span>$<?php echo number_format($shipping, 2); ?></span></div>
                <div class="summary-total"><span>Tax (8%):</span><span>$<?php echo number_format($tax, 2); ?></span></div>
                <div class="summary-total summary-grand"><strong>Total:</strong><strong>$<?php echo number_format($total, 2); ?></strong></div>
            </div>

            <button type="button" id="payButton" class="btn-place-order">
                Pay with Card
            </button>
        </div>

        <!-- RIGHT: Your Items -->
        <div class="checkout-right">
            <h3>Your Items</h3>
            <?php foreach ($cartItems as $item): ?>
                <div class="checkout-item">
                    <img src="/assets/images/<?php echo $item['slug']; ?>.png" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <div class="item-info">
                        <p><?php echo htmlspecialchars($item['title']); ?></p>
                        <p>Qty: <?php echo $item['quantity']; ?></p>
                        <p>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script src="https://js.stripe.com/v3/"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('payButton').addEventListener('click', function () {

    const fullname = document.querySelector('[name="fullname"]').value;
    const address = document.querySelector('[name="address"]').value;
    const city = document.querySelector('[name="city"]').value;

    if (!fullname || !address || !city) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Please fill all fields!',
            timer: 2500,
            showConfirmButton: false
        });
        return;
    }

    const formData = new FormData();
    formData.append('fullname', fullname);
    formData.append('address', address);
    formData.append('city', city);

    fetch('create_checkout_session.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            Swal.fire({
                icon: 'error',
                title: 'Payment Error',
                text: data.error
            });
            return;
        }
        const stripe = Stripe("<?= $stripePub ?>");
        stripe.redirectToCheckout({ sessionId: data.sessionId });
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

<style>
/* ===================== MODERN CHECKOUT PAGE ===================== */

/* GENERAL PAGE LAYOUT */
.checkout-section {
    padding: 2rem;
    background-color: #f5f5f5;
    font-family: 'Inter', sans-serif;
}

.checkout-section h2 {
    font-size: 2.5rem;
    margin-bottom: 2rem;
    color: #111827;
    text-align: center;
    font-weight: 700;
}

/* FLEX LAYOUT */
.checkout-wrapper {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.checkout-left, .checkout-right {
    background: #ffffff;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

/* SHIPPING FORM */
.checkout-left h3 {
    margin-bottom: 1rem;
    font-size: 1.25rem;
    color: #374151;
}

.checkout-left label {
    display: block;
    margin-bottom: 1rem;
    font-weight: 500;
    color: #374151;
}

.checkout-left input[type="text"] {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    margin-top: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.2s;
     box-sizing: border-box;
}

.checkout-left input[type="text"]:focus {
    border-color: #2563eb;
    outline: none;
}

/* ORDER SUMMARY */
.order-summary {
    margin-top: 2rem;
    border-top: 1px solid #e5e7eb;
    padding-top: 1.5rem;
}

.summary-item,
.summary-total {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    font-size: 1rem;
}

.summary-grand {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
}

/* BUTTON */
.btn-place-order {
    width: 100%;
    padding: 0.9rem;
    background: linear-gradient(90deg, #2563eb, #3b82f6);
    color: #fff;
    font-size: 1.1rem;
    font-weight: 600;
    border: none;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    margin-top: 1.5rem;
}

.btn-place-order:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* YOUR ITEMS */
.checkout-right h3 {
    margin-bottom: 1rem;
    font-size: 1.5rem;
    color: #111827;
}

.checkout-item {
    display: flex;
    flex-direction: row;
    gap: 1rem;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.checkout-item:last-child {
    border-bottom: none;
}

.checkout-item img {
    width: 80px;   /* smaller image */
    height: 80px;
    border-radius: 0.5rem;
    object-fit: cover;
    flex-shrink: 0;
}

.item-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.item-info p {
    margin: 2px 0;
    font-size: 0.95rem;
    color: #374151;
}

/* RESPONSIVE LAYOUT */
@media(min-width: 900px) {
    .checkout-wrapper {
        flex-direction: row;
        gap: 2rem;
        align-items: flex-start;
    }

    .checkout-left {
        flex: 2;
    }

    .checkout-right {
        flex: 1;
    }
}
</style>


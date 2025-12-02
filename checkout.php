<?php
session_start();
include __DIR__ . '/db.php';

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

$shipping = 5.99; // fixed shipping fee
$tax = $subtotal * 0.08; // 8% sales tax
$total = $subtotal + $shipping + $tax;

?>

<section class="checkout-section">
    <h2>Checkout</h2>
    <div class="checkout-wrapper">
        <!-- LEFT: Shipping & Payment -->
        <div class="checkout-left">
            <h3>Shipping Address</h3>
            <form method="POST" action="process_order.php">
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

                <h3>Payment Method</h3>
                <label>
                    <input type="radio" name="payment_method" value="card" checked> Credit / Debit Card
                </label>
                <label>
                    <input type="radio" name="payment_method" value="cod"> Cash on Delivery
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
                    <div class="summary-total summary-grand">
                        <strong>Total:</strong>
                        <strong>$<?php echo number_format($total, 2); ?></strong>
                    </div>
                </div>

                <button type="submit" class="btn-place-order">Place Order</button>
            </form>
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

<?php include __DIR__ . '/includes/footer.php'; ?>

<style>
    /* ===================== CHECKOUT PAGE CSS ===================== */

/* PAGE LAYOUT */
.checkout-section {
    padding: 1rem;
    background-color: #f3f3f3;
}

.checkout-section h2 {
    font-size: 2rem;
    margin-bottom: 1.5rem;
    margin-top: 0;
}

.checkout-wrapper {
    display: flex;
    flex-direction: column; /* vertical layout by default */
    gap: 2rem;
}

/* LEFT PANEL: SHIPPING & PAYMENT FORM */
.checkout-left {
    background: #fff;
    padding: 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.checkout-left h3 {
    margin-top: 1rem;
}

.checkout-left label {
    display: block;
    margin-bottom: 0.8rem;
    font-weight: 500;
}

.checkout-left input[type="text"],
.checkout-left input[type="email"],
.checkout-left input[type="number"] {
    width: 100%;
    padding: 0.5rem;
    margin-top: 0.25rem;
    border: 1px solid #ccc;
    border-radius: 0.25rem;
    box-sizing: border-box;
}

.order-summary {
    margin-top: 1rem;
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
}

.summary-item,
.summary-total {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.summary-grand {
    font-size: 1.2rem;
    font-weight: 700;
}

.btn-place-order {
    width: 100%;
    background: #ffd814;
    padding: 0.75rem;
    border: none;
    margin-top: 1rem;
    font-size: 1.1rem;
    font-weight: bold;
    border-radius: 0.5rem;
    cursor: pointer;
}

.btn-place-order:hover {
    background: #f7ca00;
}

/* RIGHT PANEL: YOUR ITEMS */
/* RIGHT PANEL: YOUR ITEMS */
.checkout-right {
    background: #fff;
    padding: 1rem;
    border-radius: 0.75rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column; /* stack items vertically */
    gap: 1rem;               /* spacing between items */
    overflow: visible;       /* allow natural height */
}

.checkout-item {
    display: flex;
    flex-direction: row; /* image + info side by side */
    gap: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 0.5rem;
    align-items: center; /* vertically center text next to image */
}

.checkout-item:last-child {
    border-bottom: none;
}

.checkout-item img {
    width: 100px;         /* unified smaller width */
    height: 100px;        /* unified smaller height */
    border-radius: 0.25rem;
    object-fit: cover;
    flex-shrink: 0;      /* prevent image from shrinking */
}

.item-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.item-info p {
    margin: 0;
    font-size: 0.9rem;
}


/* RESPONSIVE LAYOUT */
@media (min-width: 900px) {
    .checkout-wrapper {
        flex-direction: row; /* side by side on desktop */
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

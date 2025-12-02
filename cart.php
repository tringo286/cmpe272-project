<?php
session_start();
include __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Handle quantity update or remove actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity']) && isset($_POST['cart_id'], $_POST['quantity'])) {
        $cartId = intval($_POST['cart_id']);
        $quantity = max(1, intval($_POST['quantity'])); // Minimum 1
        $stmt = $mysqli->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $quantity, $cartId, $userId);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['remove_item']) && isset($_POST['cart_id'])) {
        $cartId = intval($_POST['cart_id']);
        $stmt = $mysqli->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
        $stmt->close();
    }

    // Refresh page to update cart count
    header("Location: cart.php");
    exit;
}

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

// Show empty cart if no items
if (empty($cartItems)) {
    ?>
    <section class="empty-cart-section">
        <div class="empty-cart-container">
            <h2>Your Cart is empty</h2>
            <a href="/index.php" class="btn-empty">Shop today's deals and find your favorite products!</a>
        </div>
    </section>
    <style>
    .empty-cart-section {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 4rem 1rem;
        min-height: 100vh;
        background-color: #f3f3f3;
    }
    .empty-cart-container {
        text-align: center;
        max-width: 600px;
        width: 100%;
        background: white;
        padding: 3rem 2rem;
        border-radius: 0.75rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .empty-cart-container h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #111827;
    }
    .btn-empty {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background-color: #ffd814;
        color: #111827;
        font-weight: 600;
        border-radius: 0.5rem;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-empty:hover { background-color: #f7ca00; }
    </style>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}
?>

<section class="cart-section">
    <h2>Your Shopping Cart</h2>
    <div class="cart-main">
        <div class="cart-items">
            <?php
            $grandTotal = 0;
            foreach ($cartItems as $item):
                $total = $item['price'] * $item['quantity'];
                $grandTotal += $total;
            ?>
            <div class="cart-item">
               <div class="cart-item-image">
                    <?php 
                        $slug = strtolower(str_replace(' ', '-', $item['title']));
                    ?>
                    <img src="/assets/images/<?php echo $slug; ?>.png" 
                        alt="<?php echo htmlspecialchars($item['title']); ?>">
                </div>


                <div class="cart-item-info">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
                    <div class="cart-actions">
                        <form method="POST" class="quantity-form">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <label>Qty:
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1">
                            </label>
                            <button type="submit" name="update_quantity" class="btn-update">Update</button>
                        </form>
                        <form method="POST" class="remove-form">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <button type="submit" name="remove_item" class="btn-remove">Remove</button>
                        </form>
                        <button class="btn-save">Save for later</button>
                    </div>
                </div>
                <div class="cart-item-total">
                    $<?php echo number_format($total, 2); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <h3>Order Summary</h3>
            <p>Items: <?php echo count($cartItems); ?></p>
            <p><strong>Subtotal:</strong> $<?php echo number_format($grandTotal, 2); ?></p>
            <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
        </div>
    </div>
</section>

<style>
.cart-section {
    min-height: 100vh;
    padding: 2rem;
    background-color: #f3f3f3;
}
.cart-section h2 {
    font-size: 2rem;
    margin-bottom: 1.5rem;
}
.cart-main {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

/* Cart items */
.cart-items {
    flex: 2;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.cart-item {
    display: flex;
    background: white;
    padding: 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    gap: 1rem;
}
.cart-item-image img {
    width: 150px;      /* fixed width */
    height: 150px;     /* fixed height */
    object-fit: cover; /* cover to fill the box nicely */
    border-radius: 0.5rem; /* rounded corners */
    border: 1px solid #e2e2e2; /* subtle border for better separation */
    background-color: #fff; /* fallback bg color */
}

.cart-item-info {
    flex: 2;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.cart-item-info h3 {
    margin: 0;
    font-size: 1.1rem;
}
.cart-item-info .price {
    font-weight: bold;
    margin-top: 0.25rem;
}
.cart-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 0.5rem;
}
.quantity-form input[type="number"] {
    width: 60px;
    padding: 0.3rem;
    border: 1px solid #ccc;
    border-radius: 0.25rem;
}
.btn-update, .btn-remove, .btn-save {
    border: none;
    padding: 0.4rem 0.8rem;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.85rem;
}
.btn-update { background-color: #3b82f6; color: white; }
.btn-update:hover { background-color: #2563eb; }
.btn-remove { background-color: #ef4444; color: white; }
.btn-remove:hover { background-color: #dc2626; }
.btn-save { background-color: #f0f0f0; color: #111; }
.btn-save:hover { background-color: #e0e0e0; }

/* Item total */
.cart-item-total {
    flex: 0 0 120px;
    text-align: right;
    font-weight: bold;
    font-size: 1.1rem;
    color: #111827;
    align-self: center;
}

/* Cart summary */
.cart-summary {
    flex: 1;
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    height: fit-content;
}
.cart-summary h3 {
    margin-top: 0;
}
.btn-checkout {
    display: inline-block;
    background-color: #f0c14b;
    color: #111;
    padding: 0.75rem 1.25rem;
    border-radius: 0.25rem;
    font-weight: bold;
    text-decoration: none;
    margin-top: 1rem;
}
.btn-checkout:hover {
    background-color: #e2b33b;
}

/* Responsive */
@media (max-width: 768px) {
    .cart-main {
        flex-direction: column;
    }
    .cart-item {
        flex-direction: column;
        align-items: flex-start;
    }
    .cart-item-total {
        text-align: left;
        width: 100%;
        margin-top: 0.5rem;
    }
}
</style>


<?php
include __DIR__ . '/includes/footer.php';
?>

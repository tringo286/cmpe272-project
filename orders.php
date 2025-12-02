<?php
// SAFE session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$userId = $_SESSION['user_id'];

/* ===============================
   Fetch all orders for this user
   =============================== */
$stmt = $mysqli->prepare("
    SELECT id, fullname, address, city, payment_method, total, created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<section class="orders-page">

    <h2>My Orders</h2>

    <?php if (empty($orders)): ?>
        <div class="no-orders">
            😕 You have no orders yet.
        </div>
    <?php else: ?>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Date</th>
                    <th>Recipient</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th>Items</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <span class="order-id-badge">#<?= $order['id'] ?></span>
                        </td>

                        <td><?= $order['created_at'] ?></td>

                        <td><?= htmlspecialchars($order['fullname']) ?></td>

                        <td>
                            <?php
                                $method = strtolower($order['payment_method']);
                                $methodLabel = strtoupper($method);
                            ?>
                            <span class="payment-badge <?= $method ?>">
                                <?= $methodLabel ?>
                            </span>
                        </td>

                        <td>
                            <strong>$<?= number_format($order['total'], 2) ?></strong>
                        </td>

                        <td>
                            <div class="item-chip-container">
                                <?php
                                // Fetch items for this order
                                $stmt = $mysqli->prepare("
                                    SELECT oi.quantity, oi.price, p.title AS product_name
                                    FROM order_items oi
                                    JOIN products p ON oi.product_id = p.id
                                    WHERE oi.order_id = ?
                                ");
                                $stmt->bind_param("i", $order['id']);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                while ($item = $result->fetch_assoc()):
                                ?>
                                    <div class="item-chip">
                                        <span class="chip-title"><?= htmlspecialchars($item['product_name']) ?></span>
                                        <span class="chip-qty">× <?= $item['quantity'] ?></span>
                                        <span class="chip-price">$<?= number_format($item['price'], 2) ?></span>
                                    </div>
                                <?php endwhile;

                                $stmt->close();
                                ?>
                            </div>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<style>

/* --- Page Layout --- */
.orders-page {
    max-width: 1200px;
    margin: 2.5rem auto;
    padding: 0 1.25rem;
    min-height: calc(100vh - 200px);
    animation: fadeIn 0.35s ease;
}

.orders-page h2 {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.5rem;
}

/* --- Empty state --- */
.no-orders {
    text-align: center;
    padding: 2rem;
    font-size: 1.2rem;
    color: #6b7280;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    background: #ffffff;
    box-shadow: 0 6px 16px rgba(0,0,0,0.05);
}

/* --- Orders Table --- */
.orders-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 1rem;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

/* FANCY HEADER */
.orders-table thead th {
    background: rgba(35, 47, 62, 1);
    color: #ffffff;
    padding: 1rem;
    font-size: 1rem;
    font-weight: 700;
    border-bottom: 2px solid rgba(255,255,255,0.15);
}

.orders-table tbody td {
    padding: 1rem;
    font-size: 0.95rem;
    color: #111827;
    border-bottom: 1px solid #f0f0f0;
}

.orders-table tbody tr:hover td {
    background: #f9fafb;
    transition: 0.2s ease;
}

/* --- Order ID Badge --- */
.order-id-badge {
    background: #eef2ff;
    color: #4338ca;
    padding: 0.45rem 0.75rem;
    font-size: 0.85rem;
    font-weight: 700;
    border-radius: 0.5rem;
}

/* --- Payment Badge --- */
.payment-badge {
    padding: 0.35rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 700;
    border-radius: 0.5rem;
    display: inline-block;
}

/* Payment color types */
.payment-badge.card {
    background: #dbeafe;
    color: #1d4ed8;
}

.payment-badge.paypal {
    background: #d1fae5;
    color: #047857;
}

.payment-badge.cod {
    background: #fef9c3;
    color: #a16207;
}

/* --- Item Chips --- */
.item-chip-container {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.item-chip {
    background: #f3f4f6;
    padding: 0.45rem 0.7rem;
    border-radius: 0.65rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.85rem;
    color: #374151;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.item-chip .chip-title {
    font-weight: 600;
}

.item-chip .chip-qty {
    color: #6b7280;
}

.item-chip .chip-price {
    color: #2563eb;
    font-weight: 600;
}

/* --- Animation --- */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

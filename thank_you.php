<?php
session_start();
include __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

$orderId = intval($_GET['order_id'] ?? 0);
if (!$orderId) {
    header("Location: index.php");
    exit;
}

// Fetch order details
$stmt = $mysqli->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}
?>

<section class="thankyou-section">
    <div class="thankyou-container">
        <h2>Thank You for Your Order!</h2>
        <p>Your order <strong>#<?php echo $order['id']; ?></strong> has been successfully placed.</p>
        <p><strong>Total Paid:</strong> $<?php echo number_format($order['total'], 2); ?></p>
        <p>We will process your order and send updates to your email.</p>
        <a href="index.php" class="btn-return">Return to Shop</a>
    </div>
</section>

<style>
/* Thank You Page Styling */
.thankyou-section {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 180px); /* adjust for header + footer */
    background-color: #f3f3f3;
    padding: 2rem 1rem;
    box-sizing: border-box;
}

.thankyou-container {
    text-align: center;
    max-width: 600px;
    width: 100%;
    background: white;
    padding: 2rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.thankyou-container h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #111827;
}

.thankyou-container p {
    font-size: 1rem;
    margin-bottom: 1rem;
    color: #374151;
}

.btn-return {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background-color: #ffd814;
    color: #111827;
    font-weight: 600;
    border-radius: 0.5rem;
    text-decoration: none;
    transition: background 0.2s;
}

.btn-return:hover {
    background-color: #f7ca00;
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>

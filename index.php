<?php
session_start();
include __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

// get products from DB
$products = [];
$result = $mysqli->query("SELECT id, title, description FROM products ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'name' => $row['title'],
            'price' => 0,
            'seller' => 'Unknown',
            'status' => $row['description'],
        ];
    }
    $result->free();
}
?>

<section class="hero">
    <h2>Discover and sell unique items</h2>
    <p>Demo marketplace homepage built with plain PHP and MySQL.</p>
    <a href="#">Start selling</a>
</section>

<section>
    <h2 style="font-size:1.1rem; margin-bottom:0.75rem;">Latest listings</h2>
    <div class="grid">
        <?php foreach ($products as $product): ?>
            <article class="card">
                <div>
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="seller">by <?php echo htmlspecialchars($product['seller']); ?></div>
                    <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                    <div class="status"><?php echo htmlspecialchars($product['status']); ?></div>
                </div>
                <div class="actions">
                    <button class="btn-secondary" type="button">View</button>
                    <button class="btn-primary" type="button">Add to cart</button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';

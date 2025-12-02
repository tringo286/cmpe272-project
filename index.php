<?php
session_start();
include __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

// Get search query and filters from GET
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$selectedPrices = isset($_GET['price']) ? $_GET['price'] : [];
$selectedSellers = isset($_GET['seller']) ? $_GET['seller'] : [];
$selectedRatings = isset($_GET['rating']) ? $_GET['rating'] : [];

// Get unique sellers from DB
$sellers = [];
$result = $mysqli->query("SELECT DISTINCT seller FROM products");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sellers[] = $row['seller'];
    }
    $result->free();
}

// Price ranges
$prices = [
    'Under $200' => [0, 200],
    '$200 - $500' => [200, 500],
    '$500 - $1000' => [500, 1000],
    'Above $1000' => [1000, PHP_INT_MAX]
];

// Build SQL with average rating and review count
$sql = "
    SELECT 
        p.id, 
        p.title, 
        p.description, 
        p.price, 
        p.seller,
        IFNULL(AVG(r.rating), 0) AS avg_rating,
        COUNT(r.id) AS review_count
    FROM products p
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE 1=1
";
$params = [];
$types = "";

// Search filter
if ($searchQuery !== '') {
    $sql .= " AND p.title LIKE ?";
    $params[] = "%$searchQuery%";
    $types .= "s";
}

// Seller filter
if (!empty($selectedSellers)) {
    $placeholders = implode(',', array_fill(0, count($selectedSellers), '?'));
    $sql .= " AND p.seller IN ($placeholders)";
    foreach ($selectedSellers as $seller) {
        $params[] = $seller;
        $types .= 's';
    }
}

// Price filter
if (!empty($selectedPrices)) {
    $priceConditions = [];
    foreach ($selectedPrices as $label) {
        if (isset($prices[$label])) {
            $min = $prices[$label][0];
            $max = $prices[$label][1];
            $priceConditions[] = "(p.price >= $min AND p.price <= $max)";
        }
    }
    if (!empty($priceConditions)) {
        $sql .= " AND (" . implode(" OR ", $priceConditions) . ")";
    }
}

// Group by product for avg_rating
$sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

// Prepare and execute
$stmt = $mysqli->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...array_map(fn($p) => $p, $params));
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['id'],
        'name' => $row['title'],
        'price' => $row['price'],
        'seller' => $row['seller'],
        'status' => $row['description'],
        'rating' => $row['avg_rating'], // keep as float
        'review_count' => $row['review_count'],
    ];
}

$stmt->close();
?>

<section class="hero">
    <h2>Find Amazing Deals Every Day!</h2>
    <p>Shop thousands of unique products from trusted sellers. Discover big discounts and exclusive offers across all categories.</p>
</section>

<section class="content-wrapper">
   <aside class="sidebar">
        <form method="GET">
            <h3>Filter by Price</h3>
            <?php foreach ($prices as $label => $range): ?>
                <label>
                    <input type="checkbox" name="price[]" value="<?php echo htmlspecialchars($label); ?>" 
                        <?php echo in_array($label, $selectedPrices) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($label); ?>
                </label><br>
            <?php endforeach; ?>

            <h3>Filter by Seller</h3>
            <?php foreach ($sellers as $seller): ?>
                <label>
                    <input type="checkbox" name="seller[]" value="<?php echo htmlspecialchars($seller); ?>" 
                        <?php echo in_array($seller, $selectedSellers) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($seller); ?>
                </label><br>
            <?php endforeach; ?>

            <h3>Filter by Customer Rating</h3>
            <?php
            $starRatings = [3, 4, 5];
            foreach ($starRatings as $stars): ?>
                <label>
                    <input type="checkbox" name="rating[]" value="<?php echo $stars; ?>" 
                        <?php echo in_array($stars, $selectedRatings) ? 'checked' : ''; ?>>
                    <?php
                    for ($i = 0; $i < $stars; $i++) echo '⭐';
                    for ($i = $stars; $i < 5; $i++) echo '☆';
                    ?>
                </label><br>
            <?php endforeach; ?>

            <button type="submit" class="btn-primary">Apply</button>
            <button><a href="index.php" 
                style="text-decoration: none; color: inherit; cursor: pointer;" 
                onmouseover="this.style.textDecoration='none';" 
                onmouseout="this.style.textDecoration='none';">
                Clear
                </a>
            </button>
        </form>
    </aside>

    <main class="product-listings">
        <h2>Latest listings</h2>
        <div class="grid">
            <?php foreach ($products as $product): ?>
                <?php
                    $imageName = strtolower(str_replace(' ', '-', $product['name'])) . ".png";
                    $imagePath = "assets/images/" . $imageName;
                    if (!file_exists($imagePath)) {
                        $imagePath = "assets/images/placeholder.jpg";
                    }

                    // Floor the rating to match product page
                    $fullStars = floor($product['rating']);
                    $emptyStars = 5 - $fullStars;
                ?>
                <article class="card">
                    <div class="card-image">
                        <a href="product.php?id=<?php echo htmlspecialchars($product['id']); ?>">
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </a>
                    </div>
                    <div class="details">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="seller">Seller: <?php echo htmlspecialchars($product['seller']); ?></div>
                        <div class="price">Price: $<?php echo number_format($product['price'], 2); ?></div>
                        <div class="status"><?php echo htmlspecialchars($product['status']); ?></div>

                        <!-- ================= Rating ================= -->
                        <div class="card-rating">
                            <?php
                                echo str_repeat("⭐", $fullStars);
                                echo str_repeat("☆", $emptyStars);
                            ?>
                            <span>(<?php echo $product['review_count']; ?> reviews)</span>
                        </div>

                    </div>
                    <div class="actions">
                        <a href="product.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="btn-secondary" style="text-decoration:none; display:inline-block; text-align:center; padding:0.4rem 0.8rem; border-radius:999px; background:#e5e7eb; color:#111827;">
                            View
                        </a>

                        <form method="POST" action="add_to_cart.php" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button class="btn-primary" type="submit">Add to cart</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>
</section>

<?php
include __DIR__ . '/includes/footer.php';
?>

<style>
.card .card-rating {
    font-size: 1rem;
    color: #fbbf24; /* golden stars */
    margin-top: 0.25rem;
}
.card .card-rating span {
    font-size: 0.85rem;
    color: #555;
    margin-left: 0.25rem;
}
</style>

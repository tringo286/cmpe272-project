<?php
session_start();
include __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

// Get search query and filters from GET
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$selectedPrices = isset($_GET['price']) ? $_GET['price'] : [];
$selectedSellers = isset($_GET['seller']) ? $_GET['seller'] : [];

// Optional: Get unique sellers from DB for filter
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

// Build SQL with filters
$sql = "SELECT id, title, description, price, seller FROM products WHERE 1=1";
$params = [];
$types = "";

// Search filter
if ($searchQuery !== '') {
    $sql .= " AND title LIKE ?"; //Look up the title column in the products table
    $params[] = "%$searchQuery%";
    $types .= "s";
}

// Seller filter
if (!empty($selectedSellers)) {
    $placeholders = implode(',', array_fill(0, count($selectedSellers), '?'));
    $sql .= " AND seller IN ($placeholders)";
    foreach ($selectedSellers as $seller) {
        $params[] = $seller;
        $types .= "s";
    }
}

// Price filter
if (!empty($selectedPrices)) {
    $priceConditions = [];
    foreach ($selectedPrices as $label) {
        if (isset($prices[$label])) {
            $min = $prices[$label][0];
            $max = $prices[$label][1];
            $priceConditions[] = "(price >= $min AND price <= $max)";
        }
    }
    if (!empty($priceConditions)) {
        $sql .= " AND (" . implode(" OR ", $priceConditions) . ")";
    }
}

$sql .= " ORDER BY created_at DESC";

// Prepare and execute
$stmt = $mysqli->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'name' => $row['title'],
        'price' => $row['price'],
        'seller' => $row['seller'],
        'status' => $row['description'],
    ];
}

$stmt->close();

// Example customer reviews (static)
$reviews = [
    '"Great product!" - Alice',
    '"Fast shipping and quality." - Bob',
    '"Excellent support and reliable." - Carol'
];
?>

<section class="hero">
    <h2>Find Amazing Deals Every Day!</h2>
    <p>Shop thousands of unique products from trusted sellers. Discover big discounts and exclusive offers across all categories.</p>
</section>

<section class="content-wrapper">
    <aside class="sidebar">
        <h3>Filter by Price</h3>
        <form method="GET">
            <?php foreach ($prices as $label => $max): ?>
                <label>
                    <input type="checkbox" name="price[]" value="<?php echo htmlspecialchars($label); ?>">
                    <?php echo htmlspecialchars($label); ?>
                </label><br>
            <?php endforeach; ?>

            <h3>Filter by Seller</h3>
            <?php
            $sellers = ['TechPro', 'Lambert Nguyen Company', 'PureBite'];
            foreach ($sellers as $seller): ?>
                <label>
                    <input type="checkbox" name="seller[]" value="<?php echo htmlspecialchars($seller); ?>">
                    <?php echo htmlspecialchars($seller); ?>
                </label><br>
            <?php endforeach; ?>
        </form>

        <h3>Filter by Customer Rating</h3>
        <form method="GET">
            <?php
            $starRatings = [3, 4, 5];
            foreach ($starRatings as $stars): ?>
                <label>
                    <input type="checkbox" name="rating[]" value="<?php echo $stars; ?>">
                    <?php
                    for ($i = 0; $i < $stars; $i++) echo '⭐';
                    for ($i = $stars; $i < 5; $i++) echo '☆';
                    ?>
                </label><br>
            <?php endforeach; ?>

            <button type="submit" class="btn-primary">Apply</button>
            <button type="reset" class="btn-secondary">Clear</button>
        </form>
    </aside>

    <main class="product-listings">
        <h2>Latest listings</h2>
        <div class="grid">
            <?php foreach ($products as $product): ?>

                <?php
                // Create image filename based on product name:
                $imageName = strtolower(str_replace(' ', '-', $product['name'])) . ".png";
                $imagePath = "assets/images/" . $imageName;

                // If image doesn't exist → fallback to placeholder
                if (!file_exists($imagePath)) {
                    $imagePath = "assets/images/placeholder.jpg";
                }
                ?>

                <article class="card">

                    <!-- Product Image -->
                    <div class="card-image">
                        <img src="<?php echo $imagePath; ?>"
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>

                    <!-- Product Details -->
                    <div class="details">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="seller">Seller: <?php echo htmlspecialchars($product['seller']); ?></div>
                        <div class="price">Price: $<?php echo number_format($product['price'], 2); ?></div>
                        <div class="status"><?php echo htmlspecialchars($product['status']); ?></div>
                    </div>

                    <!-- Buttons -->
                    <div class="actions">
                        <button class="btn-secondary" type="button">View</button>
                        <button class="btn-primary" type="button">Add to cart</button>
                    </div>

                </article>

            <?php endforeach; ?>
        </div>
    </main>
</section>

<?php
include __DIR__ . '/includes/footer.php';
?>

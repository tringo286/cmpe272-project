<?php
// SAFE session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

// Read filters
$searchQuery     = $_GET['q']      ?? '';
$selectedPrices  = $_GET['price']  ?? [];
$selectedSellers = $_GET['seller'] ?? [];
$selectedRatings = $_GET['rating'] ?? [];

// Get sellers
$sellers = [];
$res = $mysqli->query("SELECT DISTINCT seller FROM products ORDER BY seller ASC");
while ($row = $res->fetch_assoc()) {
    $sellers[] = $row['seller'];
}

// Price ranges
$prices = [
    'Under $200'     => [0, 200],
    '$200 - $500'    => [200, 500],
    '$500 - $1000'   => [500, 1000],
    'Above $1000'    => [1000, PHP_INT_MAX]
];


// ===============================================
// BUILD SQL QUERY
// ===============================================
$top5Selected = isset($_GET['top5']);

$sql = "
    SELECT 
        p.id,
        p.slug,
        p.title,
        p.description,
        p.price,
        p.seller,
        IFNULL(AVG(r.rating),0) AS avg_rating,
        COUNT(r.id) AS review_count
    FROM products p
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE 1=1
";

$params = [];
$types = '';

// Search term
if ($searchQuery !== '') {
    $sql .= " AND p.title LIKE ?";
    $params[] = "%$searchQuery%";
    $types .= "s";
}

// Seller filter
if (!empty($selectedSellers)) {
    $placeholders = implode(',', array_fill(0, count($selectedSellers), '?'));
    $sql .= " AND p.seller IN ($placeholders)";
    foreach ($selectedSellers as $s) {
        $params[] = $s;
        $types .= "s";
    }
}

// Price filter
if (!empty($selectedPrices)) {
    $priceConditions = [];
    foreach ($selectedPrices as $label) {
        if (isset($prices[$label])) {
            [$min, $max] = $prices[$label];
            $priceConditions[] = "(p.price >= $min AND p.price <= $max)";
        }
    }
    if (!empty($priceConditions)) {
        $sql .= " AND (" . implode(" OR ", $priceConditions) . ")";
    }
}

// Rating filter
$having = [];
if (!empty($selectedRatings)) {
    foreach ($selectedRatings as $rating) {
        $rating = (int)$rating;
        $having[] = "AVG(r.rating) >= $rating";
    }
}

$sql .= " GROUP BY p.id";

// Apply rating HAVING
if (!empty($having)) {
    $sql .= " HAVING " . implode(" OR ", $having);
}

// Apply Top 5 logic
if ($top5Selected) {
    $sql .= " ORDER BY avg_rating DESC LIMIT 5";
} else {
    $sql .= " ORDER BY p.created_at DESC";
}


// ===============================================
// EXECUTE QUERY
// ===============================================
$stmt = $mysqli->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$stmt->close();

?>

<!-- ======================== Hero Section ======================== -->
<section class="hero">
    <h2>Find Amazing Deals Every Day!</h2>
    <p>Shop thousands of unique products from trusted sellers.</p>
</section>

<!-- ======================== Modern Partner Section ======================== -->
<section class="partners-inline-modern">
    <div class="partners-row">
        <span class="partners-text">Our trusted partners</span>
        
        <a href="redirect.php?company_id=1&url=https://php-mysql-hosting-project.onrender.com" target="_blank" class="partner-btn">TechPro</a>
        
        <a href="redirect.php?company_id=2&url=https://lambertnguyen.cloud/" target="_blank" class="partner-btn">Lambert Nguyen Company</a>
        
        <a href="redirect.php?company_id=3&url=http://anukrithimyadala.42web.io/" target="_blank" class="partner-btn">PureBite</a>

    </div>

</section>

<!-- ======================== Content Wrapper ======================== -->
<section class="content-wrapper">

    <!-- ========== SIDEBAR ========== -->
    <aside class="sidebar">
        <form method="GET">

            <h3>Top 5 Products</h3>
            <label style="display: block; margin-bottom: 30px;">
                <input type="checkbox" name="top5" value="1" <?= isset($_GET['top5']) ? 'checked' : '' ?>>
                Show Top 5 Highest Rated
            </label>

            <h3>Filter by Price</h3>
            <?php foreach ($prices as $label => $r): ?>
                <label>
                    <input type="checkbox" name="price[]" value="<?= htmlspecialchars($label) ?>"
                        <?= in_array($label, $selectedPrices) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </label><br>
            <?php endforeach; ?>


            <h3>Filter by Seller</h3>
            <?php foreach ($sellers as $seller): ?>
                <label>
                    <input type="checkbox" name="seller[]" value="<?= htmlspecialchars($seller) ?>"
                        <?= in_array($seller, $selectedSellers) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($seller) ?>
                </label><br>
            <?php endforeach; ?>


            <h3>Filter by Rating</h3>
            <?php foreach ([3, 4, 5] as $star): ?>
                <label>
                    <input type="checkbox" name="rating[]" value="<?= $star ?>"
                        <?= in_array($star, $selectedRatings) ? 'checked' : '' ?>>
                    <?= str_repeat("⭐", $star) . str_repeat("☆", 5 - $star) ?>
                </label><br>
            <?php endforeach; ?>

            <button type="submit" class="btn-primary">Apply</button>

            <a href="index.php" class="btn-clear">Clear</a>

        </form>
    </aside>


    <!-- ========== PRODUCT LISTINGS ========== -->
    <main class="product-listings">
    <h2>Latest listings</h2>
    <div class="grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $p): ?>
                <?php
                    $slug = $p['slug'];
                    $imagePath = "assets/images/{$slug}.png";

                    $fullStars = floor($p['avg_rating']);
                    $emptyStars = 5 - $fullStars;
                ?>
                <article class="card">
                    <div class="card-image">
                        <a href="product.php?id=<?= $p['id'] ?>">
                            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                        </a>
                    </div>

                    <div class="details">
                        <h3><?= htmlspecialchars($p['title']) ?></h3>
                        <div class="seller">Seller: <?= htmlspecialchars($p['seller']) ?></div>
                        <div class="price">$<?= number_format($p['price'], 2) ?></div>

                        <div class="card-rating">
                            <?= str_repeat("⭐", $fullStars) ?>
                            <?= str_repeat("☆", $emptyStars) ?>
                            <span>(<?= $p['review_count'] ?> reviews)</span>
                        </div>
                    </div>

                    <div class="actions">
                        <button class="btn-secondary">
                            <a href="product.php?id=<?= $p['id'] ?>" style="text-decoration:none; color:inherit;">
                                View
                            </a>
                        </button>

                        <form action="add_to_cart.php" method="POST" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-primary">Add to cart</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="
                grid-column: 1 / -1;
                text-align: center;
                padding: 2rem;
                font-size: 1.1rem;
                color: #6b7280;
                border: 1px solid #e5e7eb;
                border-radius: 1rem;
                background: #f9fafb;
            ">
                😕 No products found matching your criteria.
            </div>
        <?php endif; ?>
    </div>
</main>


</section>

<?php include __DIR__ . '/includes/footer.php'; ?>


<style>
    .partners-inline-modern {
    text-align: center;
    margin: 2rem 0;
}

.partners-row {
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
}

.partners-text {
    font-weight: 700;
    font-size: 1.1rem;
    color: #111827;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.partner-btn {
    background: linear-gradient(135deg, #4f46e5, #3b82f6); /* gradient for modern feel */
    color: #fff;
    font-weight: 600;
    padding: 0.5rem 1.2rem;
    border-radius: 9999px;   /* pill shape */
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.partner-btn:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);
}
    /* Keep your card-rating styling */
    .card .card-rating {
        font-size: 1rem;
        color: #fbbf24;
    }
    .card .card-rating span {
        font-size: 0.85rem;
        color: #555;
    }

    .btn-clear {
        background: #e5e7eb;
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        text-decoration: none;
        color: #111;
        margin-top: 0.5rem;
        font-size: 1rem;
        cursor: pointer;
        box-sizing: border-box;
        display: block;
        width: 100%;
        text-align: center;
    }

    .btn-clear:hover {
        background: #d4d4d8;
    }
</style>

<?php
session_start();
include __DIR__ . '/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id === 0) {
    header('Location: index.php');
    exit();
}

// Handle Add to Cart action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

    // Check if product already exists in cart
    $stmtCheck = $mysqli->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmtCheck->bind_param("ii", $userId, $product_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($row = $resultCheck->fetch_assoc()) {
        // Product exists, update quantity
        $newQuantity = $row['quantity'] + $quantity;
        $stmtUpdate = $mysqli->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmtUpdate->bind_param("ii", $newQuantity, $row['id']);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    } else {
        // Insert new product
        $stmtInsert = $mysqli->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmtInsert->bind_param("iii", $userId, $product_id, $quantity);
        $stmtInsert->execute();
        $stmtInsert->close();
    }
    $stmtCheck->close();

    // Redirect to cart page
    header("Location: cart.php");
    exit;
}

// Fetch product
$product = null;
$stmtProduct = $mysqli->prepare("SELECT id, title, description, price, seller, details, slug FROM products WHERE id = ?");
$stmtProduct->bind_param("i", $product_id);
$stmtProduct->execute();
$result = $stmtProduct->get_result();
if ($row = $result->fetch_assoc()) {
    $product = $row;
}
$stmtProduct->close();

// Fetch reviews
$reviews = [];
$stmtReviews = $mysqli->prepare("
    SELECT r.rating, r.review_text, r.created_at, u.username
    FROM reviews r
    JOIN project_users u ON r.user_id = u.id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$stmtReviews->bind_param("i", $product_id);
$stmtReviews->execute();
$result = $stmtReviews->get_result();

while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}
$stmtReviews->close();

if (!$product) {
    header('Location: index.php');
    exit();
}

include __DIR__ . '/includes/header.php';

// Image logic
$imagePath = "assets/images/" . ($product['slug'] ?? '') . ".png";
if (!file_exists($imagePath) || empty($product['slug'])) {
    $imagePath = "assets/images/placeholder.jpg";
}
?>

<section class="hero">
    <h2><?php echo htmlspecialchars($product['title']); ?></h2>
    <p>by <?php echo htmlspecialchars($product['seller']); ?></p>
</section>

<section class="content-wrapper">

    <div class="product-detail-amz">

        <!-- LEFT COLUMN: IMAGE -->
        <div class="amz-left">
            <div class="amz-image-box">
                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
            </div>
        </div>

        <!-- MIDDLE COLUMN -->
        <div class="amz-middle">
            <h2 class="amz-title"><?php echo htmlspecialchars($product['title']); ?></h2>

            <?php
            $totalRating = 0;
            foreach ($reviews as $rev) $totalRating += $rev['rating'];
            $reviewCount = count($reviews);
            $avgRating = $reviewCount > 0 ? round($totalRating / $reviewCount, 1) : 0;
            ?>

            <div class="amz-rating">
                <?php
                    $filledStars = floor($avgRating);
                    $emptyStars = 5 - $filledStars;
                    echo str_repeat("⭐", $filledStars) . str_repeat("☆", $emptyStars);
                ?>
                <span>(<?php echo $reviewCount; ?> reviews)</span>
            </div>

            <hr>

            <div class="amz-description">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>

            <p><strong>Seller:</strong> <?php echo htmlspecialchars($product['seller']); ?></p>

            <h4>Product Details</h4>
            <p><?php echo !empty($product['details']) ? nl2br(htmlspecialchars($product['details'])) : 'No additional details available.'; ?></p>
        </div>

        <!-- RIGHT COLUMN: BUY BOX -->
        <div class="amz-right">
            <div class="amz-buy-box">
                <div class="amz-price">$<?php echo number_format($product['price'], 2); ?></div>
                <p class="amz-stock">In Stock</p>

                <!-- ADD TO CART FORM -->
                <form method="POST">
                    <label>Quantity: 
                        <input type="number" name="quantity" value="1" min="1" style="width:60px;">
                    </label>
                    <button type="submit" name="add_to_cart" class="amz-btn-buy">Add to Cart</button>
                </form>

                <p class="amz-seller-small">Sold by <?php echo htmlspecialchars($product['seller']); ?></p>
            </div>
        </div>

    </div>

    <!-- REVIEWS -->
    <div class="amz-reviews">
        <h3>Customer Reviews</h3>
        <?php if (empty($reviews)): ?>
            <p>No reviews yet. Be the first!</p>
        <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
                <div class="amz-review">
                    <strong><?php echo htmlspecialchars($rev['username']); ?></strong>
                    <?php echo str_repeat("⭐", $rev['rating']) . str_repeat("☆", 5 - $rev['rating']); ?>
                    <p><?php echo nl2br(htmlspecialchars($rev['review_text'])); ?></p>
                    <small><?php echo $rev['created_at']; ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/includes/review_form.php'; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>


<!-- ===================== AMAZON STYLE CSS ===================== -->
<style>
.content-wrapper {
    display: flex;
    flex-direction: column;
    gap: 3rem;
    margin-top: 2rem;
}

.product-detail-amz {
    display: grid;
    grid-template-columns: 1.4fr 2fr 1fr;
    gap: 2rem;
}

/* IMAGE BOX */
.amz-image-box {
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1rem;
    background: #fff;
    display: flex;
    justify-content: center;
}
.amz-image-box img {
    width: 100%;
    height: 300px;       
    object-fit: cover;   
    border-radius: 6px;
}

/* MIDDLE INFO */
.amz-title {
    font-size: 1.9rem;
    font-weight: 700;
    margin-bottom: .3rem;
}
.amz-rating {
    color: #f59e0b;
    font-size: 1.1rem;
}
.amz-description {
    margin-top: 1rem;
    margin-bottom: 1rem;
    font-size: 1rem;
    line-height: 1.6;
}

/* BUY BOX */
.amz-buy-box {
    border: 1px solid #d1d5db;
    padding: 1rem;
    border-radius: 0.75rem;
    background: #fafafa;
}
.amz-price {
    font-size: 2rem;
    font-weight: 700;
    color: #b12704;
}
.amz-stock {
    margin-top: 0.25rem;
    color: #059669;
    font-weight: 600;
}
.amz-btn-buy {
    width: 100%;
    background: #ffd814;
    padding: .75rem;
    border: none;
    margin-top: 1rem;
    font-size: 1.1rem;
    font-weight: bold;
    border-radius: .5rem;
    cursor: pointer;
}
.amz-btn-buy:hover {
    background: #f7ca00;
}
.amz-btn-list {
    width: 100%;
    background: #e5e7eb;
    padding: .75rem;
    border: none;
    margin-top: .5rem;
    font-size: 1.1rem;
    border-radius: .5rem;
    cursor: pointer;
}
.amz-seller-small {
    margin-top: 1rem;
    font-size: .9rem;
}

/* REVIEWS SECTION */
.amz-reviews {
    width: 100%;
    background: #fff;
    padding: 2rem;
    border-radius: 0.75rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    box-sizing: border-box;;
}
.amz-reviews h3 {
    font-size: 1.6rem;
    margin-bottom: 1.5rem;
}
.amz-review {
    padding: 1rem 0;
    border-bottom: 1px solid #e5e7eb;
}
.amz-review:last-child {
    border-bottom: none;
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .product-detail-amz {
        grid-template-columns: 1fr;
    }
}
</style>

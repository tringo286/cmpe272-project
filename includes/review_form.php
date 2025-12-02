<?php
// review_form.php
// $product must be available when including this file

// Determine current page URL for redirect after login
$currentUrl = $_SERVER['REQUEST_URI'];
?>

<div class="add-review-section">
    <h3>Review this product</h3>
    <p>Share your thoughts with other customers</p>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- User not logged in: show button -->
        <a href="login.php?redirect=<?php echo urlencode($currentUrl); ?>" 
           class="btn-write-review">Write a customer review</a>
    <?php else: ?>
        <!-- User logged in: show form -->
        <h4>Write a customer review</h4>
        <form method="POST" action="submit_review.php" class="review-form">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

            <label for="rating">Rating:</label>
            <select id="rating" name="rating" required>
                <option value="5">★★★★★</option>
                <option value="4">★★★★☆</option>
                <option value="3">★★★☆☆</option>
                <option value="2">★★☆☆☆</option>
                <option value="1">★☆☆☆☆</option>
            </select>

            <label for="review_text">Your Review:</label>
            <textarea id="review_text" name="review_text" rows="5" placeholder="Write your review here..." required></textarea>

            <button type="submit" class="btn-submit-review">Submit Review</button>
        </form>
    <?php endif; ?>
</div>

<style>
.add-review-section {
    background: #fff;
    padding: 2rem;
    border-radius: 0.75rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    max-width: 800px;
    margin: 2rem auto;
    box-sizing: border-box;
}

.add-review-section h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.add-review-section p {
    margin-bottom: 1.5rem;
    color: #374151;
}

.btn-write-review {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background-color: #ffd814;
    color: #111827;
    font-weight: 600;
    border-radius: 0.5rem;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    transition: background 0.2s;
    border: none;
}

.btn-write-review:hover {
    background-color: #f7ca00;
}

.review-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.review-form label {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #111827;
}

.review-form select,
.review-form textarea {
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    width: 100%;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.review-form select:focus,
.review-form textarea:focus {
    border-color: #232f3e;
    box-shadow: 0 0 5px rgba(35, 47, 62, 0.3);
    outline: none;
}

.btn-submit-review {
    width: 200px;
    background-color: #ffd814;
    color: #111827;
    font-weight: 600;
    border-radius: 0.5rem;
    border: none;
    padding: 0.75rem;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
    align-self: flex-start;
}

.btn-submit-review:hover {
    background-color: #f7ca00;
    transform: translateY(-1px);
}

@media (max-width: 600px) {
    .add-review-section {
        padding: 1.5rem;
        margin: 1rem;
    }

    .btn-submit-review {
        width: 100%;
    }
}
</style>

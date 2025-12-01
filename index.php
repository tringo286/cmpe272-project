<?php
// Simple fake data for a marketplace listing grid
$products = [
    [
        'name' => 'Wireless Headphones',
        'price' => 59.99,
        'seller' => 'TechWorld',
        'status' => 'In stock'
    ],
    [
        'name' => 'Organic Coffee Beans',
        'price' => 18.50,
        'seller' => 'Cafe Roasters',
        'status' => 'Low stock'
    ],
    [
        'name' => 'Yoga Mat',
        'price' => 24.99,
        'seller' => 'Fit&Flex',
        'status' => 'In stock'
    ],
    [
        'name' => 'Desk Lamp',
        'price' => 32.00,
        'seller' => 'HomeOffice Co.',
        'status' => 'In stock'
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Simple Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            background: #f5f5f7;
            color: #111827;
        }
        header {
            background: #111827;
            color: #f9fafb;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header h1 {
            margin: 0;
            font-size: 1.25rem;
        }
        header nav a {
            color: #e5e7eb;
            margin-left: 1rem;
            text-decoration: none;
            font-size: 0.9rem;
        }
        header nav a:hover {
            color: #ffffff;
        }
        main {
            max-width: 960px;
            margin: 1.5rem auto 3rem;
            padding: 0 1rem;
        }
        .hero {
            background: #111827;
            color: #f9fafb;
            border-radius: 0.75rem;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.5rem;
        }
        .hero h2 {
            margin: 0 0 0.5rem;
            font-size: 1.5rem;
        }
        .hero p {
            margin: 0 0 1rem;
            font-size: 0.95rem;
            color: #e5e7eb;
        }
        .hero a {
            display: inline-block;
            padding: 0.5rem 0.9rem;
            border-radius: 999px;
            background: #f97316;
            color: #111827;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
        }
        .hero a:hover {
            background: #fb923c;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }
        .card {
            background: #ffffff;
            border-radius: 0.75rem;
            padding: 1rem 1.1rem;
            box-shadow: 0 5px 15px rgba(15, 23, 42, 0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card h3 {
            margin: 0 0 0.25rem;
            font-size: 1.05rem;
        }
        .card .seller {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 0.75rem;
        }
        .card .price {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }
        .card .status {
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
            color: #059669;
        }
        .card .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.5rem;
        }
        .card button {
            border: none;
            border-radius: 999px;
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            cursor: pointer;
        }
        .card .btn-primary {
            background: #3b82f6;
            color: #f9fafb;
        }
        .card .btn-primary:hover {
            background: #2563eb;
        }
        .card .btn-secondary {
            background: #e5e7eb;
            color: #111827;
        }
        .card .btn-secondary:hover {
            background: #d1d5db;
        }
        footer {
            text-align: center;
            font-size: 0.8rem;
            color: #9ca3af;
            padding: 1rem 0 1.5rem;
        }
    </style>
</head>
<body>
<header>
    <h1>Simple Marketplace</h1>
    <nav>
        <a href="#">Browse</a>
        <a href="#">Sell</a>
        <a href="#">Account</a>
    </nav>
</header>

<main>
    <section class="hero">
        <h2>Discover and sell unique items</h2>
        <p>Demo marketplace homepage built with plain PHP. Replace this sample with your own categories, filters, and product data.</p>
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
</main>

<footer>
    Simple demo marketplace &middot; PHP + Docker
</footer>
</body>
</html>

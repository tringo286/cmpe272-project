<?php
$cartCount = 0;

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $cartCount = intval($row['total']);
    }
    $stmt->close();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>MarketHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<header>
    <div class="header-left">
        <a href="/index.php" style="text-decoration: none; color: inherit;">
            <h1>MarketHub</h1>
        </a>
    </div>

    <div class="header-center">
       <form method="GET" class="search-form">
            <input type="text" name="q" placeholder="Search products..." aria-label="Search products" />
            <button type="submit">🔍</button>
        </form>

    </div>

    <nav class="header-right">
        <?php if (isset($_SESSION['username'])): ?>
            <div class="dropdown">
                <button class="dropbtn">Hello, <?= htmlspecialchars($_SESSION['username']) ?> ▼</button>
                <div class="dropdown-content">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="/admin.php" style="text-decoration: none;">Admin Panel</a>
                    <?php endif; ?>
                    <a href="/orders.php" style="text-decoration: none;">Orders</a>
                    <a href="/logout.php" style="text-decoration: none;">Logout</a>
                </div>
            </div>

            <a href="/cart.php" class="cart-link" aria-label="Cart">
                🛒<span class="cart-count"><?php echo $cartCount; ?></span>
            </a>
        <?php else: ?>
            <a href="/login.php" class="account-link">Hello, Log in</a>
            <!-- <a href="/orders.php" class="orders-link">Orders</a> -->
            <a href="/cart.php" class="cart-link" aria-label="Cart">
                🛒<span class="cart-count">0</span>
            </a>
        <?php endif; ?>
    </nav>

</header>

<style>
    body {
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        margin: 0;
        background: #f5f5f7;
        color: #111827;
    }

    main {
        max-width: 100vw;
        margin: 1rem;
        padding: 0 1rem;
    }

    /* Dropdown container */
    header .dropdown {
        position: relative;
        display: inline-block;
    }

    header .dropbtn {
        background: none;
        border: none;
        color: white;
        font-size: 1rem;
        cursor: pointer;
        font-weight: 600;
        padding: 0;
    }

    header .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background-color: white;
        min-width: 160px;
        box-shadow: 0px 4px 12px rgba(0,0,0,0.15);
        border-radius: 0.5rem;
        z-index: 1000;
        overflow: hidden;
    }

    header .dropdown-content a {
        color: #111827;
        padding: 0.5rem 1rem;
        text-decoration: none;
        display: block;
        font-size: 0.95rem;
    }

    header .dropdown-content a:hover {
        background-color: #f3f4f6;
    }

    header .dropdown:hover .dropdown-content {
        display: block;
    }

    header .dropbtn::after {
        content: '';
        display: inline-block;
        margin-left: 0.25rem;
    }



    .hero {
        background: #111827;
        color: #f9fafb;
        border-radius: 0.75rem;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
        text-align: center;
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

    /* Product grid: 2 items per row */
    .product-listings .grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    /* CARD — Modern look */
    .card {
        display: flex;
        flex-direction: row;
        gap: 1rem;

        padding: 1rem 1.1rem;
        background: #ffffff;
        border-radius: 1rem;

        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(0,0,0,0.05);

        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }

    /* Subtle card highlight glow */
    .card::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(99,102,241,0.2));
        opacity: 0;
        transition: opacity 0.35s ease;
        z-index: 0;
    }

    /* Hover effect */
    .card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
    }

    .card:hover::before {
        opacity: 0.1;
    }

    /* IMAGE section */
    .card-image {
        width: 140px;
        height: 140px;
        border-radius: 0.6rem;
        overflow: hidden;
        flex-shrink: 0;
        background: #f3f4f6;
        position: relative;
    }

    .card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;

        transition: transform 0.35s ease;
    }

    /* Image zoom on hover */
    .card:hover .card-image img {
        transform: scale(1.07);
    }

    /* DETAILS */
    .card .details {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        z-index: 1;
    }

    .card h3 {
        margin: 0 0 0.25rem;
        font-size: 1.1rem;
        font-weight: 700;
        color: #111827;
    }

    .card .seller {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 0.6rem;
    }

    .card .price {
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 0.3rem;
        color: #1e40af;
    }

    .card .status {
        font-size: 0.8rem;
        margin-bottom: 0.75rem;
        color: #059669;
    }

    .card .extra-info p {
        margin: 0;
        font-size: 0.85rem;
    }

    /* ACTION BUTTONS */
    .card .actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        justify-content: center;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    .card button {
        border: none;
        border-radius: 999px;
        padding: 0.45rem 0.9rem;
        font-size: 0.8rem;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.25s ease;
    }

    .card .btn-primary {
        background: #3b82f6;
        color: #f9fafb;
    }

    .card .btn-primary:hover {
        background: #2563eb;
        transform: translateY(-2px);
    }

    .card .btn-secondary {
        background: #e5e7eb;
        color: #111827;
    }

    .card .btn-secondary:hover {
        background: #d1d5db;
        transform: translateY(-2px);
    }

    /* CONTENT WRAPPER (Sidebar + Listings) */
    .content-wrapper {
        display: grid;
        grid-template-columns: 1fr 3fr;
        gap: 2rem;
        max-width: 1800px;
        margin: 0 auto 3rem;
        align-items: start;
    }

    /* SIDEBAR — unchanged */
    .sidebar {
        background: #ffffff;
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        font-size: 0.9rem;
        color: #111827;
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        position: sticky;
        top: 1rem;
        transition: all 0.3s ease;
    }

    .sidebar h3 {
        margin-top: 0;
        margin-bottom: 0.5rem;
        font-weight: 700;
        font-size: 1rem;
        color: #111827;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 0.5rem;
    }

    .sidebar label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.6rem;
        cursor: pointer;
        transition: background 0.2s;
        padding: 0.35rem 0.5rem;
        border-radius: 0.5rem;
    }

    .sidebar label:hover {
        background: #f3f4f6;
    }

    .sidebar input[type="checkbox"] {
        accent-color: #3b82f6;
    }

    .sidebar button {
        width: 100%;
        padding: 0.5rem 0;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        border: none;
        margin-top: 0.5rem;
        transition: background 0.2s ease;
    }

    .sidebar .btn-primary {
        background: #3b82f6;
        color: #ffffff;
    }

    .sidebar .btn-primary:hover {
        background: #2563eb;
    }

    .sidebar .btn-secondary {
        background: #e5e7eb;
        color: #111827;
    }

    .sidebar .btn-secondary:hover {
        background: #d1d5db;
    }

    .sidebar .reviews p {
        margin: 0;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    /* Responsive layout */
    @media (max-width: 900px) {
        .sidebar {
            position: relative;
            top: auto;
        }
    }

    @media (max-width: 700px) {
        .product-listings .grid {
            grid-template-columns: 1fr;
        }
    }

    
    header {
        display: flex;
        align-items: center;
        background-color: #232f3e;
        color: white;
        padding: 1rem 1rem;
        gap: 1rem;
        font-family: Arial, sans-serif;
        flex-wrap: nowrap; 
        flex-direction: row; 
    }

   .header-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;    
        cursor: pointer;
        min-width: 180px;
        justify-content: center;
    }

    .header-left h1 {
        font-size: 1.75rem;
        margin: 0;

        user-select: none;
    }

    .header-center {
        flex-grow: 1;
        margin: 0;    
    }

    .search-form {
        display: flex;
        width: 100%;
    }

    .search-form input[type="text"] {
        flex-grow: 1;
        padding: 0.5rem 0.75rem;
        border: none;
        border-radius: 0.25rem 0 0 0.25rem;
        font-size: 1rem;
    }

    .search-form button {
        background-color: #febd69;
        border: none;
        padding: 0 1rem;
        border-radius: 0 0.25rem 0.25rem 0;
        cursor: pointer;
        font-size: 1.2rem;
    }

    .search-form button:hover {
        background-color: #f3a847;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        font-size: 1rem;
        min-width: 200px;
        justify-content: flex-end;
    }

    .header-right a {
        color: white;
        text-decoration: none;
        white-space: nowrap;
    }

    .header-right a:hover {
        text-decoration: underline;
    }

    .cart-link {
        position: relative;
        font-size: 1.8rem;
    }

    .cart-count {
        position: absolute;
        top: -0.3rem;
        right: -0.5rem;
        background: #f08804;
        border-radius: 9999px;
        color: black;
        padding: 0 0.4rem;
        font-weight: bold;
        font-size: 0.8rem;
    }

    .logout-link {
        color: white;
        text-decoration: none;
        margin-left: 0.5rem;
    }

    .logout-link:hover {
        text-decoration: underline;
    }

    /* Mobile and small screens: stack vertically and center */
    @media (max-width: 600px) {
        header {
            flex-direction: column;
            align-items: center;
            flex-wrap: wrap;
            padding: 0.5rem 0.75rem;
            gap: 0.75rem;
        }

        .header-left,
        .header-center,
        .header-right {
            flex: 1 1 100%;
            justify-content: center;
            margin: 0;
        }

        .header-left h1 {
            font-size: 1.3rem;
        }

        .search-form input[type="text"] {
            font-size: 0.9rem;
            padding: 0.4rem 0.5rem;
        }

        .search-form button {
            font-size: 1rem;
            padding: 0 0.8rem;
        }

        .header-right {
            font-size: 0.85rem;
            gap: 0.5rem;
        }

        .cart-link {
            font-size: 1.3rem;
        }
    }
</style>

<main>

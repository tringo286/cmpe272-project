<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>MarketHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/styles/index.css">
</head>
<body>
<header>
    <div class="header-left">
        <h1>MarketHub</h1>
    </div>
    <div class="header-center">
        <form action="/search.php" method="get" class="search-form">
            <input type="text" name="q" placeholder="Search products..." aria-label="Search products" />
            <button type="submit">🔍</button>
        </form>
    </div>
    <nav class="header-right">
        <a href="/account.php" class="account-link">Hello, Sign in</a>
        <a href="/orders.php" class="orders-link">Orders</a>
        <a href="/cart.php" class="cart-link" aria-label="Cart">
            🛒<span class="cart-count">0</span>
        </a>
    </nav>
</header>

<style>
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

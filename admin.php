<?php
session_start();
include __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user is admin
$stmt = $mysqli->prepare("SELECT role FROM project_users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

if ($role !== 'admin') {
    die("Access denied. Admins only.");
}

// Fetch user activity
$sql = "
    SELECT ua.*, u.username, c.name AS company_name, p.title AS product_title
    FROM user_activity ua
    JOIN project_users u ON ua.user_id = u.id
    JOIN companies c ON ua.company_id = c.id
    LEFT JOIN products p ON ua.page_id = p.id AND ua.page_type='product'
    ORDER BY ua.visited_at DESC
    LIMIT 50
";

$result = $mysqli->query($sql);

$visits = [];
while ($row = $result->fetch_assoc()) {
    $visits[] = $row;
}

// Include header
include __DIR__ . '/includes/header.php';
?>

<div class="page-wrapper">
    <div class="shop-content-wrapper">

        <h2 class="dashboard-title">User Activity Dashboard</h2>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Company</th>
                    <th>Page Type</th>
                    <th>Product</th>
                    <th>Visited At</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($visits as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['username']) ?></td>
                        <td><?= htmlspecialchars($v['company_name']) ?></td>

                        <td>
                            <span class="type-badge <?= htmlspecialchars($v['page_type']) ?>">
                                <?= htmlspecialchars(ucfirst($v['page_type'])) ?>
                            </span>
                        </td>

                        <td><?= htmlspecialchars($v['product_title'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($v['visited_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>

<style>

/* PAGE LAYOUT */
.page-wrapper {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 180px);
}

.shop-content-wrapper {
    width: 100%;
    padding: 2rem;
    clear: both;
    box-sizing: border-box;
}

.dashboard-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: #111827;
}

/* ============================
   MODERN ADMIN TABLE
   ============================ */
.admin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #fff;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 8px 22px rgba(0,0,0,0.06);
}

/* HEADER — fancy gradient like Orders page */
.admin-table thead th {
    padding: 1rem;
    background: rgba(35, 47, 62, 1);
    color: white;
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: 0.3px;
    border-bottom: 2px solid rgba(255,255,255,0.12);
}

/* ROW CELLS */
.admin-table td {
    padding: 0.9rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    color: #1f2937;
}

/* Zebra row */
.admin-table tbody tr:nth-child(even) {
    background: #f9fafb;
}

/* Hover highlight */
.admin-table tbody tr:hover td {
    background: #f3f4f6;
    transition: 0.2s ease-in-out;
}

/* ============================
   PAGE TYPE BADGES
   ============================ */
.type-badge {
    padding: 0.35rem 0.7rem;
    border-radius: 0.5rem;
    font-size: 0.78rem;
    font-weight: 600;
    display: inline-block;
    text-transform: capitalize;
}

/* Product */
.type-badge.product {
    background: #dbeafe;
    color: #1e40af;
}

/* Company page */
.type-badge.company {
    background: #dcfce7;
    color: #166534;
}

/* Generic page */
.type-badge.page {
    background: #fef9c3;
    color: #854d0e;
}

/* FOOTER */
footer {
    background: #232f3e;
    color: white;
    text-align: center;
    padding: 1rem;
    flex-shrink: 0;
}
</style>

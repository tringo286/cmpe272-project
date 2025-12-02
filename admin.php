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
                        <td><?= htmlspecialchars($v['page_type']) ?></td>
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
.page-wrapper {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 180px);
}

/* ✨ Unique wrapper for ADMIN PAGE ONLY */
.shop-content-wrapper {
    display: block;            /* Avoid grid from header */
    width: 100%;               /* Full width */
    padding: 2rem;             /* Spacing */
    clear: both;               /* Prevent header layout interference */
    box-sizing: border-box;
}

/* Dashboard title */
.dashboard-title {
    display: block;
    margin-bottom: 1.5rem;
    font-size: 1.8rem;
    font-weight: 600;
}

/* Admin table styling */
.admin-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);    
}

.admin-table th, .admin-table td {
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    text-align: left;
}

.admin-table th {
    background-color: #f3f4f6;
    font-weight: 600;
}

.admin-table tr:nth-child(even) {
    background-color: #f9fafb;
}

footer {
    background: #232f3e;
    color: white;
    text-align: center;
    padding: 1rem;
    flex-shrink: 0;
}
</style>

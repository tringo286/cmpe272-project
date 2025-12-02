<?php
session_start();
require __DIR__ . '/db.php';

// Get redirect URL from GET (default to index.php)
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $redirect = $_POST['redirect'] ?? 'index.php';

    if ($email && $password) {
        $stmt = $mysqli->prepare('SELECT id, username, password, role FROM project_users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($id, $username, $hashedPassword, $role);

        if ($stmt->fetch() && password_verify($password, $hashedPassword)) {
            // Successful login
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role; // store role in session
            header('Location: ' . $redirect);
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
        $stmt->close();
    } else {
        $error = 'Please enter a valid email and password.';
    }
}

include __DIR__ . '/includes/header.php';
?>

<main>
<div class="login-page">
    <form method="post" action="login.php" class="login-form">
        <h2>Login</h2>

        <?php if (isset($error)): ?>
            <div class="login-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Hidden input for redirect -->
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>

        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </div>
    </form>
</div>
</main>

<?php
include __DIR__ . '/includes/footer.php';
?>

<style>
.login-page {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
    padding: 2rem;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.login-page .login-form {
    background: #ffffff;
    padding: 3rem 2.5rem;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    width: 400px;
    max-width: 90%;
    box-sizing: border-box;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.login-page .login-form:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.login-page h2 {
    margin-bottom: 2rem;
    text-align: center;
    color: #232f3e;
    font-size: 1.8rem;
    font-weight: 600;
}

.login-page label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    font-size: 1rem;
    color: #333;
}

.login-page input[type="email"],
.login-page input[type="password"] {
    width: 100%;
    padding: 0.75rem 1rem;
    margin-bottom: 1.25rem;
    border: 1px solid #ccc;
    border-radius: 0.5rem;
    font-size: 1rem;
    box-sizing: border-box;
}

.login-page button {
    width: 100%;
    background-color: #232f3e;
    color: white;
    border: none;
    padding: 0.85rem;
    font-size: 1.1rem;
    border-radius: 0.5rem;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.login-page button:hover {
    background-color: #1a2230;
    transform: translateY(-1px);
}

.login-page .login-error {
    color: red;
    margin-bottom: 1rem;
    text-align: center;
    font-weight: 500;
}

.login-page .signup-link {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 1rem;
}

.login-page .signup-link a {
    color: #232f3e;
    text-decoration: none;
    font-weight: 600;
}

.login-page .signup-link a:hover {
    text-decoration: underline;
}
</style>

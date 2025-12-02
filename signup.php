<?php
session_start();
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!$email || !$username || !$password) {
        $error = "All fields are required.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $stmt = $mysqli->prepare('SELECT id FROM project_users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Insert new user
            $stmt = $mysqli->prepare('INSERT INTO project_users (email, username, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $email, $username, $hashedPassword);
            if ($stmt->execute()) {
                // Redirect to login page instead of logging in automatically
                header('Location: login.php?success=Account created. Please log in.');
                exit();
            } else {
                $error = "Signup failed. Please try again.";
            }
        }
    }
}
include __DIR__ . '/includes/header.php';
?>

<style>
.signup-page {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
    padding: 2rem;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.signup-page .signup-form {
    background: #ffffff;
    padding: 3rem 2.5rem;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    width: 400px;
    max-width: 90%;
    box-sizing: border-box;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.signup-page .signup-form:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.signup-page h2 {
    margin-bottom: 2rem;
    text-align: center;
    color: #232f3e;
    font-size: 1.8rem;
    font-weight: 600;
}

.signup-page label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    font-size: 1rem;
    color: #333;
}

.signup-page input[type="text"],
.signup-page input[type="email"],
.signup-page input[type="password"] {
    width: 100%;
    padding: 0.75rem 1rem;
    margin-bottom: 1.25rem;
    border: 1px solid #ccc;
    border-radius: 0.5rem;
    font-size: 1rem;
    box-sizing: border-box;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.signup-page input:focus {
    border-color: #232f3e;
    box-shadow: 0 0 5px rgba(35, 47, 62, 0.3);
    outline: none;
}

.signup-page button {
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

.signup-page button:hover {
    background-color: #1a2230;
    transform: translateY(-1px);
}

.signup-page .signup-error {
    color: red;
    margin-bottom: 1rem;
    text-align: center;
    font-weight: 500;
}

.signup-page .login-link {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 1rem;
}

.signup-page .login-link a {
    color: #232f3e;
    text-decoration: none;
    font-weight: 600;
}

.signup-page .login-link a:hover {
    text-decoration: underline;
}
</style>

<main>
  <div class="signup-page">
    <form method="post" class="signup-form">
        <h2>Sign Up</h2>

        <?php
        if (isset($error)) {
            echo '<div class="signup-error">' . htmlspecialchars($error) . '</div>';
        }
        ?>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Sign Up</button>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </form>
  </div>
</main>

<?php
include __DIR__ . '/includes/footer.php';
?>

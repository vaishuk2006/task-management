
<?php
session_start();
require_once __DIR__ . '/config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {
        $message = 'Enter your name, a valid email, and a password of at least 8 characters.';
    } else {
        // Check whether the email already exists
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        $emailToSave = $email;

        // Generate a unique email for a demo account
        if ($check->num_rows > 0) {
            $parts = explode('@', $email, 2);

            $emailToSave = $parts[0] . '+' .
                bin2hex(random_bytes(4)) . '@' . $parts[1];
        }

        $check->close();

        // Create the account
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users (name, email, password)
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $name, $emailToSave, $hash);

        if ($stmt->execute()) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['user_name'] = $name;

            header("Location: index.php");
            exit;
        } else {
            $message = 'Registration failed. Please try again.';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account | Task Manager</title>
    <link rel="stylesheet" href="/my_task_project/css/style.css">
</head>
<body class="auth-page">

    <div class="auth-card">
       

        <h1>Create Account</h1>
        <p class="auth-subtitle">
            Join Task Manager and organize your day with ease.
        </p>

        <?php if ($message !== ''): ?>
            <p class="auth-message">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="register.php">
            <label for="name">Full Name : </label>
            <input
                type="text"
                id="name"
                name="name"
                placeholder="Enter your full name"
                autocomplete="name"
                required
            >

            <br><br>

            <label for="email">Email Address : </label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="you@example.com"
                autocomplete="email"
                required
            >

            <br><br>

            <label for="password">Password :</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="At least 8 characters"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <br><br>

            <button type="submit">Create Account →</button>
        </form>

        <br>

        <p class="auth-footer">
            Already have an account?
            <a href="login.php">Login here</a>
        </p>
    </div>

</body>
</html>

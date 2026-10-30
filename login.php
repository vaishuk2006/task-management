
<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");

    if ($email !== "") {
        session_regenerate_id(true);

        $_SESSION["user_id"] = 1;
        $_SESSION["user_name"] = $email;

        header("Location: index.php");
        exit;
    } else {
        $error = "Please enter an email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="/my_task_project/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome Back</h1>

        <?php if (isset($_GET['registered'])): ?>
            <p>Registration successful! Please log in.</p>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <label for="email">Email : </label>
            <input type="email" id="email" name="email"
                   placeholder="Enter any email address" required>

            <br><br>

            <label for="password">Password : </label>
            <input type="password" id="password" name="password"
                   placeholder="Enter any password" required>

            <br><br>

            <button type="submit">Login</button>
        </form>

        <br>
        <p>New user? <br><a href="register.php">Create an account</a></p>
    </div>
</body>
</html>


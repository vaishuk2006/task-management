
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/config/db.php";

$totalTasks = 0;
$completedTasks = 0;
$pendingTasks = 0;

$result = $conn->query("SELECT status FROM tasks");

if ($result) {
    while ($task = $result->fetch_assoc()) {
        $totalTasks++;

        if ($task["status"] === "Completed") {
            $completedTasks++;
        } else {
            $pendingTasks++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Task Manager Dashboard</title>

    <link rel="stylesheet" href="css/style.css?v=16">
</head>

<body>

    <a href="logout.php" class="logout-btn">Logout</a>

    <main class="container">

        <header class="page-header">
            <h1>
                Organize your work.<br>
                <span>Achieve more.</span>
            </h1>

            <p>
                Welcome to your personal task management workspace.
                Plan your day, organize your priorities, and stay on track.
            </p>
        </header>

        <section class="stats-grid">

            <div class="stat-card">
                <p>📋 Total Tasks</p>
                <h2 class="total">
                    <?php echo $totalTasks; ?>
                </h2>
            </div>

            <div class="stat-card">
                <p>✅ Completed Tasks</p>
                <h2 class="completed">
                    <?php echo $completedTasks; ?>
                </h2>
            </div>

            <div class="stat-card">
                <p>⏳ Pending Tasks</p>
                <h2 class="pending">
                    <?php echo $pendingTasks; ?>
                </h2>
            </div>

        </section>

        <section class="welcome-card">

            <h2>Ready to get started?</h2>

            <p>
                Your workspace is ready. Create, update, complete,
                and delete tasks to keep your work organized.
            </p>

            <a href="tasks.php" class="primary-button">
                + Get Started
            </a>

        </section>

    </main>

</body>
</html>

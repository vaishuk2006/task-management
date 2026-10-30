
<?php
require_once __DIR__ . "/config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: tasks.php");
    exit;
}

$title = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$priority = $_POST["priority"] ?? "Medium";
$dueDate = trim($_POST["due_date"] ?? "");

if ($title === "" || mb_strlen($title) > 150) {
    exit("Please enter a task title with no more than 150 characters.");
}

if (!in_array($priority, ["Low", "Medium", "High"], true)) {
    exit("Invalid priority.");
}

if ($dueDate !== "") {
    $date = DateTime::createFromFormat("!Y-m-d", $dueDate);

    if (!$date || $date->format("Y-m-d") !== $dueDate) {
        exit("Invalid due date.");
    }
}

$stmt = $conn->prepare(
    "INSERT INTO tasks (title, description, priority, due_date)
     VALUES (?, ?, ?, NULLIF(?, ''))"
);

$stmt->bind_param("ssss", $title, $description, $priority, $dueDate);
$stmt->execute();

header("Location: tasks.php?message=" . urlencode("Task added successfully!"));
exit;

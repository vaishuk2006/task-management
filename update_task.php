
<?php
require_once __DIR__ . "/config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: tasks.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$status = $_POST["status"] ?? "";

if (!$id || !in_array($status, ["Pending", "Completed"], true)) {
    header("Location: tasks.php?message=" . urlencode("Invalid task update."));
    exit;
}

$stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();

header("Location: tasks.php?message=" . urlencode("Task status updated!"));
exit;

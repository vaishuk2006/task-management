
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: tasks.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$task = $result->fetch_assoc();
$stmt->close();

if (!$task) {
    header('Location: tasks.php?message=' . urlencode('Task not found.'));
    exit;
}

$message = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Task | Task Manager</title>
    <link rel="stylesheet" href="css/style.css?v=10">
</head>
<body>
<main class="container">
    <section class="panel">
        <div class="section-heading">
            <span class="section-kicker">UPDATE YOUR WORK</span>
            <h1>Edit Task</h1>
            <p>Change your task details and save your updates.</p>
        </div>

        <form action="update_task.php" method="POST" class="task-form">
            <input type="hidden" name="id"
                   value="<?php echo (int)$task['id']; ?>">
            <input type="hidden" name="action" value="edit">

            <div class="form-group full-width">
                <label for="title">Task Title *</label>
                <input type="text" id="title" name="title"
                       maxlength="150"
                       value="<?php echo e($task['title']); ?>" required>
            </div>

            <div class="form-group full-width">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                          rows="3"><?php echo e($task['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <?php foreach (['Low', 'Medium', 'High'] as $p): ?>
                        <option value="<?php echo e($p); ?>"
                            <?php echo ($task['priority'] ?? 'Medium') === $p
                                ? 'selected' : ''; ?>>
                            <?php echo e($p); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date"
                       value="<?php
                           echo !empty($task['due_date'])
                               && $task['due_date'] !== '0000-00-00'
                               ? e($task['due_date']) : '';
                       ?>">
            </div>

            <div class="form-actions full-width">
                <button type="submit" class="primary-button">
                    Save Changes
                </button>
                <a href="tasks.php" class="back-link">Cancel</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>

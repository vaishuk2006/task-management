
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<?php
require_once __DIR__ . "/config/db.php";

$result = $conn->query("SELECT * FROM tasks ORDER BY created_at DESC");
$tasks = [];

if ($result) {
    while ($task = $result->fetch_assoc()) {
        $tasks[] = $task;
    }
}

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

$message = $_GET["message"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tasks</title>
    <link rel="stylesheet" href="css/style.css?v=10">
</head>
<body>
<main class="container">
    <header class="page-header">
        <a href="index.php" class="back-link">← Back to Dashboard</a>

        <h1>Manage your <span>tasks.</span></h1>

        <p>Add your tasks, set priorities, and track your progress.</p>
    </header>

    <?php if ($message !== ""): ?>
        <div class="message"><?php echo e($message); ?></div>
    <?php endif; ?>

    <section class="panel" id="add-task">
        <div class="section-heading">
            <span class="section-kicker">PLAN YOUR WORK</span>
            <h2>Add a New Task</h2>
            <p>Enter your task details below.</p>
        </div>

        <form action="add_task.php" method="POST" class="task-form">
            <div class="form-group full-width">
                <label for="title">Task Title *</label>
                <input type="text" id="title" name="title"
                       maxlength="150" placeholder="Enter your task title" required>
            </div>

            <div class="form-group full-width">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                          rows="3" placeholder="Describe your task"></textarea>
            </div>

            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <option value="Low">Low</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="High">High</option>
                </select>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date">
            </div>

            <div class="form-actions full-width">
                <button type="submit" class="primary-button">+ Add Task</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="section-heading">
            <span class="section-kicker">YOUR WORKSPACE</span>
            <h2>My Tasks <span class="task-count">(<?php echo count($tasks); ?>)</span></h2>
            <p>Search, filter, complete, or delete your tasks.</p>
        </div>

        <div class="task-toolbar">
            <input type="search" id="taskSearch" placeholder="Search tasks..." aria-label="Search tasks">

            <select id="statusFilter" aria-label="Filter by status">
                <option value="all">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Completed">Completed</option>
            </select>

            <select id="priorityFilter" aria-label="Filter by priority">
                <option value="all">All Priorities</option>
                <option value="High">High Priority</option>
                <option value="Medium">Medium Priority</option>
                <option value="Low">Low Priority</option>
            </select>
        </div>

        <?php if (count($tasks) === 0): ?>
            <div class="empty-state">
                <h3>No tasks yet</h3>
                <p>Add your first task using the form above.</p>
            </div>
        <?php else: ?>
            <div class="task-list">
                <?php foreach ($tasks as $task): ?>
                    <?php
                    $status = $task["status"] ?? "Pending";
                    $priority = $task["priority"] ?? "Medium";
                    $searchText = strtolower(
                        ($task["title"] ?? "") . " " .
                        ($task["description"] ?? "")
                    );
                    ?>

                    <article class="task-card"
                        data-title="<?php echo e($searchText); ?>"
                        data-status="<?php echo e($status); ?>"
                        data-priority="<?php echo e($priority); ?>">

                        <div class="task-details">
                            <h3 class="task-title"><?php echo e($task["title"]); ?></h3>

                            <?php if (!empty($task["description"])): ?>
                                <p class="task-description"><?php echo nl2br(e($task["description"])); ?></p>
                            <?php endif; ?>

                            <div class="task-meta">
                                <span class="priority-badge priority-<?php echo strtolower(e($priority)); ?>">
                                    <?php echo e($priority); ?> Priority
                                </span>

                                <span class="status-badge <?php echo $status === "Completed" ? "status-completed" : "status-pending"; ?>">
                                    <?php echo e($status); ?>
                                </span>

                                <?php if (!empty($task["due_date"]) && $task["due_date"] !== "0000-00-00"): ?>
                                    <span class="due-date">
                                        Due <?php echo e(date("d M Y", strtotime($task["due_date"]))); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="task-actions">
                            <form action="update_task.php" method="POST">
                                <input type="hidden" name="id" value="<?php echo (int)$task["id"]; ?>">
                                <input type="hidden" name="status"
                                    value="<?php echo $status === "Completed" ? "Pending" : "Completed"; ?>">

                                <button type="submit" class="action-button complete-button">
                                    <?php echo $status === "Completed" ? "↶ Mark Pending" : "✓ Complete"; ?>
                                </button>
                            </form>

                            <form action="delete_task.php" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this task?');">
                                <input type="hidden" name="id" value="<?php echo (int)$task["id"]; ?>">
                                <button type="submit" class="action-button delete-button">🗑 Delete</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="no-results" id="noResults" hidden>
                No tasks match your search or filters.
            </div>
        <?php endif; ?>
    </section>
</main>

<script>
const search = document.getElementById("taskSearch");
const statusFilter = document.getElementById("statusFilter");
const priorityFilter = document.getElementById("priorityFilter");
const cards = document.querySelectorAll(".task-card");
const noResults = document.getElementById("noResults");

function filterTasks() {
    let visible = 0;
    const term = search.value.trim().toLowerCase();

    cards.forEach(card => {
        const matchesText = (card.dataset.title || "").includes(term);
        const matchesStatus = statusFilter.value === "all"
            || card.dataset.status === statusFilter.value;
        const matchesPriority = priorityFilter.value === "all"
            || card.dataset.priority === priorityFilter.value;

        const show = matchesText && matchesStatus && matchesPriority;
        card.hidden = !show;

        if (show) visible++;
    });

    if (noResults) noResults.hidden = visible > 0;
}

search.addEventListener("input", filterTasks);
statusFilter.addEventListener("change", filterTasks);
priorityFilter.addEventListener("change", filterTasks);
</script>
</body>
</html>

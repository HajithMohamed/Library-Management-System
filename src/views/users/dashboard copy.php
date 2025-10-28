<?php
session_start();

// Get reading progress for students
if ($_SESSION['userType'] === 'Student') {
    require_once __DIR__ . '/../../src/Controllers/UserController.php';
    $userController = new UserController();
    $progress = $userController->getReadingProgress();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/path/to/your/styles.css">
</head>
<body>
    <!-- ...existing header... -->

    <div class="dashboard-container">
        <!-- Reading Goal Widget for Students -->
        <?php if ($_SESSION['userType'] === 'Student'): ?>
            <div class="widget reading-goal-widget">
                <h3>📖 Monthly Reading Goal</h3>
                
                <?php if ($progress['goal'] == 0): ?>
                    <form method="POST" action="/index.php?route=set-goal" class="goal-form">
                        <p>Set a monthly reading goal to track your progress!</p>
                        <div class="input-group">
                            <input type="number" name="monthly_goal" min="1" max="20" placeholder="e.g., 3 books" required>
                            <button type="submit" class="btn btn-primary">Set Goal</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="progress-container">
                        <div class="progress-stats">
                            <span class="current-count"><?php echo $progress['current']; ?></span>
                            <span class="separator">/</span>
                            <span class="goal-count"><?php echo $progress['goal']; ?></span>
                            <span class="label">books this month</span>
                        </div>
                        
                        <div class="progress-bar-wrapper">
                            <progress value="<?php echo $progress['current']; ?>" 
                                      max="<?php echo $progress['goal']; ?>" 
                                      class="reading-progress">
                                <?php echo $progress['percentage']; ?>%
                            </progress>
                            <span class="progress-percentage"><?php echo $progress['percentage']; ?>% Complete</span>
                        </div>
                        
                        <form method="POST" action="/index.php?route=set-goal" class="update-goal-form">
                            <input type="number" name="monthly_goal" min="1" max="20" 
                                   value="<?php echo $progress['goal']; ?>" required>
                            <button type="submit" class="btn-small btn-outline">Update Goal</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- ...existing dashboard widgets... -->
    </div>

    <!-- ...existing footer... -->
</body>
</html>
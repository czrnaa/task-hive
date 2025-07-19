<?php
include("includes/session.php");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Task Hive | Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Task Hive</h1>

        <?php if (isLoggedIn()): ?>
            <p>Hello, you are logged in!</p>
            <nav>
                <ul>
                    <li><a href="account.php">Manage Account</a></li>
                    <li><a href="post_job.php">Post a Job</a></li>
                    <li><a href="browse_job.php">Browse Jobs</a></li> <!-- changed from browse_jobs.php-->
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        <?php else: ?>
            <p>Welcome, guest! Please <a href="login.php">Login</a> or <a href="register.php">Register</a>.</p>
        <?php endif; ?>
    </div>
</body>
</html>

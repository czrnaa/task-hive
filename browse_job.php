<?php
include("includes/session.php");
include("database/config.php");

ensureLoggedIn();

// INITIALIZE FILTER AND SORT VARIABLES
// Get values from the URL (GET request), using null coalescing operator for defaults
$search_title = $_GET['search_title'] ?? '';
$search_work_setup = $_GET['work_setup'] ?? '';
$sort_order = $_GET['sort_order'] ?? 'newest';


// BUILD THE DYNAMIC SQL QUERY
// Base query: Select all job details and join with users table to get the poster's name
$sql = "SELECT jobs.*, users.name as poster_name 
        FROM jobs 
        JOIN users ON jobs.user_id = users.id";

// An array to hold our WHERE conditions
$where_clauses = [];
$params = [];
$types = '';

// Filter by Title/Keyword (Category proxy)
if (!empty($search_title)) {
    $where_clauses[] = "jobs.title LIKE ?";
    $params[] = "%" . $search_title . "%";
    $types .= 's';
}

// Filter by Work Setup (Location proxy)
if (!empty($search_work_setup)) {
    $where_clauses[] = "jobs.work_setup = ?";
    $params[] = $search_work_setup;
    $types .= 's';
}

// If there are any WHERE conditions, append them to the SQL query
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Add Sorting logic (Budget/Deadline)
switch ($sort_order) {
    case 'deadline_asc':
        $sql .= " ORDER BY jobs.deadline ASC, jobs.created_at DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY jobs.created_at DESC";
        break;
}


// PREPARE AND EXECUTE THE QUERY
$stmt = $conn->prepare($sql);

// Bind parameters if they exist
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs - Task Hive</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="account.php">Manage Account</a></li>
                <li><a href="post_job.php">Post a Job</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <hr style="margin: 20px 0;">

        <h1>Browse Available Jobs</h1>
        
        <!-- Filter and Sort Form -->
        <form action="browse_job.php" method="GET" class="filter-form">
            <div>
                <label for="search_title">Category / Keyword</label>
                <input type="text" name="search_title" id="search_title" placeholder="e.g., 'Web Developer'" value="<?php echo htmlspecialchars($search_title); ?>">
            </div>
            
            <div>
                <label for="work_setup">Work Setup (Location)</label>
                <select name="work_setup" id="work_setup">
                    <option value="">All</option>
                    <option value="Remote" <?php if ($search_work_setup == 'Remote') echo 'selected'; ?>>Remote</option>
                    <option value="On-site" <?php if ($search_work_setup == 'On-site') echo 'selected'; ?>>On-site</option>
                    <option value="Hybrid" <?php if ($search_work_setup == 'Hybrid') echo 'selected'; ?>>Hybrid</option>
                </select>
            </div>

            <div>
                <label for="sort_order">Sort By</label>
                <select name="sort_order" id="sort_order">
                    <option value="newest" <?php if ($sort_order == 'newest') echo 'selected'; ?>>Newest First</option>
                    <option value="deadline_asc" <?php if ($sort_order == 'deadline_asc') echo 'selected'; ?>>Deadline (Soonest First)</option>
                </select>
            </div>

            <button type="submit">Filter Jobs</button>
        </form>

        <!-- Job Listings -->
        <div class="job-listings-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while($job = $result->fetch_assoc()): ?>
                    <div class="job-listing">
                        <?php if (!empty($job['image_path'])): ?>
                            <img src="assets/uploads/<?php echo htmlspecialchars($job['image_path']); ?>" alt="<?php echo htmlspecialchars($job['title']); ?>">
                        <?php endif; ?>
                        
                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                        
                        <div class="job-details">
                            <div><strong>Posted by:</strong> <?php echo htmlspecialchars($job['poster_name']); ?></div>
                            <div><strong>Payment:</strong> <?php echo htmlspecialchars($job['payment_mode']); ?></div>
                            <div><strong>Setup:</strong> <?php echo htmlspecialchars($job['work_setup']); ?></div>
                            <?php if(!empty($job['deadline'])): ?>
                            <div><strong>Deadline:</strong> <?php echo date("F j, Y", strtotime($job['deadline'])); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No jobs found matching your criteria. Try adjusting your filters!</p>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>

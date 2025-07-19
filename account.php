<?php
// Include session and database files, and ensure the user is logged in.
require_once 'includes/session.php';
ensureLoggedIn(); // Redirects to login.php if not logged in
require_once 'database/config.php';


// Initialize variables for user feedback and data.
$emailMessage = '';
$passwordMessage = '';
$emailMessageType = ''; // Used for CSS styling ('success' or 'error')
$passwordMessageType = '';
$userId = $_SESSION['user_id']; // Get the logged-in user's ID


// Check if the form was submitted via POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- A. HANDLE EMAIL UPDATE ---
    if (isset($_POST['update_email'])) {
        $newEmail = trim($_POST['newEmail']);
        $currentPasswordForEmail = $_POST['currentPasswordForEmail'];

        // Validate inputs
        if (empty($newEmail) || empty($currentPasswordForEmail)) {
            $emailMessage = "All fields are required to update email.";
            $emailMessageType = 'error';
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $emailMessage = "Please enter a valid email format.";
            $emailMessageType = 'error';
        } else {
            // Fetch user's current hashed password for verification
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // Verify the provided password
            if ($user && password_verify($currentPasswordForEmail, $user['password'])) {
                // Password is correct, proceed with email update
                $updateStmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
                $updateStmt->bind_param("si", $newEmail, $userId);

                if ($updateStmt->execute()) {
                    $emailMessage = "Email updated successfully!";
                    $emailMessageType = 'success';
                } else {
                    // Check for a duplicate email error (MySQL error code 1062)
                    if ($conn->errno == 1062) {
                        $emailMessage = "This email address is already in use.";
                    } else {
                        $emailMessage = "An error occurred. Please try again.";
                    }
                    $emailMessageType = 'error';
                }
                $updateStmt->close();
            } else {
                $emailMessage = "Incorrect password. Email not updated.";
                $emailMessageType = 'error';
            }
            $stmt->close();
        }
    }

    // --- B. HANDLE PASSWORD UPDATE ---
    if (isset($_POST['update_password'])) {
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];

        // Validate inputs
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $passwordMessage = "All fields are required to update password.";
            $passwordMessageType = 'error';
        } elseif ($newPassword !== $confirmPassword) {
            $passwordMessage = "New passwords do not match.";
            $passwordMessageType = 'error';
        } else {
            // Fetch user's current hashed password for verification
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // Verify the provided password
            if ($user && password_verify($currentPassword, $user['password'])) {
                // Hash the new password before storing it
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update password in the database
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $hashedNewPassword, $userId);

                if ($updateStmt->execute()) {
                    $passwordMessage = "Password updated successfully!";
                    $passwordMessageType = 'success';
                } else {
                    $passwordMessage = "An error occurred. Please try again.";
                    $passwordMessageType = 'error';
                }
                $updateStmt->close();
            } else {
                $passwordMessage = "Incorrect current password.";
                $passwordMessageType = 'error';
            }
            $stmt->close();
        }
    }
}


// Fetch the user's current email to display on the page.
$currentUserEmail = '';
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($user = $result->fetch_assoc()) {
    $currentUserEmail = htmlspecialchars($user['email']); // Sanitize for display
}
$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management</title>
    <!-- Basic styling for clarity -->
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f9; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 30px auto; background-color: #fff; padding: 25px 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        h1, h2 { color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }
        h1 { text-align: center; }
        h2 { font-size: 1.4em; margin-top: 40px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #555; }
        input[type="email"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; transition: border-color 0.3s; }
        input[type="email"]:focus, input[type="password"]:focus { border-color: #3498db; outline: none; }
        button { background-color: #3498db; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; width: 100%; }
        button:hover { background-color: #2980b9; }
        .message { padding: 12px; margin-top: 15px; border-radius: 5px; font-weight: 500; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .current-info { background-color: #ecf0f1; padding: 12px; border-radius: 5px; margin-bottom: 15px; color: #555; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Account Settings</h1>

        <!-- Form for Changing Email -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
            <h2>Change Email</h2>
            <div class="form-group">
                <label>Current Email</label>
                <div class="current-info"><?php echo $currentUserEmail; ?></div>
            </div>
            <div class="form-group">
                <label for="newEmail">New Email Address</label>
                <input type="email" id="newEmail" name="newEmail" required>
            </div>
            <div class="form-group">
                <label for="currentPasswordForEmail">Your Current Password</label>
                <input type="password" id="currentPasswordForEmail" name="currentPasswordForEmail" required>
            </div>
            <button type="submit" name="update_email">Update Email</button>
            <?php if (!empty($emailMessage)): ?>
                <div class="message <?php echo $emailMessageType; ?>">
                    <?php echo $emailMessage; ?>
                </div>
            <?php endif; ?>
        </form>

        <!-- Form for Changing Password -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
            <h2>Change Password</h2>
            <div class="form-group">
                <label for="currentPassword">Current Password</label>
                <input type="password" id="currentPassword" name="currentPassword" required>
            </div>
            <div class="form-group">
                <label for="newPassword">New Password</label>
                <input type="password" id="newPassword" name="newPassword" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm New Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>
            <button type="submit" name="update_password">Update Password</button>
            <?php if (!empty($passwordMessage)): ?>
                <div class="message <?php echo $passwordMessageType; ?>">
                    <?php echo $passwordMessage; ?>
                </div>
            <?php endif; ?>
        </form>
    </div>

</body>
</html>

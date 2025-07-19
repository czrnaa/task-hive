<?php
require 'database/config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Search for user with matching token
    $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $is_verified);
        $stmt->fetch();

        if ($is_verified) {
            $message = "Your email is already verified.";
        } else {
            // Mark as verified and clear token
            $update = $conn->prepare("UPDATE users SET is_verified = 1, token = NULL WHERE id = ?");
            $update->bind_param("i", $user_id);
            if ($update->execute()) {
                $message = "Your email has been successfully verified!";
            } else {
                $message = "Something went wrong while verifying.";
            }
        }
    } else {
        $message = "Invalid or expired token.";
    }

    $stmt->close();
} else {
    $message = "No token provided.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h2>Email Verification</h2>
    <p><?php echo $message; ?></p>
    <a href="login.php">Go to Login</a>
</body>
</html>



<!-- NOTES
- handles user session
- this is a shared file -->

<?php
// if session not started, start new session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// if not logged in, redirect back to login
function ensureLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
?>
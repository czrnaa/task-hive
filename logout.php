<?php
session_start();
session_unset();
session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging Out...</title>
    <meta http-equiv="refresh" content="2;url=login.php">
    <style>
        body {
            background-color: #f7f5ec;
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 100px;
        }
        h1 {
            font-size: 2rem;
            color: #333;
        }
        .spinner {
            margin: 30px auto;
            border: 6px solid #f3f3f3;
            border-top: 6px solid #333;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <h1>Logging out, please wait...</h1>
    <div class="spinner"></div>
</body>
</html>

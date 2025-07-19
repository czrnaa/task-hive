<?php
session_start();
require 'database/config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows !== 1) {
        $_SESSION['error'] = "Invalid or expired token.";
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, token = NULL WHERE token = ?");
    $stmt->bind_param("ss", $newPassword, $token);
    $stmt->execute();

    $_SESSION['success'] = "Password updated. You can now log in.";
    header("Location: login.php");
    exit;
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Task Hive</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            background-color: #f7f5ec;
            font-family: Arial, sans-serif;
        }

        .container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-container {
            background: #f0f0f0;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #fff;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 10px;
        }

        button:hover {
            background-color: #555;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        .success {
            color: green;
            margin-bottom: 15px;
        }

        #musicToggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
            padding: 6px 16px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        #musicToggle:hover {
            background-color: #555;
        }
    </style>
</head>
<body>

    <audio id="bgMusic" autoplay loop>
        <source src="background.mp3" type="audio/mpeg">
    </audio>
    <button id="musicToggle">Mute Music</button>

    <div class="container">
        <div class="form-container">
            <h2>Reset Password</h2>

            <?php
            if (isset($_SESSION['error'])) {
                echo "<div class='error'>" . htmlspecialchars($_SESSION['error']) . "</div>";
                unset($_SESSION['error']);
            }
            ?>

            <form method="POST">
                <div class="form-group">
                    <label>New Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
        </div>
    </div>

    <script>
        const bgMusic = document.getElementById('bgMusic');
        const musicToggle = document.getElementById('musicToggle');
        let isPlaying = true;

        musicToggle.textContent = 'Mute Music';

        musicToggle.addEventListener('click', () => {
            if (isPlaying) {
                bgMusic.pause();
                musicToggle.textContent = 'Play Music';
                isPlaying = false;
            } else {
                bgMusic.play().then(() => {
                    musicToggle.textContent = 'Mute Music';
                    isPlaying = true;
                }).catch(err => console.log(err));
            }
        });
    </script>

</body>
</html>

<?php
session_start();
require 'database/config.php'; // DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Both fields are required.";
        header("Location: login.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $db_email, $db_password);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $db_email;

            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Incorrect password.";
        }
    } else {
        $_SESSION['error'] = "No account found with that email.";
    }

    header("Location: login.php");
    exit;
}
?>

<!-- HTML Design Part -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Task Hive</title>
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

        input[type="email"],
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

        p {
            margin-top: 20px;
        }

        a {
            color: #333;
            text-decoration: underline;
        }

        a:hover {
            color: #555;
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
            <h2>Login</h2>

            <?php
            if (isset($_SESSION['error'])) {
                echo "<div class='error'>" . htmlspecialchars($_SESSION['error']) . "</div>";
                unset($_SESSION['error']);
            }
            ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit">Login</button>
            </form>

            <p><a href="register.php">Don't have an account? Register here</a></p>
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

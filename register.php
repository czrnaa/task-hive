<?php
session_start();
require 'database/config.php';  // DB connection

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($email, $username, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'appdevdev6@gmail.com';
        $mail->Password   = 'ghibdevszucpvpop';  // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('appdevdev6@gmail.com', 'Task Hive Team');
        $mail->addAddress($email, $username);

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - Task Hive';
        $mail->Body    = "
            <h2>Hello, $username!</h2>
            <p>Thanks for registering at Task Hive.</p>
            <p>Click the link to verify your email:</p>
            <a href='http://localhost/task-hive-main/verify.php?token=$token'>Verify Email</a>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: register.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: register.php");
        exit;
    }

    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: register.php");
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $token = bin2hex(random_bytes(16));

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email already registered.";
        header("Location: register.php");
        exit;
    }

   $stmt = $conn->prepare("INSERT INTO users (name, email, password, token, is_verified) VALUES (?, ?, ?, ?, 0)");
   $stmt->bind_param("ssss", $name, $email, $hashedPassword, $token);


    if ($stmt->execute()) {
        sendVerificationEmail($email, $name, $token);
        $_SESSION['success'] = "Registration successful. Check your email.";
    } else {
        $_SESSION['error'] = "Registration failed.";
    }

    header("Location: register.php");
    exit;
}
?>

<!-- HTML START -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Task Hive</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            background-color: #f7f5ec;
            font-family: Arial, sans-serif;
        }

        .container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-container {
            background: #f0f0f0;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 30px;
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

        input[type="text"],
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

        .success {
            color: green;
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
        <h2>Register</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit">Register</button>
        </form>

        <p><a href="login.php">Already have an account? Login here</a></p>
    </div>
</div>

<script>
    const bgMusic = document.getElementById('bgMusic');
    const musicToggle = document.getElementById('musicToggle');
    let isPlaying = true;

    musicToggle.addEventListener('click', () => {
        if (isPlaying) {
            bgMusic.pause();
            musicToggle.textContent = 'Play Music';
        } else {
            bgMusic.play();
            musicToggle.textContent = 'Mute Music';
        }
        isPlaying = !isPlaying;
    });
</script>

</body>
</html>

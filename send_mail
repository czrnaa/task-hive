<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adjust paths if needed depending on your directory
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

function sendVerificationEmail($email, $username, $token) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'appdevdev6@gmail.com';        // Your Gmail
        $mail->Password   = 'ghibdevszucpvpop';            // App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email sender and recipient
        $mail->setFrom('appdevdev6@gmail.com', 'Task Hive Team');
        $mail->addAddress($email, $username);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - Task Hive';
        $mail->Body    = "
            <h2>Hello, $username!</h2>
            <p>Thank you for registering at Task Hive.</p>
            <p>Please verify your email by clicking the link below:</p>
            <a href='http://localhost/task-hive/verify.php?token=$token'>Verify Email</a>
            <p>If you did not sign up, you can ignore this email.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

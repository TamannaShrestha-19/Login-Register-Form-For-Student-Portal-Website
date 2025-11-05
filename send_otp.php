<?php
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function send_mfa_otp($user_id, $email) {
    global $pdo;

    // Get role
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $role = $stmt->fetchColumn();

    $code = random_int(100000, 999999);

    // Mark old unused codes as used
    $stmt = $pdo->prepare("UPDATE mfa_otp SET is_used = 1 WHERE user_id = ? AND is_used = 0");
    $stmt->execute([$user_id]);

    // Insert new OTP using MySQL's NOW() for expiry time
    $stmt = $pdo->prepare("INSERT INTO mfa_otp (user_id, code, expires_at) VALUES (?, ?, NOW() + INTERVAL 10 MINUTE)");
    $stmt->execute([$user_id, $code]);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        if ($role === 'admin') {
            $mail->Username = 'admin@gmail.com';
            $mail->Password = 'pw';
            $mail->setFrom('arpanaxtha2020@gmail.com', 'Admin Portal');
        } else {
            $mail->Username = 'user@gmail.com';
            $mail->Password = 'pw';
            $mail->setFrom('tmnnshrsth@gmail.com', 'User Portal');
        }

        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->addAddress($email);
        $mail->isHTML(false);
        $mail->Subject = 'MFA Login Code';
        $mail->Body = "Your MFA login code is: $code\nIt will expire in 10 minutes.";

        $mail->send();
    } catch (Exception $e) {
        die("MFA email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}


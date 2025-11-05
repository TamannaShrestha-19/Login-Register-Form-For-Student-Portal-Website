<?php
session_start();
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$recaptchaToken = $_POST['recaptcha_token'] ?? '';
$secretKey = '6LfYwXQrAAAAALugHV-04GXm_Yf6U_3Pmffparxk';

$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaToken");
$responseData = json_decode($response);

if (!$responseData->success || $responseData->score < 0.5) {
    die("reCAPTCHA verification failed. Please try again.");
}

$username = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['repassword'] ?? '';

if (!$username || !$email || !$password || !$confirmPassword) {
    die('Please fill all fields.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email format.');
}
if ($password !== $confirmPassword) {
    die('Passwords do not match.');
}
if (
    strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/\d/', $password) ||
    !preg_match('/[\W_]/', $password)
) {
    die('Password must be 8+ chars, uppercase, lowercase, number, symbol.');
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->rowCount() > 0) {
    die('Email already registered.');
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert user
$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, last_password_change, is_locked, failed_attempts, is_verified) VALUES (?, ?, ?, NOW(), 0, 0, 0)");
$stmt->execute([$username, $email, $hashedPassword]);
$userId = $pdo->lastInsertId();

// Save password history
$stmt = $pdo->prepare("INSERT INTO password_history (user_id, old_password_hash) VALUES (?, ?)");
$stmt->execute([$userId, $hashedPassword]);

// Generate and store OTP using MySQL's NOW() + INTERVAL
$verification_code = random_int(100000, 999999);
$stmt = $pdo->prepare("UPDATE users SET verification_code = ?, verification_expires = NOW() + INTERVAL 15 MINUTE WHERE id = ?");
$stmt->execute([$verification_code, $userId]);

// Send verification email
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'tmnnshrsth@gmail.com';
    $mail->Password   = 'xcyi locr bnnq codl';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('tmnnshrsth@gmail.com', 'Student Portal');
    $mail->addAddress($email, $username);

    $mail->isHTML(false);
    $mail->Subject = 'Verify your email';
    $mail->Body    = "Hello $username,\n\nYour verification code is: $verification_code\nIt expires in 15 minutes.\n\nThank you!";

    $mail->send();
} catch (Exception $e) {
    die("Verification email could not be sent. Mailer Error: {$mail->ErrorInfo}");
}

header("Location: verify_email.php?email=" . urlencode($email));
exit;
?>

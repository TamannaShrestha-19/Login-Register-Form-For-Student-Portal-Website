<?php
session_start();
require 'db.php';

$recaptchaToken = $_POST['recaptcha_token'] ?? '';
$secretKey = '6LfYwXQrAAAAALugHV-04GXm_Yf6U_3Pmffparxk';

$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaToken");
$responseData = json_decode($response);

if (!$responseData || !isset($responseData->success) || !$responseData->success || ($responseData->score ?? 0) < 0.5) {
    // Log failed reCAPTCHA
    file_put_contents("logins.log", date('Y-m-d H:i:s') . " reCAPTCHA failed for IP: " . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND);

    echo "reCAPTCHA verification failed. Please try again.";
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    // Log incomplete form submission
    file_put_contents("logins.log", date('Y-m-d H:i:s') . " Login attempt with empty fields: $email\n", FILE_APPEND);

    die('Please fill all fields.');
}

// Log login attempt
file_put_contents("logins.log", date('Y-m-d H:i:s') . " Login attempt by $email\n", FILE_APPEND);

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dbUser || !is_array($dbUser)) {
    // Log invalid email
    file_put_contents("logins.log", date('Y-m-d H:i:s') . " Invalid email login attempt: $email\n", FILE_APPEND);

    die('Invalid email or password.');
}

if (!empty($dbUser['is_locked']) && $dbUser['is_locked']) {
    // Log locked account login attempt
    file_put_contents("logins.log", date('Y-m-d H:i:s') . "Locked account login attempt: $email\n", FILE_APPEND);

    die('Account locked due to failed login attempts.');
}

if (!password_verify($password, $dbUser['password_hash'])) {
    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = ?");
    $stmt->execute([$dbUser['id']]);

    $stmt = $pdo->prepare("SELECT failed_attempts FROM users WHERE id = ?");
    $stmt->execute([$dbUser['id']]);
    $attempts = $stmt->fetchColumn();

    // Log failed password
    file_put_contents("logins.log", date('Y-m-d H:i:s') . " Failed password for $email (Attempt $attempts)\n", FILE_APPEND);

    if ($attempts >= 3) {
        $stmt = $pdo->prepare("UPDATE users SET is_locked = 1 WHERE id = ?");
        $stmt->execute([$dbUser['id']]);

        // Log account lockout
        file_put_contents("logins.log", date('Y-m-d H:i:s') . " Account locked due to multiple failed attempts: $email\n", FILE_APPEND);

        die('Account locked due to multiple failed login attempts.');
    }

    die('Invalid email or password.');
}

$stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0 WHERE id = ?");
$stmt->execute([$dbUser['id']]);

$lastChange = strtotime($dbUser['last_password_change'] ?? '');
if (!$lastChange) $lastChange = 0;

if ($lastChange < strtotime('-30 days')) {
    $_SESSION['user_id'] = $dbUser['id'];
    $_SESSION['force_password_change'] = true;

    // Log forced password change redirect
    file_put_contents("logins.log", date('Y-m-d H:i:s') . " 🔄 Password change forced for $email\n", FILE_APPEND);

    header('Location: change_password.php');
    exit;
}

// Send MFA code
require 'send_otp.php';
send_mfa_otp($dbUser['id'], $dbUser['email']);

// Store MFA session with role
$_SESSION['mfa_user_id'] = $dbUser['id'];
$_SESSION['mfa_user_role'] = $dbUser['role'];

// Log successful login before MFA
file_put_contents("logins.log", date('Y-m-d H:i:s') . " ✅ Login successful (MFA pending) for $email\n", FILE_APPEND);

header('Location: verify_mfa.php');
exit;
?>

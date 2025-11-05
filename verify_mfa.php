<?php
session_start();
require 'db.php';
require 'send_otp.php'; // Assuming this file has your PHPMailer code to send OTP email

if (!isset($_SESSION['mfa_user_id'])) {
  header('Location: login_register.php');
  exit;
}

$error = '';
$success = '';
$user_id = $_SESSION['mfa_user_id'];

// Handle Verify Code POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['resend'])) {
  $code = trim($_POST['code'] ?? '');

  if (!$code) {
    $error = "Please enter the code.";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM mfa_otp WHERE user_id = ? AND code = ? AND expires_at > NOW() AND is_used = 0");
    $stmt->execute([$user_id, $code]);
    $mfa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mfa) {
      // Mark OTP as used
      $stmt = $pdo->prepare("UPDATE mfa_otp SET is_used = 1 WHERE id = ?");
      $stmt->execute([$mfa['id']]);

      // Fetch user role
      $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
      $stmt->execute([$user_id]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      $_SESSION['user_id'] = $user_id;
      $_SESSION['role'] = $user['role'];
      unset($_SESSION['mfa_user_id']);

      // Redirect based on role
      if ($user['role'] === 'admin') {
        header('Location: admin_dashboard.php');
      } else {
        header('Location: user_dashboard.php');
      }
      exit;
    } else {
      $error = "Invalid or expired code.";
    }
  }
}

// Handle Resend Code POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
  // Check if last OTP was sent recently to prevent spamming (e.g., 60 seconds cooldown)
  $stmt = $pdo->prepare("SELECT created_at FROM mfa_otp WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
  $stmt->execute([$user_id]);
  $lastOtp = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($lastOtp && (time() - strtotime($lastOtp['created_at'])) < 60) {
    $error = "Please wait before requesting a new code.";
  } else {
    // Generate new OTP code (6-digit)
    $newCode = random_int(100000, 999999);
    $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 minutes expiry

    // Insert new OTP into database
    $stmt = $pdo->prepare("INSERT INTO mfa_otp (user_id, code, expires_at, is_used, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->execute([$user_id, $newCode, $expiresAt]);

    // Fetch user email to send OTP
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
      // Send OTP email using your existing send_otp function (adjust to your code)
      $emailSent = send_mfa_otp($user['email'], $newCode);

      if ($emailSent) {
        $success = "A new code has been sent to your email.";
      } else {
        $error = "Failed to send the new code. Please try again later.";
      }
    } else {
      $error = "User email not found.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>MFA Verification</title>
  <style>
    body {
      background: linear-gradient(to bottom right, #1a0033, #004080);
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .box {
      background-color: #fff;
      color: #333;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    h2 {
      margin-bottom: 20px;
      color: #2e005b;
    }

    .error {
      background: #ffe6e6;
      color: #cc0000;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 15px;
    }

    .success {
      background: #e6ffe6;
      color: #009900;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 15px;
    }

    input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #2e005b;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 10px;
    }

    button:hover {
      background-color: #1c003d;
    }

    form.resend-form button {
      background-color: transparent;
      color: #2e005b;
      border: none;
      text-decoration: underline;
      cursor: pointer;
      margin-top: 15px;
      font-size: 0.9rem;
      padding: 0;
    }

    form.resend-form button:hover {
      color: #1c003d;
    }
  </style>
</head>

<body>
  <div class="box">
    <h2>MFA Code Verification</h2>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="text" name="code" placeholder="Enter 6-digit OTP" maxlength="6" required>
      <button type="submit">Verify</button>
    </form>

    <form method="POST" class="resend-form">
      <input type="hidden" name="resend" value="1">
      <button type="submit">Resend Code</button>
    </form>
  </div>
</body>

</html>
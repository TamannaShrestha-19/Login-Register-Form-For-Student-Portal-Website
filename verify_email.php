<?php
session_start();
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$success = '';
$error = '';
$prefilledEmail = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
  $email = trim($_POST['email'] ?? '');
  $code = trim($_POST['code'] ?? '');

  if (!$email || !$code) {
    $error = "Please enter both email and code.";
  } else {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND verification_code = ? AND verification_expires > NOW() AND is_verified = 0");
    $stmt->execute([$email, $code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
      $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL, verification_expires = NULL WHERE id = ?");
      $stmt->execute([$user['id']]);
      $success = "Email verified successfully! Redirecting to login...";
      header("Location:login_register.php?show=login&msg=verified");
      exit;
    } else {
      $error = "Invalid or expired code.";
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
  $email = trim($_POST['resend_email'] ?? '');

  if (!$email) {
    $error = "Please enter your email to resend code.";
  } else {
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ? AND is_verified = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
      $newCode = random_int(100000, 999999);
      $stmt = $pdo->prepare("UPDATE users SET verification_code = ?, verification_expires = NOW() + INTERVAL 10 MINUTE WHERE id = ?");
      $stmt->execute([$newCode, $user['id']]);

      $mail = new PHPMailer(true);
      try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tmnnshrsth@gmail.com';
        $mail->Password   = 'pw';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('tmnnshrsth@gmail.com', 'Student Portal');
        $mail->addAddress($email, $username);

        $mail->isHTML(false);
        $mail->Subject = 'Your NEW Email Verification Code';
        $mail->Body    = "Hello {$user['username']},\n\nYour new verification code is: $newCode\nIt expires in 10 minutes.\n\nThank you!";

        $mail->send();

        $success = "✅ New verification code sent to $email.";
      } catch (Exception $e) {
        $error = "Could not send email. Mailer Error: {$mail->ErrorInfo}";
      }
    } else {
      $error = "Email not found or already verified.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Verify Email</title>
  <style>
    body {
      background: linear-gradient(to bottom right, #2e005b, #000080);
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .container {
      background-color: #fff;
      color: #333;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
      max-width: 420px;
      width: 100%;
      text-align: center;
    }

    h2 {
      color: #2e005b;
      margin-bottom: 20px;
    }

    input[type="email"],
    input[type="text"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    button {
      width: 100%;
      padding: 12px;
      margin-top: 10px;
      background-color: #2e005b;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }

    button:hover {
      background-color: #1c003d;
    }

    .message {
      padding: 10px;
      margin: 15px 0;
      border-radius: 6px;
      font-size: 14px;
    }

    .success {
      background-color: #e6ffea;
      color: #008000;
    }

    .error {
      background-color: #ffe6e6;
      color: #cc0000;
    }

    form {
      margin-bottom: 20px;
    }

    h4 {
      margin: 20px 0 10px;
    }
  </style>
</head>

<body>
  <div class="container">
    <h2>Verify Your Email</h2>

    <?php if ($error): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
      <form method="POST">
        <input type="hidden" name="verify" value="1">
        <input type="email" name="email" placeholder="Registered Email" value="<?= htmlspecialchars($prefilledEmail) ?>" required>
        <input type="text" name="code" placeholder="Verification Code" maxlength="6" required>
        <button type="submit">Verify</button>
      </form>

      <h4>Didn't receive the code?</h4>
      <form method="POST">
        <input type="hidden" name="resend" value="1">
        <input type="email" name="resend_email" placeholder="Enter Email Again" required>
        <button type="submit">Resend Code</button>
      </form>
    <?php endif; ?>
  </div>
</body>

</html>


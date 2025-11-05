<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login_register.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (
        strlen($new_password) < 8 ||
        !preg_match('/[A-Z]/', $new_password) ||
        !preg_match('/[a-z]/', $new_password) ||
        !preg_match('/\d/', $new_password) ||
        !preg_match('/[\W_]/', $new_password)
    ) {
        $error = "Password must be 8+ chars, uppercase, lowercase, number, and symbol.";
    } else {
        // Check last 3 passwords
        $stmt = $pdo->prepare("SELECT old_password_hash FROM password_history WHERE user_id = ? ORDER BY changed_at DESC LIMIT 3");
        $stmt->execute([$user_id]);
        $last_passwords = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($last_passwords as $oldHash) {
            if (password_verify($new_password, $oldHash)) {
                $error = "New password cannot be the same as any of your last 3 passwords.";
                break;
            }
        }

        if (!$error) {
            $newHash = password_hash($new_password, PASSWORD_BCRYPT);

            // Update users table
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, last_password_change = NOW() WHERE id = ?");
            $stmt->execute([$newHash, $user_id]);

            // Insert to password_history
            $stmt = $pdo->prepare("INSERT INTO password_history (user_id, old_password_hash) VALUES (?, ?)");
            $stmt->execute([$user_id, $newHash]);

            $success = "Password updated successfully.";
            // Unset force_password_change if set
            unset($_SESSION['force_password_change']);
        }
    }
}
function is_password_reused($pdo, $userId, $newPassword) {
    // Fetch last 3 password hashes from password_history
    $stmt = $pdo->prepare("SELECT old_password_hash FROM password_history WHERE user_id = ? ORDER BY changed_at DESC LIMIT 3");
    $stmt->execute([$userId]);
    $lastHashes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($lastHashes as $hash) {
        if (password_verify($newPassword, $hash)) {
            return true; // password reused
        }
    }
    return false; // new password is fresh
}

?>

<!DOCTYPE html>
<html lang="en">
<head><title>Change Password</title></head>
<body>
<h2>Change Password</h2>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>

<form method="POST">
  <input type="password" name="new_password" placeholder="New Password" required><br>
  <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
  <button type="submit">Change Password</button>
</form>
</body>
</html>

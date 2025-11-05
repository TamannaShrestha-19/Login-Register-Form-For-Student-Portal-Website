<?php
session_start();
require 'db.php';
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('125257450980-l5dr88lnrr5jbdt9jcrhbj6lbllrh9i8.apps.googleusercontent.com'); // Replace
$client->setClientSecret('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'); // Replace
$client->setRedirectUri('http://localhost/student_portal/google_callback.php');

if (isset($_GET['code'])) {
    $client->fetchAccessTokenWithAuthCode($_GET['code']);
   // $oauth2 = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    $email = $userInfo->email;
    $name = $userInfo->name;

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // New user → register without password
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, last_password_change) VALUES (?, ?, '', 'user', NOW())");
        $stmt->execute([$name, $email]);
        $userId = $pdo->lastInsertId();
    } else {
        $userId = $user['id'];
    }

    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $name;
    $_SESSION['role'] = $user['role'] ?? 'user';

    // Optional: redirect to dashboard
    echo "Logged in with Google as " . htmlspecialchars($name);
} else {
    echo "Google login failed.";
}

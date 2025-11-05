<?php
$host = 'localhost';
$db = 'student_portal_db';
$user = 'root';
$pass = 'yourdbpw';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set MySQL session timezone to Nepali Time on every connection
    $pdo->exec("SET time_zone = '+05:45'");

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>


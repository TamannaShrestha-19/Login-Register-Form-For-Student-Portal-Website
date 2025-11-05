<?php
session_start();
$timeout_duration = 1500; // 25 minutes for session timeout
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login_register.php');
    exit;
}

// Correctly check session timeout here (outside above if block)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login_register.php?message=Session+expired,+please+login+again");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Fetch user details
$stmt = $pdo->prepare("SELECT username, email, role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Student Dashboard</title>
    <style>
        /* Reset & base */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(120deg, #2e005b, #000080);
            color: #222;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #1c003d;
            color: #eee;
            display: flex;
            flex-direction: column;
            padding: 30px 20px;
        }

        .sidebar h2 {
            font-weight: 700;
            margin-bottom: 40px;
            font-size: 1.8rem;
            letter-spacing: 2px;
        }

        .sidebar nav a {
            display: block;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 12px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .sidebar nav a:hover {
            background-color: #3a1f86;
        }

        .sidebar .logout-btn {
            margin-top: auto;
            background-color: #cc0000;
            padding: 12px 20px;
            text-align: center;
            border-radius: 6px;
            font-weight: 700;
            transition: background-color 0.3s ease;
        }

        .sidebar .logout-btn:hover {
            background-color: #990000;
        }

        /* Main content */
        main {
            flex-grow: 1;
            padding: 30px 40px;
            overflow-y: auto;
            background: #f9f9f9;
            color: #333;
        }

        main h1 {
            font-weight: 700;
            color: #2e005b;
            margin-bottom: 20px;
        }

        /* User info card */
        .profile-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            padding: 25px 30px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .profile-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2rem;
            color: #888;
            user-select: none;
        }

        .profile-info h2 {
            margin: 0 0 5px;
            font-size: 1.5rem;
            color: #2e005b;
        }

        .profile-info p {
            margin: 0;
            font-weight: 600;
            color: #555;
        }

        /* Dashboard grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            padding: 20px 25px;
        }

        .card h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2e005b;
            font-weight: 700;
            border-bottom: 2px solid #2e005b;
            padding-bottom: 5px;
        }

        ul {
            padding-left: 18px;
            margin: 0;
            color: #555;
        }

        ul li {
            margin-bottom: 8px;
            line-height: 1.3;
        }

        /* Scrollbar for main */
        main::-webkit-scrollbar {
            width: 10px;
        }

        main::-webkit-scrollbar-thumb {
            background-color: #2e005b;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <h2>Student Portal</h2>
        <nav>
            <a href="#">Dashboard</a>
            <a href="#">Courses</a>
            <a href="#">Assignments</a>
            <a href="#">Grades</a>
            <a href="#">Profile</a>
        </nav>
        <a href="logout.php" class="logout-btn">Logout</a>
    </aside>

    <main>
        <h1>Welcome, <?= htmlspecialchars($user['username']) ?> 👩‍🎓</h1>

        <div class="profile-card">
            <div class="profile-pic"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($user['username']) ?></h2>
                <p>Student</p>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <div class="dashboard-grid">
            <section class="card">
                <h3>Enrolled Courses</h3>
                <ul>
                    <li>Advanced Cyber Security - Progress: 50%</li>
                    <li>Advanced Data Technologies- Progress: 30%</li>
                    <li>Database Systems - Progress: 75%</li>
                    <li>Web Development - Progress: 40%</li>
                    <li>Artificial Intelligence - Progress: 30%</li>
                </ul>
            </section>

            <section class="card">
                <h3>Upcoming Deadlines</h3>
                <ul>
                    <li>Assignment 2 - Web Development - Due: July 10, 2025</li>
                    <li>Project Proposal - AI - Due: July 15, 2025</li>
                    <li>Quiz - Database Systems - Due: July 20, 2025</li>
                </ul>
            </section>

            <section class="card">
                <h3>Notifications</h3>
                <ul>
                    <li>No new notifications</li>
                </ul>
            </section>
        </div>
    </main>
</body>

</html>
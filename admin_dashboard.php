<?php
session_start();
$timeout_duration = 1500; // 25 minutes for session timeout

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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

require 'db.php';
$stmt = $pdo->query("SELECT id, username, email, is_locked FROM users WHERE role = 'user'");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(120deg, #1a0033, #003366);
            color: #eee;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .sidebar {
            width: 280px;
            background-color: #1a0033;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            color: #ddd;
        }

        .sidebar h2 {
            margin-bottom: 40px;
            font-weight: 700;
            font-size: 2rem;
            letter-spacing: 2px;
        }

        .sidebar nav a {
            display: block;
            padding: 14px 18px;
            margin-bottom: 15px;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .sidebar nav a:hover {
            background-color: #350068;
            color: #fff;
        }

        .sidebar .logout-btn {
            margin-top: auto;
            background-color: #cc0000;
            padding: 14px 20px;
            text-align: center;
            border-radius: 6px;
            font-weight: 700;
            color: #fff;
            transition: background-color 0.3s ease;
        }

        .sidebar .logout-btn:hover {
            background-color: #990000;
        }

        main {
            flex-grow: 1;
            padding: 30px 40px;
            overflow-y: auto;
            background: #f4f7fc;
            color: #222;
        }

        main h1 {
            font-weight: 700;
            margin-bottom: 30px;
            color: #2e005b;
        }

        /* Summary Cards */
        .summary-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .summary-cards .card {
            flex: 1;
            background: white;
            color: #333;
            font-weight: bold;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .total {
            border-left: 6px solid #2e005b;
        }

        .active {
            border-left: 6px solid #27ae60;
        }

        .disabled {
            border-left: 6px solid #c0392b;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 14px 18px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #2e005b;
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .btn {
            border: none;
            border-radius: 6px;
            padding: 8px 14px;
            font-weight: 700;
            cursor: pointer;
            color: white;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
            user-select: none;
        }

        .enable {
            background-color: #27ae60;
        }

        .enable:hover {
            background-color: #1e8449;
        }

        .disable {
            background-color: #c0392b;
        }

        .disable:hover {
            background-color: #922b21;
        }

        /* Recent Activity */
        .recent-activity {
            margin-top: 40px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .recent-activity h2 {
            color: #2e005b;
            margin-bottom: 15px;
        }

        .recent-activity ul {
            padding-left: 20px;
            margin: 0;
            color: #333;
        }

        .recent-activity ul li {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <nav>
            <a href="#">Dashboard</a>
            <a href="#">Manage Users</a>
            <a href="#">Manage Courses</a>
            <a href="#">Assignments</a>
            <a href="#">Reports</a>
        </nav>
        <a href="logout.php" class="logout-btn">Logout</a>
    </aside>

    <main>
        <h1>Admin Dashboard</h1>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="card total">Total Users: <?= count($users) ?></div>
            <div class="card active">
                Active: <?= count(array_filter($users, fn($u) => !$u['is_locked'])) ?>
            </div>
            <div class="card disabled">
                Disabled: <?= count(array_filter($users, fn($u) => $u['is_locked'])) ?>
            </div>
        </div>

        <!-- User Table -->
        <h2 style="margin-top: 30px;">User Management</h2>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= $user['is_locked'] ? 'Disabled' : 'Active' ?></td>
                        <td>
                            <?php if ($user['is_locked']): ?>
                                <a href="toggle_user.php?id=<?= $user['id'] ?>&action=enable" class="btn enable">Enable</a>
                            <?php else: ?>
                                <a href="toggle_user.php?id=<?= $user['id'] ?>&action=disable" class="btn disable">Disable</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Recent Activity (Static Example) -->
        <div class="recent-activity">
            <h2>Recent Activity</h2>
            <ul>
                <li>Arpana submitted assignment 2 (2 July 2025)</li>
                <li>👤 Tom registered for Web Development</li>
                <li>⚙️ Admin disabled user account for Laxmi</li>
            </ul>
        </div>
    </main>
</body>

</html>
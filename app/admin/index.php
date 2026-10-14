<?php

require '../../config/config.php';

requireRole('admin');

// Determine current section
$section = $_GET['section'] ?? 'activity-logs';

// Determine CRUD Operation
$action = $_GET['action'] ?? '';

// Fetch data based on current section
if ($section === 'activity-logs') {

    // Activity Logs Query
    $stmt = $pdo->query("
        SELECT *
        FROM activity_logs
        ORDER BY activity_log_created_at DESC
    ");

    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($section === 'user-sessions') {

    // User Sessions Query
    $stmt = $pdo->query("
        SELECT
            us.session_id,
            us.user_id,
            u.user_username,
            u.user_email,
            u.user_role,
            us.session_start,
            us.session_end,
            us.session_duration
        FROM user_sessions us
        INNER JOIN users u
            ON us.user_id = u.user_id
        ORDER BY us.session_start DESC
    ");

    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" 
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <style>

        body {
            font-family: Arial;
            background: #c4adad;
            margin: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        a {
            background: #f30101;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #2c2828;
            color: white;
        }

        tr:hover {
            background: #f1f1f1;
        }

        
    </style>
</head>

<body>

<div class="header">
    <h1>Welcome Mikko Admin!</h1>
    <a href="../../auth/signout.php">Sign Out</a>
</div>

<nav>
        <a href="index.php?section=activity-logs">Activity Logs</a>
        <a href="index.php?section=user-sessions">User Sessions</a>
        
    </nav>

    <?php if ($section === 'activity-logs'): ?>


<h1>Activity Logs</h1>

<table>
    <thead>
        <tr>

            <th>ID</th>
            <th>User</th>
            <th>Email</th>
            <th>Action</th>
            <th>Status</th>
            <th>IP</th>
            <th>User Agent</th>
            <th>Date</th>
            
        </tr>
    </thead>

    <tbody>
        <?php foreach ($activities as $activity): ?>
            <tr>
                <td><?= htmlspecialchars($activity['activity_log_id']) ?></td>
                <td><?= htmlspecialchars($activity['user_id']) ?></td>
                <td><?= htmlspecialchars($activity['user_email']) ?></td>
                <td><?= htmlspecialchars($activity['activity_log_action']) ?></td>
                <td><?= htmlspecialchars($activity['activity_log_status']) ?></td>
                <td><?= htmlspecialchars($activity['activity_log_ip_address']) ?></td>
                <td><?= htmlspecialchars($activity['activity_log_user_agent']) ?></td>
                <td><?= htmlspecialchars($activity['activity_log_created_at']) ?></td>
            </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

        <?php if ($section === 'user-sessions'): ?>

        <h1>User Sessions</h1>

        <table id="sessionTable" border="1" cellpadding="5" cellspacing="0">

            <thead>
                <tr>
                    <th>Session ID</th>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Session Start</th>
                    <th>Session End</th>
                    <th>Duration</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($sessions as $session): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($session['session_id']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($session['user_id']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($session['user_username']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($session['user_email']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($session['user_role']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($session['session_start']) ?>
                        </td>

                        <td>
                            <?= $session['session_end']
                                ? htmlspecialchars($session['session_end'])
                                : 'Active'
                            ?>
                        </td>

                        <td>

                            <?php

                            if ($session['session_duration'] !== null) {

                                $seconds = (int) $session['session_duration'];

                                $hours = floor($seconds / 3600);
                                $minutes = floor(($seconds % 3600) / 60);
                                $seconds = $seconds % 60;

                                echo sprintf(
                                    '%02d hr %02d min %02d sec',
                                    $hours,
                                    $minutes,
                                    $seconds
                                );

                            } else {

                                echo 'Active';

                            }

                            ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>
</body>
</html>

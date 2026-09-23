<?php
require '../../config/config.php';


requireRole('admin');

logActivity(
    $pdo,
    $_SESSION['user_id'],
    $_SESSION['user_email'],
    'view_activity_logs',
    'success'
);

$stmt = $pdo->query("
    SELECT * FROM activity_logs
    ORDER BY activity_log_created_at DESC
");

$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<h2>Activity Logs</h2>

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

</body>
</html>

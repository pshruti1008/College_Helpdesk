<?php
session_start();
require_once "config.php";
require_once "auth.php";

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"] ?? null;
$role = $_SESSION["role"] ?? null;
$tickets = [];

// Retrieve tickets directly in this file (only tickets assigned to the logged-in user)
if ($user_id) {
    $sql = "
        SELECT 
            t.id, t.title, t.priority, t.status, t.deadline, 
            u.username AS created_by_name 
        FROM tickets t
        JOIN users u ON t.created_by = u.id
        WHERE t.assigned_to = ?
    ";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tickets = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assigned Tickets</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap");

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-color: #f4f4f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .wrapper {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 1000px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2rem;
        }

        h2 {
            margin-top: 20px;
            margin-bottom: 10px;
            color: #555;
            font-size: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #428bca;
            color: white;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #428bca;
            color: white;
        }

        .btn-primary:hover {
            background-color: #357ebd;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        p {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"] ?? "Guest"); ?></h1>
        <p>Your role: <?php echo htmlspecialchars($role ?? "Unknown"); ?></p>

        <h2>Your Assigned Tickets</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Deadline</th>
            </tr>
            <?php if (!empty($tickets)): ?>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['priority']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['created_by_name']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['deadline'] ?? 'Not set'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No assigned tickets found.</td>
                </tr>
            <?php endif; ?>
        </table>
        <p>
            <a href="create_ticket.php" class="btn btn-primary">Create New Ticket</a>
            <a href="home.php" class="btn btn-primary">Home</a>
            <a href="logout.php" class="btn btn-danger">Sign Out</a>
        </p>
    </div>
</body>
</html>

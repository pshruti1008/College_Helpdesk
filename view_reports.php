<?php
session_start(); // Start the session
require_once "config.php"; // Ensure you have your database connection
require_once "functions.php"; // Include your functions if needed

// Check if HOD is logged in
if (!isset($_SESSION['hod_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Fetch reports from the database
$sql = "SELECT id, title, description, status, created_at, priority, assigned_to,escalated_by FROM reports ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tickets</title>
    <style>
        /* Add your styles here */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
        }
        table, th, td {
            border: 1px solid #dddddd;
        }
        th, td {
            text-align: left;
            padding: 12px;
        }
        th {
            background-color: #333;
            color: white;
        }
        td {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        a {
            text-decoration: none;
            color: #007BFF;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>View Tickets</h1>
        <?php
        if ($result->num_rows > 0) {
            echo '<table>';
            echo '<tr><th>Title</th><th>Description</th><th>Status</th><th>Created At</th><th>Priority</th><th>Assigned To</th><th>Action</th></tr>';
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                echo '<td>' . htmlspecialchars($row['description']) . '</td>';
                echo '<td>' . ucfirst(htmlspecialchars($row['status'])) . '</td>';
                echo '<td>' . date('F j, Y', strtotime($row['created_at'])) . '</td>';
                echo '<td>' . htmlspecialchars($row['priority']) . '</td>';
                echo '<td>' . htmlspecialchars($row['assigned_to']) . '</td>';
                echo '<td>' . htmlspecialchars($row['escalated_by']) . '</td>';
                echo '<td><a href="view_ticket_detail.php?id=' . $row['id'] . '">View Details</a></td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo "<p>No tickets found.</p>";
        }
        ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>

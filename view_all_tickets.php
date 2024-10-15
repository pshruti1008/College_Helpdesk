<?php
session_start();
require_once "config.php";
require_once "auth.php";
require_once "functions.php";

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"] ?? null;
$role = $_SESSION["role"] ?? null;
$tickets = [];

// Retrieve tickets for the logged-in user
if ($user_id && $role) {
    $tickets = get_tickets_by_user($conn, $user_id, $role);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <style>
        body, html {
    height: 100%;
    font-family: 'Arial', sans-serif;
    background-color: #f4f7f9;
    color: #333;
    line-height: 1.6;
}

.wrapper {
    max-width: 1200px;
    margin: 50px auto;
    padding: 30px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

h1, h2 {
    color: #2c3e50;
    margin-bottom: 20px;
}

h1 {
    font-size: 2.2rem;
    text-align: center;
}

h2 {
    font-size: 1.8rem;
    margin-top: 30px;
}

p {
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

th {
    background-color: #3498db;
    color: black;
    font-weight: bold;
}

tr:nth-child(even) {
    background-color: #f8f9fa;
}

tr:hover {
    background-color: #e8f4f8;
}

a {
    color: #3498db;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    margin-right: 10px;
    font-size: 16px;
    text-align: center;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.3s, color 0.3s;
    cursor: pointer;
}

.btn-primary {
    background-color: #3498db;
    color: #fff;
}

.btn-primary:hover {
    background-color: #2980b9;
}

.btn-secondary {
    background-color: #2ecc71;
    color: #fff;
}

.btn-secondary:hover {
    background-color: #27ae60;
}

.btn-danger {
    background-color: #e74c3c;
    color: #fff;
}

.btn-danger:hover {
    background-color: #c0392b;
}

@media (max-width: 768px) {
    .wrapper {
        margin: 20px;
        padding: 20px;
    }
    
    h1 {
        font-size: 1.8rem;
    }
    
    h2 {
        font-size: 1.5rem;
    }
    
    table {
        font-size: 14px;
    }
    
    .btn {
        display: block;
        margin-bottom: 10px;
    }
}
table {
  border-collapse: collapse;
  width: 100%;
  margin-bottom: 20px;
}

th, td {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: left;
}

th {
  background-color: #f2f2f2;
  font-weight: bold;
}

tr:nth-child(even) {
  background-color: #f9f9f9;
}

.btn-primary {
  background-color: #000;
  color: #fff;
  border: none;
  padding: 8px 12px;
  cursor: pointer;
}

.btn-primary:hover {
  background-color: #333;
}

.btn-secondary {
  background-color: #f2f2f2;
  color: #000;
  border: 1px solid #ccc;
  padding: 8px 12px;
  cursor: pointer;
}

.btn-secondary:hover {
  background-color: #ddd;
}

.btn-danger {
  background-color: #ff0000;
  color: #fff;
  border: none;
  padding: 8px 12px;
  cursor: pointer;
}

.btn-danger:hover {
  background-color: #cc0000;
}
        </style>
</head>
<body>
    <div class="wrapper">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"] ?? "Guest"); ?></h1>
        
        <h2>Your Tickets</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Assigned To</th>
                <th>Deadline</th>
                <th>Action</th>
                <th>View</th>
            </tr>
            <?php if (!empty($tickets)): ?>
                <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['priority']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['created_by_name']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['assigned_to_name'] ?? 'Unassigned'); ?></td>
                    <td><?php echo htmlspecialchars($ticket['deadline'] ?? 'Not set'); ?></td>
                    <td>
                    <?php if ($ticket['status'] === 'completed'): ?>
                    <!-- Link to the feedback page -->
                    <a href="feedback.php?ticket_id=<?= $ticket['id'] ?>">Give Feedback</a>
                <?php else: ?>
                    No feedback available
                <?php endif; ?>
                    </td>
                    <!-- <td><a href="view_ticket.php?id=<?php echo $ticket['id']; ?>">View</a></td> -->
                    <td><a href="view_ticket.php?id=<?php echo htmlspecialchars($ticket['id']); ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No tickets found.</td>
                </tr>
            <?php endif; ?>
        </table>
        <p>
            <a href="create_ticket.php" class="btn btn-primary">Create New Ticket</a>
            <a href="home.php" class="btn btn-primary">Home Page</a>
            <?php if ($role === 'department_head'): ?>
            <a href="assign_ticket.php" class="btn btn-secondary">Assign Tickets</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-danger">Sign Out</a>
        </p>
    </div>
</body>
</html>
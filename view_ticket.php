<?php
session_start(); 
require_once "config.php";
require_once "auth.php";
require_once "functions.php";

requireLogin();

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("location: home.php");
    exit();
}

$ticket_id = $_GET["id"];
$user_id = $_SESSION["id"];
$role = $_SESSION["role"];

$sql = "SELECT t.*, u1.username as created_by_name, u2.username as assigned_to_name 
        FROM tickets t 
        LEFT JOIN users u1 ON t.created_by = u1.id 
        LEFT JOIN users u2 ON t.assigned_to = u2.id 
        WHERE t.id = ? AND (t.created_by = ? OR t.assigned_to = ? OR ? = 'department_head')";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "iiis", $ticket_id, $user_id, $user_id, $role);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $ticket = mysqli_fetch_assoc($result);
        } else {
            header("location: home.php");
            exit();
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "Oops! Something went wrong. Please try again later.";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
.wrapper {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f5f5f5; /* Light gray background */
}

h1 {
  text-align: center;
  margin-bottom: 20px;
  color: #000; /* Black text */
}

p {
  margin-bottom: 15px;
  color: #333; /* Darker gray text */
}

strong {
  font-weight: bold;
}

.btn {
  background-color: #000;
  color: #fff;
  border: none;
  padding: 8px 12px;
  cursor: pointer;
  border-radius: 4px;
}

.btn:hover {
  background-color: #333;
}

.btn-primary {
  background-color: #000;
  color: #fff;
  border: none;
  padding: 8px 12px;
  cursor: pointer;
  border-radius: 4px;
}

.btn-primary:hover {
  background-color: #333;
}

.btn-default {
  background-color: #f2f2f2;
  color: #000;
  border: 1px solid #ccc;
  padding: 8px 12px;
  cursor: pointer;
}

.btn-default:hover {
  background-color: #ddd;
}
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>Ticket #<?php echo htmlspecialchars($ticket['id']); ?></h1>
        <p><strong>Title:</strong> <?php echo htmlspecialchars($ticket['title']); ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
        <p><strong>Priority:</strong> <?php echo htmlspecialchars($ticket['priority']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($ticket['status']); ?></p>
        <p><strong>Created By:</strong> <?php echo htmlspecialchars($ticket['created_by_name']); ?></p>
        <p><strong>Assigned To:</strong> <?php echo htmlspecialchars($ticket['assigned_to_name'] ?? 'Unassigned'); ?></p>
        <p><strong>Created At:</strong> <?php echo htmlspecialchars($ticket['created_at']); ?></p>
        <p><strong>Updated At:</strong> <?php echo htmlspecialchars($ticket['updated_at']); ?></p>
        <p><strong>Deadline:</strong> <?php echo htmlspecialchars($ticket['deadline'] ?? 'Not set'); ?></p>
        <a href="report.php?ticket_id=<?php echo htmlspecialchars($ticket['id']); ?>" class="btn">View Report</a>
        <?php if ($role == 'teacher' ||  $role == 'staff'): ?>
        <a href="escalate_ticket.php?ticket_id=<?php echo urlencode($ticket_id); ?>" class="button">Escalate Ticket</a>
        <?php endif; ?>
        <?php if ($role == 'department_head'): ?>
        <a href="update_ticket.php?id=<?php echo htmlspecialchars($ticket['id']); ?>" class="btn btn-primary">Update Ticket</a>
        <?php endif; ?>
        <a href="home.php" class="btn btn-default">Back to Home</a>
    </div>
</body>
</html>
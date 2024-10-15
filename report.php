<?php
session_start();

require_once "config.php"; // Database configuration file
require_once "auth.php"; // Authentication checks

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Get the ticket ID from the query parameter
$ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;

// Fetch ticket details from the database
function getTicketDetails($conn, $ticket_id) {
    $sql = "SELECT t.id, t.title, t.priority, t.status, t.created_at, t.deadline, 
                   u.username AS created_by, u2.username AS assigned_to, t.escalated_by, t.escalation_reason
            FROM tickets t
            LEFT JOIN users u ON t.created_by = u.id
            LEFT JOIN users u2 ON t.assigned_to = u2.id
            WHERE t.id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } else {
        die("Database query failed: " . mysqli_error($conn));
    }
}

$ticket = getTicketDetails($conn, $ticket_id);

// Check if the ticket exists
if (!$ticket) {
    echo "No ticket found.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket Report - ID: <?= htmlspecialchars($ticket['id']) ?></title>
    <style>
 .report {
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

table {
  border-collapse: collapse;
  width: 100%;
  margin-bottom: 20px;
}

th, td {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: left;
  color: #000; /* Black text */
}

th {
  background-color: #f2f2f2;
  font-weight: bold;
}

tr:nth-child(even) {
  background-color: #f9f9f9;
}

a {
  color: #000; /* Black text for links */
  text-decoration: underline;
}

a:hover {
  text-decoration: none;
}
    </style>
</head>
<body>
    <div class="report">
        <h1>Ticket Report</h1>
        <table>
            <tr>
                <th>Field</th>
                <th>Details</th>
            </tr>
            <tr>
                <td><strong>Ticket ID:</strong></td>
                <td><?= htmlspecialchars($ticket['id']) ?></td>
            </tr>
            <tr>
                <td><strong>Title:</strong></td>
                <td><?= htmlspecialchars($ticket['title']) ?></td>
            </tr>
            <tr>
                <td><strong>Priority:</strong></td>
                <td><?= htmlspecialchars($ticket['priority']) ?></td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td><?= htmlspecialchars($ticket['status']) ?></td>
            </tr>
            <tr>
                <td><strong>Created By:</strong></td>
                <td><?= htmlspecialchars($ticket['created_by']) ?></td>
            </tr>
            <tr>
                <td><strong>Assigned To:</strong></td>
                <td><?= htmlspecialchars($ticket['assigned_to'] ?? 'Unassigned') ?></td>
            </tr>
            <tr>
                <td><strong>Created At:</strong></td>
                <td><?= htmlspecialchars($ticket['created_at']) ?></td>
            </tr>
            <tr>
                <td><strong>Deadline:</strong></td>
                <td><?= htmlspecialchars($ticket['deadline'] ?? 'Not set') ?></td>
            </tr>
            <tr>
                <td><strong>Escalated By:</strong></td>
                <td><?= htmlspecialchars($ticket['escalated_by'] ?? 'Not escalated') ?></td>
            </tr>
            <tr>
                <td><strong>Escalation Reason:</strong></td>
                <td><?= htmlspecialchars($ticket['escalation_reason'] ?? 'No reason provided') ?></td>
            </tr>
        </table>

        <p><a href="home.php">Back to Home</a></p>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

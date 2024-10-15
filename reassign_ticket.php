<?php
session_start();

// Database connection
$servername = "localhost"; // Change this if your server is different
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "college_helpdesk"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize user input
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to get users (staff and teachers)
function getUsers($conn) {
    $sql = "SELECT id, username FROM users WHERE role IN ('staff', 'teacher')";
    $result = $conn->query($sql);
    $users = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

// Function to get tickets with status 'in_progress' and 'pending' (including escalation reason)
function getUnassignedTickets($conn) {
    $sql = "SELECT id, title, escalation_reason FROM tickets WHERE status ='escalated' AND (assigned_to IS NULL OR assigned_to = 'Unassigned')";
    $result = $conn->query($sql);
    $tickets = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tickets[] = $row;
        }
    }
    return $tickets;
}

// Function to reassign the ticket
function reassignTicket($ticket_id, $new_assignee) {
    global $conn;
    $sql = "UPDATE tickets SET assigned_to = ?, status = 'in_progress' WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $new_assignee, $ticket_id);
        if ($stmt->execute()) {
            return true;
        } else {
            die("Execute failed: " . $stmt->error);
        }
    } else {
        die("Prepare failed: " . $conn->error);
    }
    return false;
}

// Handle form submission for ticket reassignment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticket_id = sanitize_input($_POST['ticket_id']);
    $new_assignee = sanitize_input($_POST['new_assignee']);
    
    if (reassignTicket($ticket_id, $new_assignee)) {
        echo "Ticket reassigned successfully.";
    } else {
        echo "Failed to reassign ticket.";
    }
}

// Fetch unassigned tickets and users
$tickets = getUnassignedTickets($conn);
$users = getUsers($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reassign Ticket</title>
    <style>
        body, html {
            height: 100%;
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f9;
            color: black;
            line-height: 1.6;
        }
        .wrapper {
            max-width: 800px;
            margin: 50px auto;
            margin-top: 50px;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 2.2rem;
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: whitesmoke;
            color: black;
        }
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        input[type="submit"] {
            display: inline-block;
            padding: 12px 20px;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            background-color: #3498db;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #2980b9;
        }
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
}

tr:nth-child(even) {
  background-color: #f9f9f9;
}

select {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  color: #000; /* Black text */
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
    </style>
</head>
<body>
    <center>
    <div class="wrapper">
        <h1><b>Reassign Ticket</b></h1>
        <form method="post" action="">
            <table border="1">
                <thead>
                    <tr>
                        <th>Ticket Title</th>
                        <th>Escalation Reason</th>
                        <th>New Assignee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tickets)): ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><?= htmlspecialchars($ticket['title'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($ticket['escalation_reason'] ?? ''); ?></td>
                                <td>
                                    <select name="new_assignee" required>
                                        <option value="">Select User</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?= htmlspecialchars($user['id']); ?>">
                                                <?= htmlspecialchars($user['username']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket['id']); ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No Unassigned Tickets Found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <input type="submit" value="Reassign Ticket">
            <a href="home.php" class="btn btn-primary">Home Page</a>
        </form>
    </div>
    </center>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

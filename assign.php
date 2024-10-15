<?php
// Initialize the session
session_start();
require_once "config.php";  // Database configuration file
require_once "auth.php";    // Authentication file
require_once "functions.php";  // Utility functions like sanitize_input

// Load PHPMailer
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

requireLogin();  // Ensure user is logged in

// Check if the logged-in user is a department head
if ($_SESSION["role"] != "department_head") {
    header("location: home.php");
    exit();
}

$department = "";

// Fetch department of the logged-in user (department head)
$sql = "SELECT department FROM users WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $department = $row["department"];
        }
    } else {
        echo "Error fetching department: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Function to send email when ticket is assigned
function sendAssignmentEmail($assignedEmail, $assignedName, $ticketTitle, $ticketDescription, $dueDate) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'shrutipachpimple2003@gmail.com';
        $mail->Password = 'dena vbtf dmep uoyj';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('shrutipachpimple2003@gmail.com', 'College Helpdesk');
        $mail->addAddress($assignedEmail, $assignedName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Ticket Assigned: $ticketTitle";
        $mail->Body = "
        <html>
        <head>
            <title>New Ticket Assignment</title>
        </head>
        <body>
            <p>Dear $assignedName,</p>
            <p>You have been assigned a new ticket. Here are the details:</p>
            <p><strong>Ticket Title:</strong> $ticketTitle</p>
            <p><strong>Description:</strong> $ticketDescription</p>
            <p><strong>Due Date:</strong> $dueDate</p>
            <p>Please take the necessary actions to resolve the issue as soon as possible.</p>
            <p>Thank you,</p>
            <p>College Helpdesk Team</p>
        </body>
        </html>
        ";
        $mail->AltBody = "Dear $assignedName,\n\nYou have been assigned a new ticket.\n\nTicket Title: $ticketTitle\nDescription: $ticketDescription\nDue Date: $dueDate\n\nPlease take the necessary actions to resolve the issue as soon as possible.\n\nThank you,\nCollege Helpdesk Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Fetch unassigned tickets in the department
$unassigned_tickets = [];
$sql = "SELECT t.id, t.title, t.priority, u.username AS created_by_name 
        FROM tickets t 
        JOIN users u ON t.created_by = u.id 
        WHERE t.assigned_to IS NULL AND u.department = ? AND t.status != 'escalated'";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $department);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $unassigned_tickets = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        echo "Error fetching tickets: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Fetch staff members from the same department for assignment
$staff_members = [];
$sql = "SELECT id, username FROM users WHERE role IN ('teacher', 'staff') AND department = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $department);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $staff_members = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        echo "Error fetching staff members: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Handle form submission for ticket assignment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticket_id = sanitize_input($_POST["ticket_id"]);
    $assigned_to = sanitize_input($_POST["assigned_to"]);
    $deadline = sanitize_input($_POST["deadline"]);

    // Update the ticket to assign it to a staff member
    $sql = "UPDATE tickets SET assigned_to = ?, deadline = ?, status = 'in_progress' WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "isi", $assigned_to, $deadline, $ticket_id);
        if (mysqli_stmt_execute($stmt)) {
            // Fetch ticket and assigned user details
            $sql = "SELECT t.title, t.description, t.deadline, u.email, u.username
                    FROM tickets t
                    JOIN users u ON t.assigned_to = u.id
                    WHERE t.id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $ticket_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $ticketTitle = $row['title'];
                $ticketDescription = $row['description'];
                $dueDate = $row['deadline'];
                $assignedEmail = $row['email'];
                $assignedName = $row['username'];

                // Send email to the assigned user
                if (sendAssignmentEmail($assignedEmail, $assignedName, $ticketTitle, $ticketDescription, $dueDate)) {
                    $_SESSION['success_message'] = "Ticket assigned and notification email sent successfully.";
                } else {
                    $_SESSION['error_message'] = "Ticket assigned, but there was an error sending the email.";
                }
            } else {
                $_SESSION['error_message'] = "Ticket assigned, but could not fetch ticket details.";
            }
            
            // Redirect back to assign_ticket.php after assignment
            header("location: assign_ticket.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating ticket: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_message'] = "Error preparing SQL statement: " . mysqli_error($conn);
    }
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            font-family: 'Open Sans', sans-serif;
            background-color: #f4f7f9;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-align: center;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #3498db;
            color: black;
            font-weight: bold;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e8f4f8;
        }

        form {
            display: flex;
            gap: 10px;
        }

        select, input[type="date"], input[type="submit"] {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        select {
            flex-grow: 1;
        }

        input[type="submit"] {
            padding: 12px 20px;
            font-size: 16px;
            background-color: #2ecc71;
            color: black;
            cursor: pointer;
            transition: background-color 0.3s;
            border: none;
            border-radius: 5px;
        }

        input[type="submit"]:hover {
            background-color: #27ae60;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            text-decoration: none;
            color: #fff;
            background-color: #3498db;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .btn-default {
            background-color: #95a5a6;
        }

        .btn-default:hover {
            background-color: #7f8c8d;
        }

        p {
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
            color: #7f8c8d;
        }

        .success {
            color: #2ecc71;
            font-weight: bold;
        }

        .error {
            color: #e74c3c;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            table, form {
                font-size: 14px;
            }

            form {
                flex-direction: column;
            }

            select, input[type="date"], input[type="submit"] {
                width: 100%;
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

.btn-default {
  background-color: #fff;
  color: #000;
  border: 1px solid #ccc;
  padding: 8px 12px;
  cursor: pointer;
}

.btn-default:hover {
  background-color: #f2f2f2;
}
    </style>
</head>
<body>
    <div class="container">
        <center><h2>Assign Tickets</h2></center>
        <br><br>

        <?php
        // Display success or error messages
        if (isset($_SESSION['success_message'])) {
            echo "<p class='success'>" . $_SESSION['success_message'] . "</p>";
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo "<p class='error'>" . $_SESSION['error_message'] . "</p>";
            unset($_SESSION['error_message']);
        }
        ?>

        <!-- Display message if no unassigned tickets are found -->
        <?php if (empty($unassigned_tickets)): ?>
            <p>No unassigned tickets found.</p>
        <?php else: ?>
            <table border=1>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Priority</th>
                    <th>Created By</th>
                    <th>Assign to & Deadline</th>
                </tr>
                <?php foreach ($unassigned_tickets as $ticket): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['priority']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['created_by_name']); ?></td>
                    <td>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                            <select name="assigned_to" required>
                                <option value="">Select Staff Member</option>
                                <!-- Display staff members in the dropdown -->
                                <?php foreach ($staff_members as $staff): ?>
                                <option value="<?php echo $staff['id']; ?>"><?php echo htmlspecialchars($staff['username']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" name="deadline" required>
                            <input type="submit" value="Assign" class="btn btn-primary">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <a href="home.php" class="btn btn-default">Back to Home</a>
    </div>
</body>
</html>
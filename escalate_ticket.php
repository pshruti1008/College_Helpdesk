<?php
session_start();
require_once "config.php";
require_once "auth.php";
require_once "functions.php";

requireLogin();

// Ensure the user is a teacher or staff member
if ($_SESSION["role"] != "teacher" && $_SESSION["role"] != "staff") {
    header("location: home.php");
    exit();
}

$user_id = $_SESSION['id'];
$error = $success = "";
$assigned_tickets = [];

// Fetch tickets assigned to the current user
$sql = "SELECT id, title FROM tickets WHERE assigned_to = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $assigned_tickets = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $error = "Error fetching assigned tickets: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticket_id = intval($_POST['ticket_id']);
    $reason = sanitize_input($_POST['reason']);
    
    // Check if the ticket belongs to the current user
    $sql = "SELECT * FROM tickets WHERE id = ? AND assigned_to = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $ticket_id, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) == 1) {
                // Update the ticket status to 'escalated' and include the reason
                $update_sql = "UPDATE tickets SET status = 'escalated', escalation_reason = ?, assigned_to = NULL WHERE id = ?";
                if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
                    mysqli_stmt_bind_param($update_stmt, "si", $reason, $ticket_id);
                    if (mysqli_stmt_execute($update_stmt)) {
                        $success = "Ticket successfully escalated.";
                    } else {
                        $error = "Error updating ticket. Please try again.";
                    }
                    mysqli_stmt_close($update_stmt);
                }
            } else {
                $error = "You are not authorized to escalate this ticket.";
            }
        } else {
            $error = "Error retrieving ticket information. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Escalate Ticket</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Import Google Font - Poppins */
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap");

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

/* Body Styling */
body {
    background-color: #f4f4f9;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 20px;
}

/* Wrapper Styling */
.wrapper {
    background-color: #fff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    width: 100%;
}

/* Heading Style */
h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 1.8rem;
    color: #333;
}

/* Alert Styles */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    font-size: 0.9rem;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

/* Form Styles */
.form-group {
    margin-bottom: 20px;
    text-align: left;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

/* Input, Select, and Textarea Styles */
.form-control {
    width: 100%;
    padding: 12px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    resize: none; /* Prevent textarea resizing */
    background-color: #fff;
    transition: border-color 0.3s;
}

.form-control:focus {
    border-color: #428bca;
    outline: none;
}

/* Button Styles */
.btn {
    display: inline-block;
    padding: 12px 20px;
    font-size: 1rem;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
    text-decoration: none;
    text-align: center;
}

/* Primary Button */
.btn-primary {
    background-color: #428bca;
    color: #fff;
    border: none;
}

.btn-primary:hover {
    background-color: #357ebd;
}

/* Secondary Button */
.btn-secondary {
    background-color: #6c757d;
    color: #fff;
    border: none;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

/* Align buttons horizontally */
.form-group a, .form-group input[type="submit"] {
    width: 48%;
    margin: 5px 1%;
    display: inline-block;
    text-align: center;
}


    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Escalate a Ticket</h2>
        <?php 
        if (!empty($error)) {
            echo '<div class="alert alert-danger">' . $error . '</div>';
        }
        if (!empty($success)) {
            echo '<div class="alert alert-success">' . $success . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="ticket_id">Select Ticket to Escalate:</label>
                <select name="ticket_id" class="form-control" required>
                    <option value="">Select Ticket</option>
                    <?php foreach ($assigned_tickets as $ticket): ?>
                        <option value="<?php echo htmlspecialchars($ticket['id']); ?>">
                            <?php echo htmlspecialchars($ticket['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="reason">Reason for Escalation:</label>
                <textarea name="reason" class="form-control"  rows="8" cols="70"  required></textarea>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Escalate Ticket">
                <a href="home.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>

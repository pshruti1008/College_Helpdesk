<?php
session_start();

require_once "config.php";  // Database connection
require_once "auth.php";    // Authentication functions
require_once "functions.php"; // Utility functions

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

// Handle ticket deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_ticket'])) {
    $ticket_id = $_POST['ticket_id'];
    $sql = "DELETE FROM tickets WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $ticket_id);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Ticket deleted successfully.');</script>";
        } else {
            echo "<script>alert('Error deleting ticket: " . mysqli_error($conn) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle user deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $sql = "DELETE FROM users WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('User deleted successfully.');</script>";
        } else {
            echo "<script>alert('Error deleting user: " . mysqli_error($conn) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch all users
$users = [];
$sql = "SELECT * FROM users";
if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Fetch all tickets
$tickets = [];
$sql = "SELECT t.*, u.username AS created_by FROM tickets t JOIN users u ON t.created_by = u.id";
if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tickets[] = $row;
    }
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - College Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
  table {
            width: 100%; /* Full width */
            border-collapse: collapse; /* Remove gaps between cells */
            margin-top: 20px;
            background-color: #1c1c1c; /* Black background */
            border: 3px solid;
            border-color: #1c1c1c;
        }

        thead th {
            background-color: Black; /* Dark gray header background */
            color: #ffffff; /* White text */
            padding: 15px; /* Padding for header cells */
            text-align: left;
            font-weight: bold;
            text-transform: uppercase; /* Make header text uppercase */
        }

        tbody td {
            padding: 12px; /* Padding for content cells */
            border-bottom: 1px solid #3c3c3c; /* Subtle border between rows */
            color: #f5f5f5; /* Light text for readability */
        }

        tbody tr:nth-child(even) {
            background-color: #2b2b2b; /* Slightly lighter row background */
        }

        tbody tr:hover {
            background-color: #444444; /* Highlight row on hover */
        }
    

        /* Delete Button Style */
        .btn-danger {
            background-color: #dc3545; /* Red color for delete button */
            border: none;
            color: #fff;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #c82333; /* Darker red on hover */
        }

        /* Responsive Table */
        @media (max-width: 768px) {
            table {
                font-size: 0.9rem; /* Adjust font size for smaller screens */
            }

            tbody td {
                word-break: break-word; /* Handle long content gracefully */
            }
        }
        .btn1 {
            display: inline-block;
            background-color: #333; /* Dark gray button background */
            color: #fff; /* White text */
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
          }
          
          .btn1:hover {
            background-color: #555; /* Slightly lighter on hover */
          }
    </style>
    </style>
</head>
<body>
    <div class="container">
        <center><h2>All Users</h2></center>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" name="delete_user" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <center><a href="home.php" class="btn1">Back to Home</a></center>
    </div>
</body>
</html>

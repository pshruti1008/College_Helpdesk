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

// Fetch ticket details
$sql = "SELECT * FROM tickets WHERE id = ? AND (assigned_to = ? OR ? = 'department_head')";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "iis", $ticket_id, $user_id, $role);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 1) {
            $ticket = mysqli_fetch_assoc($result);
        } else {
            header("location: home.php");
            exit();
        }
    }
    mysqli_stmt_close($stmt);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = sanitize_input($_POST["status"]);
    $priority = sanitize_input($_POST["priority"]);
    
    $sql = "UPDATE tickets SET status = ?, priority = ? WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $status, $priority, $ticket_id);
        if (mysqli_stmt_execute($stmt)) {
            header("location: view_ticket.php?id=" . $ticket_id);
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
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
    <title>Update Ticket</title>
    <style>
        body, html {
            height: 100%;
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f9;
            color: #333;
            line-height: 1.6;
        }

        .wrapper {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 2rem;
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        label {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
            color: #2c3e50;
        }

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }

        input[type="submit"], .btn-default {
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

        .btn-default {
            background-color: #ccc;
            color: #333;
            margin-left: 10px;
        }

        .btn-default:hover {
            background-color: #bbb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .wrapper {
                margin: 20px;
                padding: 20px;
            }

            h2 {
                font-size: 1.8rem;
            }
        }
        .wrapper {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f5f5f5; /* Light gray background */
}

h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #000; /* Black text */
}

.form-group {
  margin-bottom: 15px;
}

label {
  display: block;
  margin-bottom: 5px;
  color: #000; /* Black text */
}

select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  color: #000; /* Black text */
}

.btn {
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-primary {
  background-color: #000;
  color: #fff;
}

.btn-primary:hover {
  background-color: #333;
}

.btn-default {
  background-color: #f2f2f2;
  color: #000;
  border: 1px solid #ccc;
}

.btn-default:hover {
  background-color: #ddd;
}
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Update Ticket #<?php echo htmlspecialchars($ticket_id); ?></h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $ticket_id; ?>" method="post">
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="pending" <?php echo ($ticket['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo ($ticket['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo ($ticket['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <div class="form-group">
                <label>Priority</label>
                <select name="priority" class="form-control" required>
                    <option value="low" <?php echo ($ticket['priority'] == 'low') ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo ($ticket['priority'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo ($ticket['priority'] == 'high') ? 'selected' : ''; ?>>High</option>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Update">
                <a href="view_ticket.php?id=<?php echo $ticket_id; ?>" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
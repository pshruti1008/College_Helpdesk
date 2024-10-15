<?php
session_start();

require_once "config.php";
require_once "auth.php";
require_once "functions.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true ) {
    header("location: login.php");
    exit;
}

// Fetch all feedback
function get_all_feedback($conn) {
    $sql = "SELECT feedback.id, feedback.feedback_text, feedback.ticket_id, tickets.title, feedback.created_at, users.username 
            FROM feedback 
            JOIN tickets ON feedback.ticket_id = tickets.id 
            JOIN users ON feedback.user_id = users.id 
            ORDER BY feedback.created_at DESC";

    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get feedback data
$feedbacks = get_all_feedback($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback Overview</title>
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

body {
  background-color: #f4f4f9; /* Light gray background */
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 20px;
}

.wrapper {
  width: 80%;
  max-width: 900px;
  background-color: #fff; /* White background */
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); /* Subtle shadow */
}

h1 {
  text-align: center;
  margin-bottom: 20px;
  color: #333; /* Dark gray text */
  font-size: 2rem;
}

/* Table styling */
table {
  width: 100%;
  border-collapse: collapse; /* Remove space between table cells */
  margin-bottom: 20px;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd; /* Light gray border */
}

th {
  background-color: #333; /* Dark gray header background */
  color: #fff; /* White text */
  font-weight: 500;
}

td {
  color: #333; /* Dark gray text */
}

tr:hover {
  background-color: #f2f2f2; /* Light hover effect */
}

/* Handle empty table case */
td[colspan="5"] {
  text-align: center;
  color: #888;
  font-style: italic;
}

/* Button styling */
.btn {
  display: inline-block;
  background-color: #333; /* Dark gray button background */
  color: #fff; /* White text */
  padding: 10px 20px;
  text-decoration: none;
  border-radius: 5px;
  transition: background-color 0.3s ease;
}

.btn:hover {
  background-color: #555; /* Slightly lighter on hover */
}

p {
  text-align: center;
  margin-top: 10px;
}

    </style>
</head>
<body>
    <div class="wrapper">
        <h1>Feedback Overview</h1>
        <table>
            <tr>
                <th>Feedback ID</th>
                <th>Ticket Title</th>
                <th>Comment</th>
                <th>Submitted By</th>
                <th>Submitted At</th>
            </tr>
            <?php if (!empty($feedbacks)): ?>
                <?php foreach ($feedbacks as $feedback): ?>
                <tr>
                    <td><?php echo htmlspecialchars($feedback['id']); ?></td>
                    <td><?php echo htmlspecialchars($feedback['title']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($feedback['feedback_text'])); ?></td>
                    <td><?php echo htmlspecialchars($feedback['username']); ?></td>
                    <td><?php echo htmlspecialchars($feedback['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No feedback available.</td>
                </tr>
            <?php endif; ?>
        </table>

        <p>
            <a href="home.php" class="btn">Back to Home</a>
        </p>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

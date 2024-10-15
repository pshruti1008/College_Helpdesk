<?php
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

// Start session
session_start();

// Function to sanitize user input
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to check if the user is allowed to provide feedback
function userCanProvideFeedback($ticket_id, $user_id, $conn) {
    $sql = "SELECT * FROM tickets WHERE id = ? AND created_by = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $ticket_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0; // User can provide feedback if the ticket exists and was created by them
    }
    return false;
}

// Handle feedback submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticket_id = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['id']; // Assuming the user ID is stored in session
    $feedback_text = sanitize_input($_POST['feedback_text']);

    // Check if the user can provide feedback for the ticket
    if (userCanProvideFeedback($ticket_id, $user_id, $conn)) {
        // Insert feedback into the database
        $sql = "INSERT INTO feedback (ticket_id, user_id, feedback_text) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iis", $ticket_id, $user_id, $feedback_text);
            if ($stmt->execute()) {
                echo "Feedback submitted successfully.";
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "You are not allowed to provide feedback for this ticket.";
    }
}

// Fetch ticket ID from query parameters
$ticket_id = filter_input(INPUT_GET, 'ticket_id', FILTER_VALIDATE_INT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback</title>
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

        h1 {
            font-size: 2.2rem;
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

        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
            resize: vertical;
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

        a {
            color: #3498db;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        p {
            margin-top: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .wrapper {
                margin: 20px;
                padding: 20px;
            }

            h1 {
                font-size: 1.8rem;
            }

            textarea {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>Submit Feedback for Ticket ID: <?= htmlspecialchars($ticket_id) ?></h1>
        <form method="post" action="">
            <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket_id) ?>">
            <div class="form-group">
                <label for="feedback_text">Feedback:</label>
                <textarea name="feedback_text" required></textarea>
            </div>
            <center><input type="submit" value="Submit Feedback"></center>
        </form>
        <p><a href="home.php">Back to Dashboard</a></p>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

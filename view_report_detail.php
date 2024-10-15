<?php
// Start session
require_once "config.php";
require_once "functions.php";


// Check if HOD is logged in
if (!isset($_SESSION['hod_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
include('db_connection.php');

// Check if a report ID is passed through the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Invalid report ID.";
    exit();
}

// Get the report ID from the URL
$report_id = intval($_GET['id']);

// Fetch the report details from the database
$sql = "SELECT report_title, description, status, created_at FROM reports WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the report exists
if ($result->num_rows === 0) {
    echo "No report found with this ID.";
    exit();
}

// Fetch the report data
$report = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Details</title>
    <!-- Internal CSS for styling -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        p {
            font-size: 18px;
            line-height: 1.6;
            color: #666;
        }

        .report-detail {
            margin-top: 20px;
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .label {
            font-weight: bold;
            color: #333;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007BFF;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Report Details</h1>
        <div class="report-detail">
            <p><span class="label">Title:</span> <?php echo htmlspecialchars($report['report_title']); ?></p>
            <p><span class="label">Description:</span> <?php echo nl2br(htmlspecialchars($report['description'])); ?></p>
            <p><span class="label">Status:</span> <?php echo ucfirst(htmlspecialchars($report['status'])); ?></p>
            <p><span class="label">Created At:</span> <?php echo date('F j, Y, g:i a', strtotime($report['created_at'])); ?></p>
        </div>
        <a href="view_reports.php">Back to Reports</a>
    </div>
</body>
</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>

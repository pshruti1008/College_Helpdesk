<?php
session_start();
require_once "config.php";
require_once "auth.php";

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read More - College Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f4f4f4;
        }
        .content {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        p, ul {
            color: #555;
            line-height: 1.6;
        }
        .btn {
            background-color: #007bff;
            color: white;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="content">
        <h2>Read More About Our Services</h2>
        <p>At the College Helpdesk, we strive to provide comprehensive support to our students and faculty. Here are some of the key services we offer:</p>
        <ul>
            <li>Technical Support: Assistance with IT-related issues such as password resets and system access.</li>
            <li>Academic Resources: Guidance on course registration, tutoring services, and academic counseling.</li>
            <li>Library Services: Help with accessing library resources, including books and online databases.</li>
            <li>Event Support: Coordination for campus events and activities, including workshops and seminars.</li>
        </ul>
        <p>We are here to ensure your academic journey is smooth and successful. For more details, feel free to reach out!</p>
        <a href="home.php" class="btn">Back to Home</a>
    </div>
</body>
</html>

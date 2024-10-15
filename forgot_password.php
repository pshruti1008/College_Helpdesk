<?php
session_start();

// Include required files
require_once "config.php";  // Database configuration
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = "";
$success_message = "";

// Function to send OTP via email
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'shrutipachpimple2003@gmail.com';
        $mail->Password = 'dena vbtf dmep uoyj';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('shrutipachpimple2003@gmail.com', 'College Helpdesk');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body = "Your OTP for password reset is: <b>$otp</b><br>This OTP will expire in 15 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Handle OTP request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_otp"])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $otp = sprintf("%06d", mt_rand(0, 999999));  // Generate a 6-digit OTP
        $otp_expiration = date('Y-m-d H:i:s', strtotime('+15 minutes'));  // Set expiration time

        // Store OTP and expiration in the database
        $update_stmt = $conn->prepare("UPDATE users SET otp = ?, otp_expiration = ? WHERE email = ?");
        $update_stmt->bind_param("sss", $otp, $otp_expiration, $email);

        if ($update_stmt->execute() && sendOTP($email, $otp)) {
            $_SESSION['reset_email'] = $email;
            $success_message = "An OTP has been sent to your email. Please check your inbox.";
            header("Location: reset_password.php");
            exit();
        } else {
            $error_message = "Failed to send OTP. Please try again.";
        }
        $update_stmt->close();
    } else {
        $error_message = "Email not found.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .form-container {
            width: 400px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="text-center">Forgot Password</h2>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" name="send_otp" class="btn btn-primary w-100">Send OTP</button>
        </form>
    </div>
</body>
</html>

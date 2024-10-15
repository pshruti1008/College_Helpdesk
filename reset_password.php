<?php
session_start();
require_once "config.php"; // Database connection

$error_message = "";
$success_message = "";

// Check if the user has come from the forgot password page
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $otp = trim($_POST['otp'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validate inputs
    if (empty($otp) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } else {
        $stmt = $conn->prepare("SELECT id, otp, otp_expiration FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $current_time = new DateTime();
            $otp_expiration = new DateTime($user["otp_expiration"]);

            // Check OTP and expiration
            if (strval($user["otp"]) === $otp && $current_time <= $otp_expiration) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $update_stmt = $conn->prepare("UPDATE users SET password = ?, otp = NULL, otp_expiration = NULL WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user["id"]);

                if ($update_stmt->execute()) {
                    $success_message = "Password reset successful. You can now login with your new password.";
                    unset($_SESSION['reset_email']); // Clear session data
                } else {
                    $error_message = "Failed to reset password. Please try again.";
                }
                $update_stmt->close();
            } else {
                $error_message = "Invalid or expired OTP. Please request a new OTP.";
            }
        } else {
            $error_message = "Invalid email. Please start the password reset process again.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .form-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="text-center">Reset Password</h2>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php else: ?>
        <form method="post">
            <div class="mb-3">
                <label for="otp" class="form-label">OTP:</label>
                <input type="text" class="form-control" id="otp" name="otp" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password:</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
        <?php endif; ?>
        <div class="mt-3 text-center">
            <a href="login.php">Back to Login</a>
            <a href="forgot_password.php">Request new OTP</a>
        </div>
    </div>
</body>
</html>

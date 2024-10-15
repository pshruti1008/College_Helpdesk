<?php
session_start();  // Start session at the beginning

require_once "config.php";  // Database connection
require_once "functions.php";  // Sanitize input function

$username = $password = "";
$username_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST["username"]);
    $password = sanitize_input($_POST["password"]);

    if (empty($username)) {
        $username_err = "Please enter your username.";
    }
    if (empty($password)) {
        $password_err = "Please enter your password.";
    }

    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Set session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;

                            // Redirect to home.php
                            header("location: home.php");
                            exit();
                        } else {
                            $password_err = "Invalid password.";
                        }
                    }
                } else {
                    $username_err = "No account found with that username.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
 /* Importing Google Font - Open Sans */
@import url("https://fonts.googleapis.com/css2?family=Open+Sans:wght@200;300;400;500;600;700&display=swap");

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Open Sans", sans-serif;
}

body {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  width: 100%;
  background: linear-gradient(135deg, #f5f5f5, #ddd);
}

.container {
  max-width: 800px;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.form-container {
  display: flex;
  background-color: rgba(255, 255, 255, 0.9); /* Slightly transparent background */
  border-radius: 15px;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1); /* Soft shadow */
  overflow: hidden;
  width: 100%;
  max-width: 900px;
}

.image-container {
  flex: 1;
  background-color: #428bca; /* Blue background */
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-container img {
  max-width: 100%;
  height: auto;
  border-radius: 10px;
}

.form-content {
  flex: 1;
  padding: 40px;
}

h2 {
  text-align: center;
  font-size: 2rem;
  margin-bottom: 20px;
  color: #333;
}

p {
  text-align: center;
  color: #555;
  margin-bottom: 20px;
}

.input-group {
  margin-bottom: 20px;
  position: relative;
}

label {
  display: block;
  margin-bottom: 8px;
  font-size: 1.1rem;
  color: #555;
}

input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
  color: #333;
}

input:focus {
  border-color: #428bca;
  outline: none;
  box-shadow: 0 0 5px rgba(66, 139, 202, 0.5); /* Blue shadow on focus */
}

.btn {
  width: 100%;
  padding: 12px;
  background-color: #428bca;
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 1.2rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn:hover {
  background-color: #357ebd; /* Darker blue on hover */
}

.help-block {
  color: red;
  font-size: 0.9rem;
}

.forgot-password {
  text-align: center;
  margin-top: 10px;
}

.forgot-password a {
  color: #428bca;
  text-decoration: none;
}

.forgot-password a:hover {
  text-decoration: underline;
}

    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="image-container">
                <img src="img5.png" alt="Login Image">
            </div>
            <div class="form-content">
                <h2>Login</h2>
                <p>Please fill in your credentials to login.</p>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="input-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo $username; ?>">
                        <span class="help-block"><?php echo $username_err; ?></span>
                    </div>    
                    <div class="input-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                        <label>Password</label>
                        <input type="password" name="password">
                        <span class="help-block"><?php echo $password_err; ?></span>
                    </div>
                    <a href="forgot_password.php" class="forgot-pass-link">Forgot password?</a>
                    <div class="input-group">
                        <input type="submit" class="btn" value="Login">
                    </div>
                    <div class="forgot-password">
                        <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
                    </div>
                </form>
            </div>
        </div>    
    </div>
</body>
</html>

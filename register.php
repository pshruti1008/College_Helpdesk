<?php
if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
require_once "config.php"; // Database connection
require_once "functions.php"; // Function to sanitize input

// Initialize variables
$username = $password = $confirm_password = $role = $department = $email = "";
$username_err = $password_err = $confirm_password_err = $role_err = $email_err = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST["username"]);
    $password = sanitize_input($_POST["password"]);
    $confirm_password = sanitize_input($_POST["confirm_password"]);
    $role = sanitize_input($_POST["role"]);
    $department = sanitize_input($_POST["department"]);
    $email = sanitize_input($_POST["email"]);

    // Validate username
    if (empty($username)) {
        $username_err = "Please enter a username.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                $username_err = "This username is already taken.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validate email
    if (empty($email)) {
        $email_err = "Please enter your email.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                $email_err = "This email is already taken.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validate password
    if (empty($password)) {
        $password_err = "Please enter a password.";     
    } elseif (strlen($password) < 6) {
        $password_err = "Password must have at least 6 characters.";
    }

    // Validate confirm password
    if (empty($confirm_password)) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        if ($password !== $confirm_password) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Validate role
    if (empty($role)) {
        $role_err = "Please select a role.";
    }

    // Check input errors before inserting in database
    if (empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($role_err)) {
        $sql = "INSERT INTO users (username, email, password, role, department) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $param_password, $role, $department);
            if (mysqli_stmt_execute($stmt)) {
                header("location: login.php");
                exit; // Prevent further execution
            } else {
                echo "Something went wrong. Please try again later.";
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Authentication System | Login & Sign-Up</title>
  <style>
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
      min-height: 100vh;
      width: 100%;
      padding: 0 10px;
      background: white;
    }

    .form-container {
      display: flex;
  align-items: center;
  justify-content: space-evenly; /* Center contents horizontally */
  gap: 30px; /* Space between image and form */
  max-width: 900px; /* Control overall width */
  width: 90%; /* Take 90% of the screen width */
  padding: 40px;
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.5);
 
  
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .image-container img {
      width: 300px; /* Adjust the image size */
      height: auto;
      border-radius: 8px;
    }

    .wrapper {
      width: 400px;
    }

    h2 {
      font-size: 2rem;
      margin-bottom: 20px;
      color: black;
    }
    p{
      color: black;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .input-field {
      position: relative;
      border-bottom: 2px solid #ccc;
    }

    .input-field input, 
    .select {
      width: 100%;
      height: 40px;
      background: transparent;
      border: none;
      outline: none;
      font-size: 16px;
      color: #fff;
    }

    .select option {
    background-color: white; /* Dropdown list background */
    color: black; /* Dropdown text color */
  }

    .input-field label {
      position: absolute;
      top: 50%;
      left: 0;
      transform: translateY(-50%);
      color: black;
      font-size: 16px;
      pointer-events: none;
      transition: 0.15s ease;
    }

    .input-field form groupp


    .input-field input:focus ~ label,
    .input-field input:valid ~ label {
      font-size: 0.8rem;
      top: 10px;
      transform: translateY(-120%);
    }

    button {
      background: #fff;
      color: #000;
      font-weight: 600;
      border: none;
      padding: 12px 20px;
      cursor: pointer;
      border-radius: 5px;
      font-size: 16px;
      transition: background-color 0.3s;
    }

    button:hover {
      background: #175d69;
      color: black;
    }

    .register {
      margin-top: 20px;
      color: #fff;
    }

    .register a {
      color: #007bff;
      text-decoration: none;
    }

    .register a:hover {
      text-decoration: underline;
    }

    .button-group {
      display: flex;
      justify-content: space-between;
      gap: 10px;
    }
    .input-field {
    margin-bottom: 15px; /* Adds space below each input field */
}
.input-field {
  position: relative;
}

.input-field label {
  position: absolute;
  top: 0;
  left: 0;
  font-size: 16px;
  cursor: text;
  transition: 0.2s ease-out;
}

.input-field input[type="text"], 
.input-field input[type="email"], 
.input-field input[type="password"], 
.input-field select {
  padding-top: 20px;
}

.input-field input[type="text"]:focus, 
.input-field input[type="email"]:focus, 
.input-field input[type="password"]:focus, 
.input-field select:focus {
  padding-top: 10px;
}

.input-field input[type="text"]:focus ~ label, 
.input-field input[type="email"]:focus ~ label, 
.input-field input[type="password"]:focus ~ label, 
.input-field select:focus ~ label {
  top: -20px;
  font-size: 14px;
  color: black;
}

.input-field input[type="text"]:hover ~ label, 
.input-field input[type="email"]:hover ~ label, 
.input-field input[type="password"]:hover ~ label, 
.input-field select:hover ~ label {
  top: -20px;
  font-size: 14px;
  color: black;
}


  </style>
</head>
<body>
  <div class="form-container">
    <!-- Image section -->
    <div class="image-container">
      <img src="img5.png" alt="Login Image">
    </div>
    <div class="wrapper" id="signup-form" style="display: block;">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <h2>Sign Up</h2>

        <div class="input-field form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" value="<?php echo $username; ?>" required />
            <span class="help-block"><?php echo $username_err; ?></span>
        </div>

        <div class="input-field form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo $email; ?>" required />
            <span class="help-block"><?php echo $email_err; ?></span>
        </div>

        <div class="row">
            <div class="input-field form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" value="<?php echo $password; ?>" required />
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <br>

            <div class="input-field form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>" required />
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
            </div>
        </div>

        <div class="input-field form-group <?php echo (!empty($role_err)) ? 'has-error' : ''; ?>">
            <label for="role">Select Role</label>
            <select name="role" id="role" class="select" required>
                <option value=""></option>
                <option value="admin" <?php echo ($role == "admin") ? "selected" : ""; ?>>Admin</option>
                <option value="student" <?php echo ($role == "student") ? "selected" : ""; ?>>Student</option>
                <option value="teacher" <?php echo ($role == "teacher") ? "selected" : ""; ?>>Teacher</option>
                <option value="staff" <?php echo ($role == "staff") ? "selected" : ""; ?>>Staff</option>
                <option value="department_head" <?php echo ($role == "department_head") ? "selected" : ""; ?>>Department Head</option>
            </select>
            <span class="help-block"><?php echo $role_err; ?></span>
        </div>

        <div class="input-field form-group">
            <label for="department">Department:</label>
            <input type="text" id="department" name="department" class="form-control" value="<?php echo $department; ?>" />
        </div>
        
        <div class="button-group">
            <button type="submit">Submit</button>
            <button type="reset">Reset</button>
        </div>
        <div class="register">
            <p>Already have an account? <a href="login.php" onclick="toggleForms()">Login</a></p>
        </div>
    </form>
</div>

    

        <!-- Sign-up Form -->
        <!-- <div class="wrapper" id="signup-form" style="display: block;">
      <form action="signup.php" method="post">
        <h2>Sign Up</h2>
        <div class="input-field">
          <input type="text" name="username" required />
          <label>Username</label>
        </div>
        <div class="input-field">
          <input type="email" name="email" required />
          <label>Email</label>
        </div>
        <div class="input-field">
          <input type="password" name="password" required />
          <label>Password</label>
        </div>
        <div class="input-field">
          <input type="password" name="confirm_password" required />
          <label>Confirm Password</label>
        </div>
        <div class="input-field">
          <select name="role" class="select" required>
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
            <option value="staff">Staff</option>
            <option value="department_head">Department Head</option>
          </select>
        </div>
        <div class="input-field">
          <input type="text" name="department" required />
          <label>Department</label>
        </div>
        <div class="button-group">
          <button type="submit">Submit</button>
          <button type="reset">Reset</button>
        </div>
        <div class="register">
          <p>Already have an account? <a href="#" onclick="toggleForms()">Login</a></p>
        </div>
      </form>
    </div>
  </div> -->
</body>
</html>

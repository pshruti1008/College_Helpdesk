<?php
session_start();
require_once "config.php";
require_once "auth.php";
require_once "functions.php";

requireLogin();

$title = $description = $priority = "";
$title_err = $description_err = $priority_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = sanitize_input($_POST["title"]);
    $description = sanitize_input($_POST["description"]);
    $priority = sanitize_input($_POST["priority"]);
    
    if (empty($title)) {
        $title_err = "Please enter a title.";
    }
    if (empty($description)) {
        $description_err = "Please enter a description.";
    }
    if (empty($priority)) {
        $priority_err = "Please select a priority.";
    }
    
    if (empty($title_err) && empty($description_err) && empty($priority_err)) {
        $sql = "INSERT INTO tickets (title, description, priority, created_by, status) VALUES (?, ?, ?, ?, 'pending')";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssi", $title, $description, $priority, $_SESSION["id"]);
            
            if (mysqli_stmt_execute($stmt)) {
                header("location: home.php");
                exit();
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
    <title>Create Ticket</title>
   <style>
    /* Importing Google Font */
@import url("https://fonts.googleapis.com/css2?family=Open+Sans:wght@200;300;400;500;600;700&display=swap");

/* General reset and font styling */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Open Sans", sans-serif;
}



/* Body styling */
body {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    width: 100%;
    padding: 0 10px;
    background: white; /* Change background to white */
}

/* Wrapper for the form */
.wrapper {
    width: 500px;
    height: auto; /* Increase the width of the wrapper */
    padding: 20px; /* Inner padding */
    background-color: #fff;/* Change background color to dark */
    border-radius: 8px; /* Rounded corners */
    border: 1px solid rgba(255, 255, 255, 0.5); /* Border color */
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15); /* Shadow effect */
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
  justify-content: space-evenly;/* Center contents horizontally */
  gap: 30px; /* Space between image and form */
  max-width: 1200px; /* Control overall width */
  width: 90%; /* Take 90% of the screen width */
  padding: 40px;
  background-color: white;
  border-radius: 8px;
  border: 1px solid black;
    }

    .image-container img {
      width: 300px; /* Adjust the image size */
      height: auto;
      border-radius: 2px;
    }
/* Header styling */
h2 {
    font-size: 2rem; /* Font size for the title */
    margin-bottom: 20px; /* Space below the title */
    color: black; /* Title color */
}

/* Form layout */
form {
    display: flex;
    flex-direction: column; /* Column layout for inputs */
    gap: 20px; /* Space between form elements */
}

/* Input field styling */
.form-group {
    position: relative; /* Position relative for labels */
}

.form-group label {
    color: black; /* Change label color to white */
    font-size: 16px; /* Label font size */
    transition: 0.2s ease; /* Smooth transition for label movement */
}

/* Input fields, select box, and textarea */
.form-control {
    width: 100%; /* Full width */
    height: 40px; /* Height for inputs */
    background: transparent; /* Transparent background */
    border: 2px solid black; /* White border */
    outline: none; /* No outline */
    font-size: 16px; /* Font size */
    color: #000; /* Black text color */
    padding: 10px; /* Padding for better UX */
    border-radius: 5px; /* Rounded corners */
}

/* Textarea specific styling */
textarea {
    height: 100px; /* Height for textarea */
}

/* Dropdown and select box */
.form-control select {
    background-color: transparent; /* Transparent background */
}

/* Error message styling */
.help-block {
    color: #f44336; /* Error message color */
    font-size: 12px; /* Smaller font size for error messages */
}

/* Focus and hover styles for inputs and select */
.form-control:focus {
    border-color: black; /* Change border color on focus */
}

.form-control:focus + label,
.form-control:not(:placeholder-shown) + label {
    top: -10px; /* Move label up */
    font-size: 14px; /* Smaller font size */
    color: black; /* Change color */
}

/* Button styling */
.btn {
    background: #007bff; /* Button background color */
    color: #fff; /* Button text color */
    border: none; /* No border */
    padding: 12px 20px; /* Button padding */
    cursor: pointer; /* Pointer cursor */
    border-radius: 5px; /* Rounded corners for buttons */
    font-size: 16px; /* Button font size */
    transition: background-color 0.3s; /* Transition for hover effects */
    width: 48%; /* Set width for the buttons */
}

.btn:hover {
    background: rgba(0, 123, 255, 0.7); /* Darker blue on hover */
}

/* Cancel button styles */
.btn-default {
    background: #ccc; /* Default background color for cancel button */
    color: #000; /* Text color for cancel button */
}

.btn-default:hover {
    background: rgba(200, 200, 200, 0.7); /* Darker grey on hover */
}

/* Add spacing between buttons */
.button-group {
    display: flex;
    justify-content: space-between; /* Space between buttons */
    gap: 10px; /* Gap between buttons */
}

/* Style for the select dropdown */
#select-priority .form-control {
    background: transparent; /* Make background transparent */
    color: black; /* Font color for the dropdown */
    border: 2px solid #ccc; /* Border style */
    padding: 10px; /* Padding for better usability */
    border-radius: 5px; /* Rounded corners */
    height: 40px; /* Height for the dropdown */
    appearance: none; /* Remove default dropdown arrow */
}

/* Dropdown options styling */
#select-priority .form-control option {
    background-color: transparent; /* Transparent background for options */
    color: black; /* Font color for options */
}

/* Focus and hover effects */
#select-priority .form-control:focus {
    border-color: #007bff; /* Change border color on focus */
    outline: none; /* Remove outline */
}

/* Change background color on hover */
#select-priority .form-control:hover {
    background-color: rgba(0, 123, 255, 0.1); /* Optional: Change background color on hover */
}
.carousel {
    position: relative;
    width: 300px; /* Set to the desired width */
    height: 200px; /* Set to the desired height */
    overflow: hidden; /* Hide overflow to keep the images contained */
    border-radius: 8px; /* Optional: rounded corners */
}

.carousel-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: contain; /* Cover the container */
    opacity: 0; /* Initially hide all images */
    transition: opacity 1s ease; /* Smooth transition */
}

.carousel-image.active {
    opacity: 1; /* Show the active image */
}


   </style>

</head>
<body>
<div class="form-container">
      <div class="image-container">
          <div class="carousel">
              <img src="img5.png" alt="Image 1" class="carousel-image active">
              <img src="hero-bg.png" alt="Image 2" class="carousel-image">
              <img src="img3.jpg" alt="Image 3" class="carousel-image">
          </div>
      </div>
        <div class=" wrapper">
        <h2>Create New Ticket</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($title_err)) ? 'has-error' : ''; ?>">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo $title; ?>">
                <span class="help-block"><?php echo $title_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($description_err)) ? 'has-error' : ''; ?>">
                <label>Description</label>
                <textarea name="description" class="form-control"><?php echo $description; ?></textarea>
                <span class="help-block"><?php echo $description_err; ?></span>
            </div>
            <div id="select-priority" class="form-group  <?php echo (!empty($priority_err)) ? 'has-error' : ''; ?>">
                <label>Priority</label>
                <select name="priority" class="form-control">
                    <option value="">Select Priority</option>
                    <option value="low" <?php echo ($priority == "low") ? "selected" : ""; ?>>Low</option>
                    <option value="medium" <?php echo ($priority == "medium") ? "selected" : ""; ?>>Medium</option>
                    <option value="high" <?php echo ($priority == "high") ? "selected" : ""; ?>>High</option>
                </select>
                <span class="help-block"><?php echo $priority_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <a href="home.php" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>  
    <script>
        let currentIndex = 0;
        const images = document.querySelectorAll('.carousel-image');
        const totalImages = images.length;

        function changeImage() {
            images[currentIndex].classList.remove('active'); // Remove active class from current image
            currentIndex = (currentIndex + 1) % totalImages; // Increment index and loop around
            images[currentIndex].classList.add('active'); // Add active class to the next image
        }

        setInterval(changeImage, 3000); // Change image every 3 seconds
    </script>
</body>
</html>
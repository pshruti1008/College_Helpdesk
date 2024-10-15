<?php
session_start();

require_once "config.php";
require_once "auth.php";
require_once "functions.php";

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Get the user's information from the session
$user_id = $_SESSION["id"];
$role = $_SESSION["role"];

// Retrieve tickets for the logged-in user
$tickets = get_tickets_by_user($conn, $user_id, $role);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>College Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap");

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: #e8f0f8;
            color: #333;
        }

        /* Header and Navbar styling */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 85px;
            background: #263238;
            z-index: 1001;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 40px;
            height: 100%;
            width: 100%;
        }

        .nav_logo {
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
        }

        .menu_items {
            display: flex;
            list-style: none;
            gap: 25px;
        }

        .menu_items li a {
            color: #ffffff;
            text-decoration: none;
            font-size: 18px;
            transition: color 0.3s;
        }

        .menu_items li a:hover {
            color: #ff7043;
        }

        /* Sidebar styling */
        .sidebar {
            position: fixed;
            top: 85px;
            left: 0;
            bottom: 0;
            width: 270px;
            background-color: #37474f;
            color: #ffffff;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 3px 0 6px rgba(0, 0, 0, 0.15);
        }

        .sidebar-header {
            padding: 25px;
            background-color: #263238;
            color: #ffffff;
            font-size: 1.6em;
            font-weight: bold;
            text-align: center;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 25px;
        }

        .sidebar ul li {
            margin-bottom: 18px;
        }

        .sidebar ul li a {
            color: #ffffff;
            text-decoration: none;
            font-size: 17px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .sidebar ul li a:hover {
            color: #ff7043;
        }

        /* Main content styling */
        .hero {
            margin-left: 270px;
            padding-top: 110px;
            min-height: 100vh;
            background: #e8f0f8;
        }

        .hero .row {
            display: flex;
            padding: 45px;
            gap: 35px;
        }

        .hero .column {
            flex: 1;
        }

        .hero h2 {
            font-size: 38px;
            margin-bottom: 18px;
            color: #263238;
        }

        .hero p {
            color: #555;
            font-size: 19px;
        }

        .buttons {
            display: flex;
            margin-top: 30px;
            gap: 12px;
        }

        .btn {
            padding: 16px 30px;
            background: #ff7043;
            color: #ffffff;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background-color: #ff5722;
        }

        .btn:last-child {
            border: 2px solid #ff7043;
            background: transparent;
            color: #ff7043;
        }

        .btn:last-child:hover {
            background-color: #ff7043;
            color: #ffffff;
        }

        .hero_img {
            width: 100%;
            max-width: 550px;
            height: auto;
        }

        /* Responsive styles */
        @media (max-width: 860px) {
            .sidebar {
                left: -270px;
                transition: left 0.3s ease;
            }

            .sidebar.active {
                left: 0;
            }

            .hero {
                margin-left: 0;
            }

            .hero .row {
                flex-direction: column;
            }

            #menu_toggle {
                display: block;
            }
        }

        @media (max-width: 600px) {
            .hero h2 {
                font-size: 28px;
            }

            .buttons {
                justify-content: center;
            }

            .btn {
                padding: 12px 18px;
            }
        }

        #menu_toggle {
            display: none;
            cursor: pointer;
        }

        footer {
            background-color: #263238;
            color: #ffffff;
            padding: 25px;
            text-align: center;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #ffffff;
            font-size: 19px;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #ff7043;
        }

        .copyright {
            font-size: 15px;
        }
    </style
  </head>
  <body>
    <div class="sidebar">
      <div class="sidebar-header">Dashboard</div>
      <ul>
      <?php if ($role === 'teacher' || $role === 'staff' || $role === 'student'): ?>
    <li><a href="create_ticket.php"><i class="fas fa-plus-circle"></i> Create New Ticket</a></li>
    <?php endif; ?>
    <?php if ($role === 'teacher' || $role === 'staff' || $role === 'student' || $role === 'department_head'): ?>
    <li><a href="view_all_tickets.php"><i class="fas fa-ticket-alt"></i> View All Tickets</a></li>
    <?php endif; ?>
    <?php if ($role === 'admin'): ?>
        <li><a href="view_feedback.php"><i class="fas fa-comments"></i> Feedback</a></li>
        <li><a href="manage.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
        <li><a href="manage_tickets.php"><i class="fas fa-tasks"></i> Manage Tickets</a></li>
    <?php endif; ?>
    <?php if ($role === 'department_head'): ?>
        <li><a href="assign.php"><i class="fas fa-user-check"></i> Assign Tickets</a></li>
        <li><a href="reassign_ticket.php"><i class="fas fa-exchange-alt"></i> Reassign Escalated Tickets</a></li>
        <li><a href="view_feedback.php"><i class="fas fa-comments"></i> Feedback</a></li>
    <?php endif; ?>
    <?php if ($role === 'teacher' || $role === 'staff'): ?>
        <li><a href="assigned_ticket.php"><i class="fas fa-user-tag"></i> Assigned Tickets</a></li>
        <li><a href="escalate_ticket.php"><i class="fas fa-arrow-up"></i> Escalate Ticket</a></li>
    <?php endif; ?>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a></li>
</ul>

    </div>

    <header>
      <nav class="nav">
      
        <h2 class="nav_logo"><a href="#" style="color: #fff; text-decoration: none;">College Helpdesk</a></h2>
        <ul class="menu_items">
          <li><a href="#" class="nav_link">Home</a></li>
          <li><a href="about.html" class="nav_link">About</a></li>
          <li><a href="services.html" class="nav_link">Service</a></li>
          <li><a href="faqs.html" class="nav_link">FAQ</a></li>
          <li><a href="contact_us.php" class="nav_link">Contact</a></li>
        </ul>
        <img src="images/bars.svg" alt="menuicon" id="menu_toggle" />
      </nav>
    </header>
          <br><br><br><br>
    <section class="hero">
      <center>
      <div class="row">
        <div class="column">
          <h2>Innovative Solutions for Your College Helpdesk Needs</h2>
          <p>Welcome to our College Helpdesk, where we are dedicated to providing efficient and innovative support for all your academic needs. Our team of knowledgeable staff is here to assist you with a wide range of services, from troubleshooting technical issues to offering guidance on academic resources. Whether you have questions about course registration, library services, or online learning platforms, we're just a click away. Experience seamless support that empowers you to thrive in your academic journey!</p>
          <div class="buttons">
          <a href="readmore.php"><button class="btn">Read More</button></a>
          <a href="contact_us.php"><button class="btn">Contact Us</button></a>
          </div>
        </div>
        <div class="column">
          <img src="homep1.png" alt="heroImg" class="hero_img" />
        </div>
      </div>
      </center>
    </section>


    <script>
      const sidebar = document.querySelector(".sidebar");
      const menuToggle = document.getElementById("menu_toggle");

      menuToggle.addEventListener("click", () => {
        sidebar.classList.toggle("active");
      });
    </script>
     <!-- Footer -->
     <footer>
        <div class="footer-links">
            <a href="#"><i class="fas fa-phone"></i> Contact Us</a>
            <a href="#"><i class="fas fa-envelope"></i> Email Us</a>
            <a href="#"><i class="fas fa-map-marker-alt"></i> Visit Us</a>
        </div>
        <div>
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
        <div class="copyright">
            &copy; 2024 College Helpdesk. All Rights Reserved.
        </div>
    </footer>


  </body>
</html>
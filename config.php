

<?php

// Create connection
$conn=mysqli_connect("localhost","root","");
mysqli_select_db($conn,"college_helpdesk");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

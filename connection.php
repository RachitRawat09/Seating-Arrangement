<?php
// Step 1: Database connection

// Create connection to the database on port 3307
$conn = new mysqli("localhost", "root", "", "sittingarangement", "3307");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>

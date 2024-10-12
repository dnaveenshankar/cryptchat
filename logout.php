<?php
// Include the database connection file
include 'db_connection.php';

// End the session
session_start();
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>

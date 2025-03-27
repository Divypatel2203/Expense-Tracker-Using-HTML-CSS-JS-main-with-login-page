<?php
// Start the session to access session variables
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect back to the home page
header("Location: index.html");
exit();
?> 
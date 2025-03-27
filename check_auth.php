<?php
// Start the session to access session variables
session_start();

// Default response is not logged in
$response = array('logged_in' => false);

// Check if the user is logged in by verifying session variables
if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
    // User is logged in
    $response['logged_in'] = true;
    $response['user_id'] = $_SESSION['user_id'];
    $response['username'] = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
    $response['email'] = $_SESSION['user_email'];
}

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?> 
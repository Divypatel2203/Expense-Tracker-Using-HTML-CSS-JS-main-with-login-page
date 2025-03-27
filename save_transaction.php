<?php
// Start the session to access session variables
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get the user ID from session
$user_id = $_SESSION['user_id'];

// Connect to database
$conn = new mysqli('localhost', 'root', '', 'expense_tracker');

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Check if action is set
if (!isset($_POST['action'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit();
}

$action = $_POST['action'];

// Handle different actions
if ($action === 'add_transaction') {
    // Add a new transaction
    if (!isset($_POST['description']) || !isset($_POST['amount']) || !isset($_POST['transaction_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }
    
    $description = $conn->real_escape_string($_POST['description']);
    $amount = floatval($_POST['amount']);
    $transaction_id = $conn->real_escape_string($_POST['transaction_id']);
    $date = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO transactions (user_id, transaction_id, description, amount, created_at) 
            VALUES ('$user_id', '$transaction_id', '$description', $amount, '$date')";
    
    if ($conn->query($sql) === TRUE) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Transaction added successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
} 
elseif ($action === 'delete_transaction') {
    // Delete a transaction
    if (!isset($_POST['transaction_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Transaction ID is required']);
        exit();
    }
    
    $transaction_id = $conn->real_escape_string($_POST['transaction_id']);
    
    $sql = "DELETE FROM transactions WHERE user_id = '$user_id' AND transaction_id = '$transaction_id'";
    
    if ($conn->query($sql) === TRUE) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Transaction deleted successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
}
else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

// Close the database connection
$conn->close();
?> 
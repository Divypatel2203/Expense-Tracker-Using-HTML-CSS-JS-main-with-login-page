<?php
// Start the session to access session variables
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get the user ID from session
$user_id = $_SESSION['user_id'];

// Connect to database
$conn = new mysqli('localhost', 'root', '', 'expense_tracker');

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Query to get all transactions for the user
$sql = "SELECT transaction_id, description, amount FROM transactions WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = $conn->query($sql);

// Check if query was successful
if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Error retrieving transactions: ' . $conn->error]);
    exit();
}

// Format transactions for the frontend
$transactions = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $transactions[] = [
            'id' => $row['transaction_id'],
            'text' => $row['description'],
            'amount' => (float)$row['amount']
        ];
    }
}

// Return success response with transactions
echo json_encode([
    'success' => true,
    'transactions' => $transactions
]);

// Close the database connection
$conn->close();
?> 
<?php
// Start session
session_start();

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "expense_tracker";

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => "Connection failed: " . $conn->connect_error
    ]));
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if (!$conn->query($sql)) {
    die(json_encode([
        'success' => false,
        'message' => "Error creating database: " . $conn->error
    ]));
}

// Select the database
$conn->select_db($database);

// Create users table if not exists
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    occupation VARCHAR(50) NOT NULL,
    monthlyIncome DECIMAL(10,2) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die(json_encode([
        'success' => false,
        'message' => "Error creating table: " . $conn->error
    ]));
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'register':
            register();
            break;
            
        case 'login':
            login();
            break;
            
        case 'export':
            exportData();
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
}

// Function to register a new user
function register() {
    global $conn;
    
    // Get form data
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $occupation = $_POST['occupation'];
    $monthlyIncome = $_POST['monthlyIncome'];
    $password = $_POST['password'];
    
    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($occupation) || empty($monthlyIncome) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        return;
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists'
        ]);
        return;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, phone, occupation, monthlyIncome, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssds", $firstName, $lastName, $email, $phone, $occupation, $monthlyIncome, $hashedPassword);
    
    if ($stmt->execute()) {
        // Set session data
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['user_email'] = $email;
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

// Function to login user
function login() {
    global $conn;
    
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email and password are required'
        ]);
        return;
    }
    
    // Get user data
    $stmt = $conn->prepare("SELECT id, firstName, lastName, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
            $_SESSION['user_email'] = $user['email'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid password'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
    
    $stmt->close();
}

// Function to export user data to Excel
function exportData() {
    global $conn;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Please login to export data'
        ]);
        return;
    }
    
    $userId = $_SESSION['user_id'];
    
    // Get user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Create CSV data
        $csvData = "User ID,First Name,Last Name,Email,Phone,Occupation,Monthly Income,Created At\n";
        $csvData .= $user['id'] . "," . $user['firstName'] . "," . $user['lastName'] . "," . $user['email'] . "," . 
                   $user['phone'] . "," . $user['occupation'] . "," . $user['monthlyIncome'] . "," . $user['created_at'] . "\n";
        
        // Output CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=user_data.csv');
        echo $csvData;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
    
    $stmt->close();
}
?> 
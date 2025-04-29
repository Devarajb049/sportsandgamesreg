<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php'; 
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Check if required fields are set
if (!isset($_POST['username'], $_POST['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Validate if username exists in database
$stmt = $conn->prepare("SELECT * FROM `admin_users` WHERE username = ? LIMIT 1");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // If password is stored as plaintext, compare directly
    if ($password === $row['password']) { 
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_username'] = $row['username'];

        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    exit;
}
?>
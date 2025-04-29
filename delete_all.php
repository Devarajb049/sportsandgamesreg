<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sports_registration";

// Create connection (ensure correct parameters)
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Start transaction to maintain integrity
$conn->begin_transaction();

try {
    // Delete from student_sports first (to maintain foreign key constraints)
    $conn->query("DELETE FROM student_sports");
    
    // Delete from students
    $conn->query("DELETE FROM students");

    // Commit transaction
    $conn->commit();
    echo json_encode(["status" => "success", "message" => "All records deleted successfully!"]);
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Error deleting records: " . $e->getMessage()]);
}

$conn->close();
?>

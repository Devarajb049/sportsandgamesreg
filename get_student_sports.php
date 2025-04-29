<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sports_registration";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed."]));
}

if (isset($_GET['pin_number'], $_GET['student_name'], $_GET['phno'])) {
    $pin_number = trim($_GET['pin_number']);
    $student_name = trim($_GET['student_name']);
    $phno = trim($_GET['phno']);

    // Check if student exists
    $sql = "SELECT id FROM students WHERE pin_number = ? AND student_name = ? AND phno = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $pin_number, $student_name, $phno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["error" => "<span style='color: red;'>No student found with these details.</span>"]);
        exit();
    }

    $row = $result->fetch_assoc();
    $student_id = $row['id'];

    // Fetch selected sports
    $sql_sports = "SELECT sport_name FROM student_sports WHERE student_id = ?";
    $stmt_sports = $conn->prepare($sql_sports);
    $stmt_sports->bind_param("i", $student_id);
    $stmt_sports->execute();
    $result_sports = $stmt_sports->get_result();

    $sports = [];
    while ($row_sport = $result_sports->fetch_assoc()) {
        $sports[] = $row_sport['sport_name'];
    }

    echo json_encode($sports);
    exit();
}
?>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sports_registration";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve and sanitize form data
$student_name = trim($_POST['student_name']);
$pin_number = trim($_POST['pin_number']);
$phno = trim($_POST['phno']);
$options = isset($_POST['options']) ? $_POST['options'] : [];

if (empty($student_name) || empty($pin_number) || empty($phno)) {
    echo "<script>alert('Error: Please fill in all required fields.'); window.location.href='Student_Details.html';</script>";
    exit();
}

// Extract Year and Branch from PIN
$pin_pattern = '/^(\d{2})101-(CM|EC|EE|M)-(\d{3})$/i';
if (preg_match($pin_pattern, $pin_number, $matches)) {
    $pin_year = $matches[1];
    $pin_branch_code = strtoupper($matches[2]);
    $year_mapping = ["22" => "3", "23" => "2", "24" => "1"];
    $year = $year_mapping[$pin_year] ?? "";
    $branch_mapping = ["CM" => "DCME", "EC" => "DECE", "EE" => "DEEE", "M" => "DME"];
    $branch = $branch_mapping[$pin_branch_code] ?? "";

    if (!$year || !$branch) {
        echo "<script>alert('Error: Invalid PIN Number!'); window.location.href='Student_Details.html';</script>";
        exit();
    }
} else {
    echo "<script>alert('Error: Invalid PIN format!'); window.location.href='Student_Details.html';</script>";
    exit();
}

try {
    // Check if student already exists
    $sql_check = "SELECT id FROM students WHERE pin_number = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $pin_number);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo "<script>alert('Error: Student with this PIN Number already registered!'); window.location.href='Student_Details.html';</script>";
        exit();
    }
    $stmt_check->close();

    // Insert student data
    $sql_insert_student = "INSERT INTO students (student_name, pin_number, year, branch, phno) VALUES (?, ?, ?, ?, ?)";
    $stmt_student = $conn->prepare($sql_insert_student);
    $stmt_student->bind_param("sssss", $student_name, $pin_number, $year, $branch, $phno);

    if ($stmt_student->execute()) {
        $student_id = $stmt_student->insert_id;

        // Insert selected sports into student_sports table
        if (!empty($options)) {
            $sql_insert_sports = "INSERT INTO student_sports (student_id, sport_name) VALUES (?, ?)";
            $stmt_sport = $conn->prepare($sql_insert_sports);
            foreach ($options as $sport) {
                $stmt_sport->bind_param("is", $student_id, $sport);
                $stmt_sport->execute();
            }
            $stmt_sport->close();
        }

        // Success Message with PDF Download
        echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Registration Successful</title>
                <link rel="shortcut icon" href="IMG/FAVICON.png" type="image/x-icon">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="CSS/style.css">
            </head>
            <body class="container mt-5">
                <div class="card p-4 shadow-sm">
                    <center><h2 class="text-success">Registration Successful!</h2></center>
                    <h3>Student Details:</h3>
                    <p><strong>Name:</strong> ' . htmlspecialchars($student_name) . '</p>
                    <p><strong>PIN Number:</strong> ' . htmlspecialchars($pin_number) . '</p>
                    <p><strong>Year:</strong> ' . htmlspecialchars($year) . '</p>
                    <p><strong>Branch:</strong> ' . htmlspecialchars($branch) . '</p>
                    <p><strong>Mobile Number:</strong> ' . htmlspecialchars($phno) . '</p>';
        if (!empty($options)) {
            echo '<p><strong>Selected Sports:</strong> ' . htmlspecialchars(implode(", ", $options)) . '</p>';
        }
        echo '
                    <br>
                    <button onclick="generatePDF()" class="btn btn-success">Download PDF</button>
                    <br><br>
                    <a href="#" class="btn btn-primary" onclick="goHome()">Register Another Student</a>

                    <script>
                        function goHome() {
                            window.location.href = "Student_Details.html"; 
                        }
                    </script>
                </div>

                <script>
                    function generatePDF() {
                        const { jsPDF } = window.jspdf;
                        let doc = new jsPDF();
                        doc.setFont("helvetica", "bold");
                        doc.setFontSize(16);
                        doc.text("Student Registration Details", 10, 20);
                        doc.setFontSize(12);
                        doc.setFont("helvetica", "normal");
                        let studentDetails = [
                            ["Name", "' . addslashes($student_name) . '"],
                            ["PIN Number", "' . addslashes($pin_number) . '"],
                            ["Year", "' . addslashes($year) . '"],
                            ["Branch", "' . addslashes($branch) . '"],
                            ["Mobile Number", "' . addslashes($phno) . '"],
                            ["Selected Sports", "' . (!empty($options) ? addslashes(implode(", ", $options)) : "None") . '"]
                        ];
                        doc.autoTable({
                            startY: 30,
                            head: [["Field", "Details"]],
                            body: studentDetails,
                            theme: "striped",
                        });
                        doc.save("Student_Details_' . addslashes($pin_number) . '.pdf");
                    }
                </script>

                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
            </body>
            </html>';
    } else {
        throw new Exception("Error: Registration failed, please try again.");
    }
} catch (Exception $e) {
    echo "<script>alert('" . $e->getMessage() . "'); window.location.href='Student_Details.html';</script>";
} finally {
    $stmt_student->close();
    $conn->close();
}

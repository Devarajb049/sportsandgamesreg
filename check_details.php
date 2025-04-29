<?php
include 'db.php'; // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_name = isset($_POST['student_name']) ? trim($_POST['student_name']) : '';
    $pin_number = isset($_POST['pin_number']) ? trim($_POST['pin_number']) : '';
    $phno = isset($_POST['phno']) ? trim($_POST['phno']) : '';

    if (empty($student_name) || empty($pin_number) || empty($phno)) {
        echo "<div class='text-center text-danger'><h3>Please fill in all fields.</h3></div>";
        exit;
    }

    // Query to fetch student details
    $query = "SELECT * FROM students WHERE student_name = ? AND pin_number = ? AND phno = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $student_name, $pin_number, $phno);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize sports statement
    $sports_stmt = null;

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();

        // Fetch selected sports
        $sports_query = "SELECT sport_name FROM student_sports WHERE student_id = ?";
        $sports_stmt = $conn->prepare($sports_query);
        $sports_stmt->bind_param("i", $student['id']);
        $sports_stmt->execute();
        $sports_result = $sports_stmt->get_result();

        $selected_sports = [];
        while ($row = $sports_result->fetch_assoc()) {
            $selected_sports[] = $row['sport_name'];
        }
?>
        <link rel="shortcut icon" href="IMG/FAVICON.png" type="image/x-icon">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="CSS/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 20px;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }

            .container {
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
                max-width: 500px;
                width: 100%;
                text-align: left;
            }

            .fa-arrow-left {
                position: absolute;
                top: 20px;
                left: 20px;
                font-size: 20px;
                cursor: pointer;
                border-radius: 50%;
                background-color: rgba(0, 0, 0, 0.2);
                padding: 10px;
                transition: background-color 0.3s ease;
            }

            .fa-arrow-left:hover {
                transform: scale(1.1);
            }
        </style>

        <nav class="fixed-top">
            <i class="fa fa-arrow-left" aria-hidden="true"></i>
        </nav>

        <div class="container">
            <center>
                <h3 class="text-success">Student Details Found</h3>
            </center>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($student['student_name']); ?></p>
            <p><strong>PIN Number:</strong> <?php echo htmlspecialchars($student['pin_number']); ?></p>
            <p><strong>Year:</strong> <?php echo htmlspecialchars($student['year']); ?></p>
            <p><strong>Branch:</strong> <?php echo htmlspecialchars($student['branch']); ?></p>
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($student['phno']); ?></p>
            <p><strong>Selected Games:</strong> <?php echo htmlspecialchars(implode(", ", $selected_sports)); ?></p>
            <button onclick="generatePDF()" class="btn btn-primary">Download PDF</button>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
        <script>
            function generatePDF() {
                const {
                    jsPDF
                } = window.jspdf;
                let doc = new jsPDF();
                doc.text('Student Details', 10, 20);
                doc.setFontSize(12);

                let details = [
                    ['Name', '<?php echo addslashes($student['student_name']); ?>'],
                    ['PIN Number', '<?php echo addslashes($student['pin_number']); ?>'],
                    ['Year', '<?php echo addslashes($student['year']); ?>'],
                    ['Branch', '<?php echo addslashes($student['branch']); ?>'],
                    ['Phone Number', '<?php echo addslashes($student['phno']); ?>'],
                    ['Selected Games', '<?php echo addslashes(implode(", ", $selected_sports)); ?>']
                ];

                doc.autoTable({
                    startY: 30,
                    head: [
                        ['Field', 'Details']
                    ],
                    body: details,
                    theme: 'striped'
                });

                doc.save('Student_Details_<?php echo addslashes($student['pin_number']); ?>.pdf');
            }

            document.querySelector('.fa-arrow-left').addEventListener('click', function(event) {
                event.preventDefault();
                history.back();
            });
        </script>
    <?php
    } else {
    ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>No Records Found</title>
            <link rel="stylesheet" href="CSS/style.css">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    background-color: #f4f4f4;
                }

                .error-container {
                    text-align: center;
                    background: #fff;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
                    max-width: 400px;
                }
            </style>
        </head>

        <body>
            <div class="error-container">
                <h3 class="text-danger">No matching records found.</h3>
                <p>Please check your details and try again.</p>
                <button onclick="history.back()" class="btn btn-primary">Go Back</button>
            </div>
        </body>

        </html>
<?php
    }

    // Close statements and DB connection
    $stmt->close();
    if ($sports_stmt !== null) {
        $sports_stmt->close();
    }
    $conn->close();
}
?>
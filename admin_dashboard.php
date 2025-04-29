<?php

session_start(); // Start the session

if (!isset($_SESSION['admin_username'])) {
    // If session exists, redirect to the dashboard or home page
    header("Location: Admin_login.php");
    exit();
}


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sports_registration";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Predefined years and branches
$years = ["I", "II", "III"];
$branches = ["DCME", "DEEE", "DECE", "DME"];
$sportsList = ["100mts", "200mts", "400mts", "800mts", "1500mts", "100mtsrel", "400mtsrel", "High Jump", "Long Jump", "Triple Jump", "Javelin Throw", "Discus Throw", "Shot Put", "Shuttle Singles", "Shuttle Doubles", "Ball Badminton", "Table Tennis Singles", "Table Tennis Doubles", "Chess", "Volleyball", "Kabaddi"];


// Filtering logic
$yearFilter = isset($_GET['year']) && $_GET['year'] !== "" ? $_GET['year'] : "";
$branchFilter = isset($_GET['branch']) && $_GET['branch'] !== "" ? $_GET['branch'] : "";
$sportFilter = isset($_GET['sport']) && $_GET['sport'] !== "" ? $_GET['sport'] : "";


$sql = "SELECT students.id, students.student_name, students.pin_number, students.year, students.branch, students.phno,
               GROUP_CONCAT(student_sports.sport_name SEPARATOR ', ') AS sports
        FROM students
        LEFT JOIN student_sports ON students.id = student_sports.student_id";


// Apply filters if selected
$conditions = [];
if ($yearFilter !== "") {
    $conditions[] = "students.year = '$yearFilter'";
}
if ($branchFilter !== "") {
    $conditions[] = "students.branch = '$branchFilter'";
}
if ($sportFilter !== "") {
    $conditions[] = "student_sports.sport_name = '$sportFilter'";
}
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " GROUP BY students.id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Students</title>
    <link rel="shortcut icon" href="IMG/FAVICON.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <style>
        .btn-primary {
            background-color: #336699 !important;
            border: none !important;
        }

        #tableContainer {
            max-height: 500px;
            overflow-y: auto;
        }

        #studentsTable {
            table-layout: fixed;
            width: 100%;
        }

        #studentsTable th,
        #studentsTable td {
            word-wrap: break-word;
            text-align: center;
            white-space: wrap;
        }

        .form-select {

            border: 2px solid #336699;
            border-radius: 50px;
            text-align: center;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container mt-5 col-md-12">
        <h2 class="mb-4 text-center">Registered Students</h2>

        <!-- Filter Form -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-2">
                    <label for="year" class="form-label text-wrap" style="font-weight: bold;">Select Year:</label>
                    <select name="year" id="year" class="form-select">
                        <option value="">All Years</option>
                        <?php foreach ($years as $year) { ?>
                            <option value="<?= $year ?>" <?= $yearFilter == $year ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="branch" class="form-label text-wrap" style="font-weight: bold;">Select Branch:</label>
                    <select name="branch" id="branch" class="form-select">
                        <option value="">All Branches</option>
                        <?php foreach ($branches as $branch) { ?>
                            <option value="<?= $branch ?>" <?= $branchFilter == $branch ? 'selected' : '' ?>>
                                <?= $branch ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2 me-5">
                    <label for="sport" class="form-label text-wrap" style="font-weight: bold;">Select Sport:</label>
                    <select name="sport" id="sport" class="form-select">
                        <option value="">All Sports</option>
                        <?php foreach ($sportsList as $sport) { ?>
                            <option value="<?= $sport ?>" <?= $sportFilter == $sport ? 'selected' : '' ?>>
                                <?= $sport ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end flex-wrap justify-content-around">
                    <button type="submit" class="btn btn-primary w-25  mb-2 m-1">Filter</button>
                    <a  href="upload.php" class="btn btn-success w-40 m-2" id="uploadWinnersButton">
                        Upload Winners
                    </a>

                    <a href="logout.php" class="btn btn-danger mt-2 mb-2 w-15 m-1">Logout </a>

                </div>
            </div>
        </form>

        <!-- Students Table -->
        <div id="tableContainer">
            <table class="table table-bordered table-hover" id="studentsTable">
                <thead class="table-primary">
                    <tr>
                        <th>Name</th>
                        <th>PIN Number</th>
                        <th>Year</th>
                        <th>Branch</th>
                        <th>Mobile Number</th>
                        <th>Sports Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $row['student_name'] ?></td>
                                <td><?= $row['pin_number'] ?></td>
                                <td><?= $row['year'] ?></td>
                                <td><?= $row['branch'] ?></td>
                                <td><?= $row['phno'] ?></td>
                                <td><?= $row['sports'] ? $row['sports'] : 'None' ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="6" class="text-center">No students found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Buttons for PDF and Excel Export -->
        <div class="d-flex  flex-wrap justify-content-between align-items-center mt-5">
            <div>
                <button id="deleteAll" class="btn btn-danger ms-auto m-2">Delete All Records</button>
            </div>
            <div>
                <button id="downloadPdf" class="btn btn-danger m-2">Download as PDF</button>
                <button id="downloadExcel" class="btn btn-success m-2">Export to Excel</button>
            </div>
        </div>

    </div>
 

     <script>
        $(document).ready(function() {
            $("#downloadPdf").click(function() {
                const {
                    jsPDF
                } = window.jspdf;
                let doc = new jsPDF();

                // Add title
                doc.setFontSize(16);
                doc.text("Registered Students", 14, 15);

                // Define columns and extract data from the table
                let columns = ["Name", "PIN Number", "Year", "Branch", "Mobile Number",
                    "Sports Registered"
                ];
                let rows = [];

                $("#studentsTable tbody tr").each(function() {
                    let rowData = [];
                    $(this).find("td").each(function() {
                        rowData.push($(this).text().trim());
                    });
                    rows.push(rowData);
                });

                // Add table to PDF
                doc.autoTable({
                    startY: 20,
                    head: [columns],
                    body: rows,
                    theme: 'grid',
                    styles: {
                        fontSize: 10,
                        cellPadding: 2,
                        lineWidth: 0.2,
                        lineColor: [0, 0, 0]
                    },
                    headStyles: {
                        fillColor: [255, 255, 255],
                        textColor: '#336699',
                        lineWidth: 0.2,
                        lineColor: [0, 0, 0]
                    }
                });


                // Save the PDF
                doc.save("Registered_Students.pdf");
            });
        });


        // Excel Export (CSV)
        $("#downloadExcel").click(function() {
            let table = document.getElementById("studentsTable");
            let rows = table.rows;
            let csvData = ["\ufeff" + "Name, PIN Number, Year, Branch,Mobile Number, Sports Registered"];

            // Loop through table rows
            for (let i = 1; i < rows.length; i++) { // Skip headers
                let rowData = [];
                let cells = rows[i].cells;
                for (let j = 0; j < cells.length; j++) {
                    rowData.push(cells[j].innerText);
                }
                csvData.push(rowData.join(","));
            }

            let csvFile = new Blob([csvData.join("\n")], {
                type: "text/csv;charset=utf-8;"
            });
            let csvURL = window.URL.createObjectURL(csvFile);
            let tempLink = document.createElement("a");
            tempLink.href = csvURL;
            tempLink.setAttribute("download", "Registered_Students.csv");
            tempLink.click();
        });


        //Delete All Records in Table
        $(document).ready(function() {
            $("#deleteAll").click(function() {
                if (confirm("Are you sure you want to delete all records? This action cannot be undone!")) {
                    $.ajax({
                        url: "delete_all.php",
                        type: "POST",
                        dataType: "json", // Expect JSON response
                        success: function(response) {
                            if (response.status === "success") {
                                alert(response.message);
                                location.reload(); // Refresh page after deletion
                            } else {
                                alert("Error: " + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            alert("An error occurred: " + xhr.responseText);
                        }
                    });
                }
            });
        });
        //modal

        $("#uploadForm").submit(function(event) {
            event.preventDefault(); // Prevent default form submission

            let formData = new FormData(this);

            $.ajax({
                url: "upload_winners.php", // Backend PHP file to handle the upload
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    alert(response); // Show response message
                    location.reload(); // Reload page after upload
                },
                error: function() {
                    alert("Error uploading file.");
                }
            });
        });
        $(document).ready(function() {
        $("#uploadWinnersButton").click(function() {
            $("#uploadModal").modal("show");
        });
    });
    </script>
    <!-- Bootstrap JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php $conn->close(); ?>
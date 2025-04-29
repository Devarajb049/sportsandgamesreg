<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sports_registration";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_sports'])) {
    $pin_number = trim($_POST['pin_number']);
    $student_name = trim($_POST['student_name']);
    $phno = trim($_POST['phno']);
    $options = isset($_POST['options']) ? $_POST['options'] : [];

    // Validate input
    if (empty($pin_number) || empty($student_name) || empty($phno)) {
        echo "<script>alert('Please enter all details (PIN, Name, Phone Number).'); window.location.href='update_sports.php';</script>";
        exit();
    }

    // Check if student exists and details match
    $sql_check = "SELECT id FROM students WHERE pin_number = ? AND student_name = ? AND phno = ?";
    $stmt_check = $conn->prepare($sql_check);
    if (!$stmt_check) {
        die("SQL Error: " . $conn->error);
    }
    $stmt_check->bind_param("sss", $pin_number, $student_name, $phno);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('No student found with these details. Please check and try again.'); window.location.href='update_sports.php';</script>";
        exit();
    }

    $row = $result->fetch_assoc();
    $student_id = $row['id'];

    // Remove old selections
    $sql_delete = "DELETE FROM student_sports WHERE student_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $student_id);
    $stmt_delete->execute();

    // Insert new selections
    if (!empty($options)) {
        $sql_insert = "INSERT INTO student_sports (student_id, sport_name) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        foreach ($options as $sport) {
            $stmt_insert->bind_param("is", $student_id, $sport);
            $stmt_insert->execute();
        }
    }

    echo "<script>alert('Sports selection updated successfully!'); window.location.href='update_sports.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Sports Selection</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="shortcut icon" href="IMG/FAVICON.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        body {
            overflow-y: auto;
        }

        .section-2 {
            height: 75vh;
        }

        .btn-primary:hover {
            background-color: rgba(var(--primary), 50%) !important;
            color: #000 !important;
        }

        .fa-arrow-left {
            position: fixed;
            top: 20px;
            left: 20px;
            font-size: 20px;
            cursor: pointer;
            border-radius: 50%;
            background-color: rgb(255, 255, 255);
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
            z-index: 1000;
        }

        .fa-arrow-left:hover {
            background-color: rgba(0, 0, 0, 0.4);
            transform: scale(1.2);
        }

        .category-title {
            font-weight: bold;
            font-size: 16px;
            margin-top: 15px;
        }

        .form-check {
            margin-left: 10px;
        }

        .input-group input {
            border: none;
            border-bottom: 2px solid var(--primary);
            background-color: transparent;
            outline: none;
            width: 100%;
            padding: 8px;
            transition: border-bottom 0.3s ease-in-out;
            border-radius: 0;
        }

        .input-group input:focus {
            outline: none;
            box-shadow: none;
        }

        .category-title {
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #336699;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            background-color: transparent;
            transition: background-color 0.3s;
        }

        .category-title:hover {
            background-color: #e9ecef;
        }

        .sports-list {
            display: none;
            padding: 10px;
            border-left: 2px solid #007bff;
            margin-left: 10px;
        }

        .form-check {
            margin-left: 15px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="d-flex justify-content-around bg p-2 align-items-center">
        <img src="IMG/Logo-James.png" alt="Logo" class="img-fluid logos">
        <center>
            <span class="text-white tw textss" style="font-size: 25px; font-weight: bold;">
                Dr Y C JAMES YEN GOVT POLYTECHNIC, KUPPAM
            </span>
        </center>
        <img src="IMG/Sbtet-Logo.png" alt="Sbtet-Logo" class="img-fluid logos">
    </header>

    <!-- Back Button -->
    <nav class="fixed-top">
        <i class="fa fa-arrow-left" aria-hidden="true"></i>
    </nav>

    <!-- Main Section -->
    <section class="section-2 d-flex justify-content-around align-items-center container-fluid mt-5">
        <form action="update_sports.php" method="POST" class="p-4 col-md-4 bg-white shadow-lg rounded">
            <center>
                <h5 class="tw fw-bold">Update Your Sports Selection</h5>
            </center>

            <div
                div class="mb-3 input-group">
                <input type="text" name="student_name" id="student_name" class="form-control" placeholder="Name" autocomplete="off" required>
            </div>
            <div class="mb-3 input-group">
                <input type="text" name="pin_number" id="pin_number" class="form-control" placeholder="PIN Number" autocomplete="off" required>
            </div>

            <div class="mb-3 input-group">
                <input type="tel" name="phno" id="phno" class="form-control" placeholder="Mobile Number" pattern="[0-9]{10}" autocomplete="off" maxlength="10" required>
            </div>

            <div id="error_message" class="text-danger fw-bold" style="display: none;"></div>

            <!-- Sports Selection -->
            <div id="sports_section" class="mb-3"></div>

            <button type="submit" name="update_sports" class="btn btn-primary w-100">Update</button>
        </form>
    </section>


    <script>
        // Go back to HOME page
        document.querySelector('.fa-arrow-left').addEventListener('click', function(event) {
            event.preventDefault();
            window.location.href = 'index.html';
        });

        function fetchStudentSports() {
            let pinNumber = document.getElementById('pin_number').value.trim();
            let studentName = document.getElementById('student_name').value.trim();
            let phno = document.getElementById('phno').value.trim();
            let messageDiv = document.getElementById('error_message');
            let sportsSection = document.getElementById('sports_section');

            if (pinNumber && studentName && phno) {
                fetch(`get_student_sports.php?pin_number=${pinNumber}&student_name=${studentName}&phno=${phno}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            messageDiv.innerHTML = data.error;
                            messageDiv.style.display = "block";
                            sportsSection.innerHTML = "";
                            return;
                        }

                        messageDiv.style.display = "none";

                        let sportsCategories = {
                            "Running": ["100mts", "200mts", "400mts", "800mts", "1500mts", "100mts Relay", "400mts Relay"],
                            "Jumps": ["High Jump", "Long Jump", "Triple Jump"],
                            "Throws": ["Javelin Throw", "Discus Throw", "Shot Put"],
                            "Badminton": ["Shuttle Singles", "Shuttle Doubles", "Ball Badminton"],
                            "Indoor Games": ["Table Tennis Singles", "Table Tennis Doubles", "Chess"],
                            "Other Games": ["Volleyball", "Kabaddi"]
                        };

                        let sportsHTML = "<h5 style:>Select a Category</h5>";

                        for (let category in sportsCategories) {
                            let categoryId = category.replace(/\s+/g, "");

                            sportsHTML += `<div class='category-title' onclick="toggleSports('${categoryId}')">
                            <span>${category}</span> 
                            <i id="${categoryId}Icon" class="fa fa-chevron-down"></i>
                        </div>`;

                            sportsHTML += `<div id='${categoryId}' class='sports-list'>`;

                            sportsCategories[category].forEach(sport => {
                                let checked = data.includes(sport) ? "checked" : "";
                                sportsHTML += `<div class='form-check'>
                                <input class='form-check-input' type='checkbox' name='options[]' value='${sport}' ${checked}>
                                <label class='form-check-label'>${sport}</label>
                            </div>`;
                            });

                            sportsHTML += `</div>`; // Close category div
                        }

                        sportsSection.innerHTML = sportsHTML;
                    })
                    .catch(error => console.error('Error fetching sports:', error));
            }
        }

        function toggleSports(categoryId) {
            let allCategories = document.querySelectorAll('.sports-list');
            let allIcons = document.querySelectorAll('.category-title i');

            allCategories.forEach(div => {
                if (div.id !== categoryId) {
                    div.style.display = "none";
                }
            });

            allIcons.forEach(icon => {
                if (icon.id !== categoryId + "Icon") {
                    icon.classList.replace("fa-chevron-up", "fa-chevron-down");
                }
            });

            let categoryDiv = document.getElementById(categoryId);
            let icon = document.getElementById(categoryId + "Icon");

            if (categoryDiv.style.display === "none" || categoryDiv.style.display === "") {
                categoryDiv.style.display = "block";
                icon.classList.replace("fa-chevron-down", "fa-chevron-up");
            } else {
                categoryDiv.style.display = "none";
                icon.classList.replace("fa-chevron-up", "fa-chevron-down");
            }
        }

        document.getElementById('pin_number').addEventListener('blur', fetchStudentSports);
        document.getElementById('student_name').addEventListener('blur', fetchStudentSports);
        document.getElementById('phno').addEventListener('blur', fetchStudentSports);
    </script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
session_start(); // Start the session

if (!isset($_SESSION['admin_username'])) {
    // If session exists, redirect to the dashboard or home page
    header("Location: Admin_login.php");
    exit();
}
require 'SimpleXLSX.php';

$uploadDir = 'winners/';
$message = "";
$messageClass = "";

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $file = $_FILES["file"]["tmp_name"];
    $fileType = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);

    if ($_FILES["file"]["error"] > 0) {
        $message = "Error uploading file!";
        $messageClass = "alert-danger";
    } elseif ($fileType != "xlsx") {
        $message = "Please upload an Excel (.xlsx) file!";
        $messageClass = "alert-warning";
    } else {
        // Ensure winners folder exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Delete old files before uploading new one
        array_map('unlink', glob("$uploadDir/*.xlsx"));

        // Generate unique filename
        $newFileName = $uploadDir . "winners_" . time() . ".xlsx";

        // Move uploaded file to winners folder
        if (move_uploaded_file($file, $newFileName)) {
            file_put_contents("latest_winner_file.txt", $newFileName); // Save latest file reference
            $message = "✅ Winners list uploaded successfully!";
            $messageClass = "alert-success";
        } else {
            $message = "❌ File upload failed! Please try again.";
            $messageClass = "alert-danger";
        }
    }
}

// Handle file deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_file"])) {
    if (file_exists("latest_winner_file.txt")) {
        $latestFile = file_get_contents("latest_winner_file.txt");
        if (file_exists($latestFile)) {
            unlink($latestFile);
        }
        unlink("latest_winner_file.txt");
        $message = "✅ Winners list deleted successfully!";
        $messageClass = "alert-warning";
    } else {
        $message = "❌ No file to delete!";
        $messageClass = "alert-danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="IMG/FAVICON.png" type="image/x-icon">
    <title>Upload Winners List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .upload-container {
            max-width: 500px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        h2 {
            text-align: center;
            color: #336699;
            font-weight: bold;
        }
        .btn-primary, .btn-danger {
            width: 100%;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #004080;
        }
        .alert {
            margin-top: 15px;
            font-weight: bold;
        }
        .btn-view {
            width: 100%;
            font-weight: bold;
            margin-top: 10px;
        }
        .fa-arrow-left {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 20px;
            cursor: pointer;
            border-radius: 50%;
            background-color: rgba(0, 0, 0, 0.4);
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
            z-index: 1000;
        }
        .fa-arrow-left:hover {
            transform: scale(1.2);
        }
    </style>
</head>
<body>
<nav class="fixed-top"> <i class="fa fa-arrow-left" aria-hidden="true"></i></nav>

    <div class="container d-flex justify-content-center">
        <div class="upload-container">
            <h2>🏆 Upload Winners List</h2>
            <form action="" method="post" enctype="multipart/form-data">
                <input type="file" name="file" class="form-control mb-3" accept=".xlsx" required>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
            <form action="" method="post">
                <button type="submit" name="delete_file" class="btn btn-danger mt-2">Delete Records</button>
            </form>
            <!-- Success or Error Message -->
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $messageClass; ?> text-center">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <a href="display.php" class="btn btn-success btn-view">View Winners</a>
        </div>
    </div>
    <script>
          document.querySelector('.fa-arrow-left').addEventListener('click', function (event) {
            event.preventDefault();
            history.back();
        });
    </script>
</body>
</html>




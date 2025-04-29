<?php
session_start(); // Start the session

if (isset($_SESSION['admin_username'])) {
    // If session exists, redirect to the dashboard or home page
    header("Location: admin_dashboard.php");
    exit();
} else {
    // If session is not set, stay on the current page or show login form
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="shortcut icon" href="IMG/FAVICON.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .fa-arrow-left {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 20px;
            cursor: pointer;
            border-radius: 50%;
            background-color: rgba(0, 0, 0, 0.2);
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
        }
    </style>
</head>

<body>
    <header class="d-flex justify-content-around bg p-2 align-items-center">
        <img src="IMG/Logo-James.png" alt="Logo" class="img-fluid logos">
        <center>
            <span class="text-white tw textss" style="font-size: 25px; font-weight: bold;">Dr Y C JAMES YEN GOVT
                POLYTECHNIC, KUPPAM</span>
        </center>
        <img src="IMG/Sbtet-Logo.png" alt="Sbtet-Logo" class="img-fluid logos">
    </header>

    <section class="section-1 d-flex justify-content-center align-items-center container-fluid">
        <nav class="fixed-top"> <i class="fa fa-arrow-left" aria-hidden="true"></i></nav>
        <form id="loginForm" class="p-4 mt-5 mb-5 col-md-3">
            <h3 class="text-center">Admin Login</h3>
            <p id="errorMessage" style="color: red; text-align: center; display: none;"></p>
            <input type="text" name="username" id="username" placeholder="Username" class="mt-2 mb-2" autocomplete="off" required
                autocomplete="username">
            <input type="password" name="password" id="password" placeholder="Password" class="mt-2 mb-2" required
                autocomplete="new-password">

            <button class="btn btn-primary w-100 mt-3" type="submit">Login</button>
        </form>
    </section>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const errorMessage = document.getElementById('errorMessage');

            if (!username || !password) {
                errorMessage.textContent = 'Please fill out all fields.';
                errorMessage.style.display = 'block';
                return;
            }

            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);

            try {
                const response = await fetch('adminlogin.php', {
                    method: 'POST',
                    body: formData
                });

                const text = await response.text();
                console.log("Raw Response:", text);

                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    throw new Error("Server response is not valid JSON");
                }

                if (result.success) {
                    window.location.href = 'admin_dashboard.php';
                } else {
                    errorMessage.textContent = result.message;
                    errorMessage.style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
                errorMessage.textContent = 'An error occurred. Please try again.';
                errorMessage.style.display = 'block';
            }
        });
        // Go back to HOME page
        document.querySelector('.fa-arrow-left').addEventListener('click', function(event) {
            event.preventDefault();
            window.location.href = 'index.html';
        });
    </script>
</body>

</html>
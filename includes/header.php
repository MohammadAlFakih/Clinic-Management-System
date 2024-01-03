<?php
    session_start();
    include($_SERVER['DOCUMENT_ROOT'] . '/Clinic-Management-System/includes/functions.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/Clinic-Management-System/db_utils/DB_Functions.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/header.css">
    <link rel="stylesheet" href="static/css/header.css">
    <title>Header</title>
</head>

<body>
    <div class='header'>
    <div class='home_app'><a class='action' href='http://localhost/Clinic-Management-System/index.php'>Home</a></div>
        <?php
        if (isset($_SESSION["role"])) {
            if ($_SESSION["role"] == "patient") {
                echo "<div class='home_app app'><a class='action' href='http://localhost/Clinic-Management-System/patient/appointments.php'>Appointments</a></div>";
            }
        ?>
            <div class='actions'>
                <a href="http://localhost/Clinic-Management-System/includes/profile.php" class='action'>Profile</a>
                <a href="http://localhost/Clinic-Management-System/logout.php" class='action'>Log Out</a>
            </div>
            <?php
        } else { ?>
            <div class='actions'>
                <a href="http://localhost/Clinic-Management-System/login.php" class='action'>Log In</a>
                <a href="http://localhost/Clinic-Management-System/signup.php" class='action'>Sign Up</a>
            <?php } ?>
            </div>
    </div>
</body>
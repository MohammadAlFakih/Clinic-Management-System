<?php
    session_start();
    include "includes/functions.php";
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
        <?php
        if (isset($_SESSION["role"])) {
            if ($_SESSION["role"] == "patient") {
                echo "<div class='appointment'><a class='action' href='http://localhost/Clinic-Management-System/patient/appointments.php'>Appointments</a></div>";
            }
        ?>
            <div class='actions'>
                <a href="http://localhost/Clinic-Management-System/profile.php" class='action'>Profile</a>
                <a href="http://localhost/Clinic-Management-System/logout.php" class='action'>Log Out</a>
            <?php
        } else { ?>
                <a href="http://localhost/Clinic-Management-System/login.php" class='action'>Log In</a>
                <a href="http://localhost/Clinic-Management-System/signup.php" class='action'>Sign Up</a>
            <?php } ?>
            </div>
    </div>
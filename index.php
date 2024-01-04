<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="static/css/index.css" rel="stylesheet">
    <title>Home</title>
</head>

<body>
    <?php
    include "includes/header.php"; ?>

    <?php
    //<------MAKE APPOINTMENT------>
    if (isset($_SESSION['role'])) {
    ?>
        <div class="contain_make_app">
            <?php
            if(isset($_GET['message'])){
                echo '<div class="message">'.$_GET['message'].'</div>';
            }
            ?>
            <div><a class="make_app" href="http://localhost/Clinic-Management-System/patient/make_appointment2.php">New Appointment</a></div>
            <?php
            if ($_SESSION['role'] == 'doctor') {
                echo '<div><a class="make_app" href="http://localhost/Clinic-Management-System/doctor/manage_schedule.php">Manage Weekly Schedule</a></div>';
            }
            ?>
        </div>
    <?php } ?>
</body>

</html>
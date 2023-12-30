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
            <a class="make_app" href="http://localhost/Clinic-Management-System/patient/make_appointment.php">New Appointment</a>
        </div>
    <?php } ?>
</body>

</html>
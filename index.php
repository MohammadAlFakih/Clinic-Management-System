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
    include "includes/header.php"; 
    ?>
    <?php
    //<------MAKE APPOINTMENT------>
    if (isset($_SESSION['role'])) {
    ?>
        <div class="contain_make_app">
            <?php
            if(isset($_GET['message'])){
                echo '<div class="message">'.$_GET['message'].'</div>';
            }
            if ($_SESSION['role'] == 'patient') {
            echo '<div><a class="make_app" href="http://localhost/Clinic-Management-System/patient/make_appointment2.php">New Appointment</a></div>';
            }
            elseif ($_SESSION['role'] == 'doctor' || $_SESSION['role'] == 'secretary') {
                echo '<div><a class="make_app" href="http://localhost/Clinic-Management-System/secretary/choose_patient.php">New Appointment</a></div>';
                echo '<div><a class="make_app" href="http://localhost/Clinic-Management-System/doctor/manage_schedule.php">Manage Weekly Schedule</a></div>';
            }
            ?>
        </div>
    <?php } 
    if(isset($_SESSION['role']) && $_SESSION['role'] == 'patient'){
        $dbc = connectServer('localhost', 'root', '', 0);
        $db = "clinic_db";
        selectDB($dbc, $db, 0);
        $notifications = get_notifications_unreaded($dbc,$_SESSION['user_id']);
        if($notifications && mysqli_num_rows($notifications)>0){
            echo '<script type="text/javascript">move()</script>';
        }
    }
    ?>
</body>

</html>
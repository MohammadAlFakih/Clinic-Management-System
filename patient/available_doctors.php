<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available_doctors</title>
    <link rel="stylesheet" href="../static/css/available_doctors.css">
</head>

<body style="background-image: url('../static/media/light_blue_bck_img.avif'); background-size: cover; background-repeat: no-repeat;">

</body>

</html>
<?php
include '../includes/header.php';
if (!isset($_SESSION['role'])) {
    header('location:../login.php');
    die();
}
if ($_SERVER['REQUEST_METHOD'] == 'GET' && (!isset($_GET['city']) || !isset($_GET['specialization'])
    || !isset($_GET['date']))) {
    header('location:make_appointment2.php');
    die();
} 

//Display the available doctors in this city on this date with this specialization
else if (!isset($_GET['doctor_id'])) {
    $dbc = connectServer('localhost', 'root', '', 0);
    $db = "clinic_db";
    selectDB($dbc, $db, 0);
    $available_doctors = get_doctors($_GET, $dbc);
    if (count($available_doctors) == 0) {
        echo '<div class="container">
                    <div class="message">
                        <p>Sorry, No Free Appointments are Available for This Date</p>
                        <a class="back-button" href="make_appointment.php">Go Back</a>
                    </div>
                </div>';
    } else {
        if(isset($_GET['message'])){
            echo '<div class="message">' . $_GET['message'] . '</div>';
        }
        echo '<div class="big_title">' . ucfirst($_GET['specialization']) . ' in ' . ucfirst($_GET['city']) . '<br> on <br>' . $_GET['date'] . '</div>';
        echo '<div class="doctor-list">';
        $counter = 0;
        $current_url = "?date=".$_GET['date']."&specialization=".$_GET['specialization']."&city=".$_GET['city'];
        foreach ($available_doctors as $doctor) {
            echo '
            <a class="doctor" href="available_doctors.php' . $current_url . '&doctor_id=' . $doctor['doctor_id'] . '">
                <div class="doctor-info">
                    <h2 class="title">Dc. ' . $doctor['first_name'] . ' ' . $doctor['last_name'] . '</h2>
                    <p class="address">Adress details: ' . $doctor['details'] . '</p>
                    <p class="address">Room number: ' . $doctor['room'] . '</p>
                </div>
                <div class="time-blocks">';

            //Check if doctor make this day off
            $not_available = false;
            if (count($doctor['unavailable_time']) == 1) {
                $unavailable_start_date = new DateTime($doctor['unavailable_time'][0]['start_date']);
                $unavailable_start_hour = $unavailable_start_date->format('H:i:s');
                $unavailable_end_date = new DateTime($doctor['unavailable_time'][0]['end_date']);
                $unavailable_end_hour = $unavailable_end_date->format('H:i:s');
                $not_available = $unavailable_start_hour == $doctor['start_hour'] && $unavailable_end_hour == $doctor['end_hour'];
            }
            if ($doctor['start_hour'] == $doctor['end_hour'] || $not_available) {
                echo '<div class="time-block not_available">Not available on this day</div>';
            }
            
            else {
                echo '<div class="time-block">
                Work hours: ' . substr($doctor['start_hour'], 0, 5) . ' till ' . substr($doctor['end_hour'], 0, 5) . '</div>';
            }
            echo '
                </div>
            </a>';
        }
        echo '</div>';
    }
} 

//Display the schedule of the choosen doctor
else {
    if(isset($_GET['message'])){
        echo '<div class="message">' . $_GET['message'] . '</div>';
    }
    $_SESSION['last_url'] = $_SERVER['REQUEST_URI'];
    $dbc = connectServer('localhost', 'root', '', 0);
    $db = "clinic_db";
    selectDB($dbc, $db, 0);

    //Check if this user make more than 2 appointments on this day with this doctor
    $appointments_nb = count_patient_appointments($_SESSION['patient_id'],$_GET['doctor_id'], $_GET['date'], $dbc);
    if ($appointments_nb > 2) {
       header('location:available_doctors.php?date='.$_GET['date'].'&specialization='.$_GET['specialization'].'&city='
        .$_GET['city']
       .'&message=Sorry, You have already made 2 appointments on this day');
       die();
    }

    $doctor = get_doctor($_GET, $dbc,$_SESSION['role']);
    $_SESSION['doctor_id'] = $doctor['doctor_id'];
    $_SESSION['department_id'] = $doctor['department_id'];
    echo '
            <div class="single">
                <div class="doctor-info sinlge-info">
                    <h2 class="title">Dc. ' . $doctor['first_name'] . ' ' . $doctor['last_name'] . '</h2>
                    <p class="address">' . $_GET['specialization'] . '</p>
                    <p class="address">Adress Details: ' . $doctor['details'] . '</p>
                    <p class="address">Room number: ' . $doctor['room'] . '</p>
            </div></div>';
    
            //<-----------Draw the schedule------------>
            include '../includes/draw_work_hours.php';
            //<-----------Draw the schedule------------>

    echo '
            <form method="POST" action="book_appointment.php">
            <input type="hidden" name="doctor_name" value="'. $doctor["first_name"]." ".$doctor['last_name']. '">
            <input type="hidden" name="specialization" value="'. $_GET["specialization"]. '">
            <input type="hidden" name="city" value="'. $_GET["city"]. '">
            ';

            //Prepare sensitive information to book appointment
            $_SESSION['doctor_id'] = $doctor['doctor_id'];
            $_SESSION['date'] = $_GET['date'];
            $_SESSION['department_id'] = $doctor['department_id'];
            $_SESSION['work_start_hour'] = $doctor['start_hour'];
            $_SESSION['work_end_hour'] = $doctor['end_hour'];


            echo'
            <div class="choose_time">
            <div class="col">
            <div class="row">
            <label class="styled-label" for="startTime">Start Time:</label>
            <input class="styled-input" type="time" name="start_hour" value="08:00">
            </div>
            <div class="row">
            <label class="styled-label" for="endTime">End Time:</label>
            <input class="styled-input" type="time" name="end_hour" value="09:00">
            </div>
            <div class="row">
            <button class="styled-button" type="submit">Submit</button>
            </div>
            </div>';
            if ($_SESSION['role'] == 'patient') {
                echo ' <div class="note"> Once you select your appointment, your application will be marked as pending,
                and you will receive a notification once it is accepted. If you choose a time slot that
                is already booked, you will be placed in our queue system, and there is a possibility
                that you may not secure this appointment.
                </div> ';
            }
            echo '
            </form></div>
        ';
}
?>
<?php
mysqli_close($dbc);
?>
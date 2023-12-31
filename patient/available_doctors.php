<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available_doctors</title>
    <link rel="stylesheet" href="../static/css/available_doctors.css">
</head>
<body>
    
</body>
</html>
<?php
    include '../includes/header.php';
    if(!isset($_SESSION['role'])){
        header('location:../login.php');
        die();
    }
    if($_SERVER['REQUEST_METHOD']=='GET' && (!isset($_GET['city']) || !isset($_GET['specialization'])
     || !isset($_GET['date']))){
        header('location:make_appointment.php');
        die();
    }
    else if(!isset($_GET['index'])){
        $dbc = connectServer('localhost', 'root', '', 1);
        $db = "mhamad";
        selectDB($dbc, $db, 1);
        $available_doctors = get_doctors($_GET,$dbc);
        if(count($available_doctors) == 0){
            echo '<div class="container">
                    <div class="message">
                        <p>Sorry, No Free Appointments are Available for This Date</p>
                        <a class="back-button" href="make_appointment.php">Go Back</a>
                    </div>
                </div>';
        }
        else{
            echo '<div class="big_title">'.ucfirst($_GET['specialization']).' in '.ucfirst($_GET['city']).'<br> on <br>'.$_GET['date'].'</div>';
            echo '<div class="doctor-list">';
            $counter=0;
            foreach ($available_doctors as $doctor) {
            echo'
            <a class="doctor" href="'.$_SERVER['REQUEST_URI'].'&index='.$doctor['id'].'">
                <div class="doctor-info">
                    <h2 class="title">Dc. '.$doctor['first_name'].' '.$doctor['last_name'].'</h2>
                    <p class="address">Adress details: '.$doctor['details'].'</p>
                </div>
                <div class="time-blocks">';
                $margin_of_hours = 0;
                for($j=0;$j<min(strlen($doctor['sequence']),3);$j++){
                    echo '<div class="time-block '.check_status($doctor['sequence'][$j]).'">'.
                    float_to_hour($doctor['start_hour']+$margin_of_hours)."-"
                    .float_to_hour($doctor['start_hour']+$margin_of_hours+0.5).'</div>';
                    $margin_of_hours+=0.5;
                }
                    echo'<div class="dots"> ... </div>
                </div>
            </a>';
            }
            echo'</div>';
        }
    }
    else{
        $dbc = connectServer('localhost', 'root', '', 1);
        $db = "mhamad";
        selectDB($dbc, $db, 1);
        $doctor = get_doctor($_GET,$dbc);
        $start_hour=9;
        echo'
            <div class="single" href="'.$_SERVER['REQUEST_URI'].'&index='.$doctor['id'].'">
                <div class="doctor-info sinlge-info">
                    <h2 class="title">Dc. '.$doctor['first_name'].' '.$doctor['last_name'].'</h2>
                    <p class="address">Adress Details: '.$doctor['details'].'</p>
                </div></div>
                <form method="POST" action="book_appointment.php">
                <input type="hidden" name="start_hour" value="'.$doctor['start_hour'].'"/>
                <input type="hidden" name="doctor_name" value="'.$doctor['first_name']." ".$doctor['last_name'].'"/>
                <input type="hidden" name="date" value="'.$_GET['date'].'"/>
                <div class="slots">
                ';
                $margin_of_hours = 0;
                for($j=0;$j<strlen($doctor['sequence']);$j++){
                    echo '<input id="'.$j.'" type="checkbox" class="check_slot" name="'.$j.'"/>
                    <label for="'.$j.'" class="slot '.check_status($doctor['sequence'][$j]).'">'.
                    float_to_hour($doctor['start_hour']+$margin_of_hours)."-"
                    .float_to_hour($doctor['start_hour']+$margin_of_hours+0.5).'</label>';
                    $margin_of_hours+=0.5;
                }
                echo '</div>
                <div class="submit_container">
                <input type="submit" class="form_button" value="Book This Appointment">
                </div>
                </form>
            ';
    }
?>
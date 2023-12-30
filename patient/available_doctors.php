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
                    <h2 class="title">'.$doctor['first_name'].' '.$doctor['last_name'].'</h2>
                    <p class="address">'.$doctor['details'].'</p>
                </div>
                <div class="time-blocks">
                    <div class="time-block '.check_status($doctor['sequence'],$counter++).'">9:00 AM - 10:00 AM</div>
                    <div class="time-block '.check_status($doctor['sequence'],$counter++).'">10:00 AM - 11:00 AM</div>
                    <div class="time-block '.check_status($doctor['sequence'],$counter++).'">11:00 AM - 12:00 PM</div>
                    <div class="dots"> ... </div>
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
            <div class="doctor" href="'.$_SERVER['REQUEST_URI'].'&index='.$doctor['id'].'">
                <div class="doctor-info">
                    <h2 class="title">'.$doctor['first_name'].' '.$doctor['last_name'].'</h2>
                    <p class="address">'.$doctor['details'].'</p>
                </div>
                <div class="time-blocks">';
                for($i=0;$i<strlen($doctor['sequence']);$i++){
                    echo '<div class="time-block '.check_status($doctor['sequence'],$i).'">'.
                    $start_hour++.' :00 - '.$start_hour.':00 </div>';
                }
                echo '</div>
            </div>';
    }
?>
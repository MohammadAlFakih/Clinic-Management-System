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
            <a class="doctor" href="'.$_SERVER['REQUEST_URI'].'&index='.$doctor['doctor_id'].'">
                <div class="doctor-info">
                    <h2 class="title">Dc. '.$doctor['first_name'].' '.$doctor['last_name'].'</h2>
                    <p class="address">Adress details: '.$doctor['details'].'</p>
                    <p class="address">Room number: '.$doctor['room'].'</p>
                </div>
                <div class="time-blocks">';
                
                $not_available = false;
                //Check if doctor make this day off
                if(count($doctor['unavailable_time'])==1){
                    $unavailable_start_date = new DateTime($doctor['unavailable_time'][0]['start_date']);
                    $unavailable_start_hour = $unavailable_start_date->format('H:i:s');
                    $unavailable_end_date = new DateTime($doctor['unavailable_time'][0]['end_date']);
                    $unavailable_end_hour = $unavailable_end_date->format('H:i:s');
                    $not_available = $unavailable_start_hour==$doctor['start_hour'] && $unavailable_end_hour==$doctor['end_hour'];
                }

                if($doctor['start_hour']==$doctor['end_hour'] || $not_available){
                    echo '<div class="time-block not_available">Not available on this day</div>';
                }
                else{
                echo'<div class="time-block">
                Work hours: '.substr($doctor['start_hour'],0,5).' till '.substr($doctor['end_hour'],0,5).'</div>';
                }
                echo '
                </div>
            </a>';
            }
            echo'</div>';
        }
    }
    else{
        $_SESSION['last_url'] = $_SERVER['REQUEST_URI'];
        $dbc = connectServer('localhost', 'root', '', 1);
        $db = "mhamad";
        selectDB($dbc, $db, 1);
        $doctor = get_doctor($_GET,$dbc);
        $_SESSION['doctor_id']= $doctor['doctor_id'];
        //$_SESSION['date']=$_GET['date'];
        $_SESSION['department_id']=$doctor['department_id'];
        $start_hour=9;
        echo'
            <div class="single" href="'.$_SERVER['REQUEST_URI'].'&index='.$doctor['doctor_id'].'">
                <div class="doctor-info sinlge-info">
                    <h2 class="title">Dc. '.$doctor['first_name'].' '.$doctor['last_name'].'</h2>
                    <p class="address">'.$_GET['specialization'].'</p>
                    <p class="address">Adress Details: '.$doctor['details'].'</p>
                    <p class="address">Room number: '.$doctor['room'].'</p>
                </div></div>';
    }
?>

<?php 
mysqli_close($dbc);
?>
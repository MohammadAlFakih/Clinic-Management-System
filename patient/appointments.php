<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/appointments.css">
    <title>Appointments</title>
</head>
<?php
    include '../includes/header.php';
    if(!isset($_SESSION['role']))
        header("location:../login.php");
    
    if($_SESSION['role']=='patient'){
        $dbc = connectServer('localhost','root','',1);
        selectDB($dbc,'mhamad',1);
        $appointments = get_appointments($dbc,$_SESSION['patient_id']);
        //No appointments
        if($appointments->num_rows == 0){
            echo '<div class="container">
            <div class="message">
                <p>You have no appointments 📅.</p>
            </div>
                <a class="make_app" href="http://localhost/Clinic-Management-System/patient/make_appointment.php">
                New Appointment
                </a>
            </div>';
        }
        //Display the appointments
        else{
            while($appointment = $appointments->fetch_assoc()){
                foreach($appointment as $key=>$value){
                    echo $key." : ".$value;
                }
                echo '<br>';
            }
        }
    }
?>
<?php 
mysqli_close($dbc);
?>
</html>
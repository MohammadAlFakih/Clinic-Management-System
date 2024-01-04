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
                <a class="make_app" href="http://localhost/Clinic-Management-System/patient/make_appointment2.php">
                New Appointment
                </a>
            </div>';
        }
        //Display the appointments
        else{
            echo ' <div class="container"><h1>Appointments List</h1>

        <table>
            <thead>
                <tr>
                    <th>Doctor Name</th>
                    <th class="hide">Specialization</th>
                    <th>Address</th>
                    <th class="hide">Room Number</th>
                    <th>Start Date</th>
                    <th>Duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';
            while($appointment = $appointments->fetch_assoc()){

                //Calculate Duration
                $display_duration = duration($appointment['start_date'],$appointment['end_date']);

                $appointment['start_date'] = new DateTime($appointment['start_date']);
                $appointment['start_date'] = $appointment['start_date']->format('Y-m-d H:i');

                echo '<tr class="even" onclick="window.location=`../includes/appointment.php?app_id='.$appointment['id'].'`">
                <td>Dc. '.$appointment['first_name']." ".$appointment['last_name'].'</td>
                <td class="hide">'.$appointment['alias'].'</td>
                <td>'.$appointment['city_name'].'</td>
                <td class="hide">'.$appointment['room'].'</td>
                <td>'.$appointment['start_date'].'</td>
                <td>'.$display_duration.'</td>
                <td>'.$appointment['status'].'</td>
                </tr>';
            }
            echo '</tbody>
        </table></div>';
        }
        
    }
?>
<?php 
mysqli_close($dbc);
?>
</html>
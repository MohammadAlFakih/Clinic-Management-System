<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/appointments.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Appointments</title>
</head>
        

<?php
    include '../includes/header.php';
    if(!isset($_SESSION['role']))
        header("location:../login.php");

    if($_SESSION['role'] != 'doctor') {
        header("location:../index.php");
        die();
    }
?>
    <div class="container">
        <h1>Appointments List</h1>
        <form method="post" action="appointments.php">
        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="any" <?php echo ($_SERVER['REQUEST_METHOD']=='POST' && $_POST['status'] === 'any') ? 'selected' : ''; ?>>Any</option>
            <option value="upcoming"<?php echo ($_SERVER['REQUEST_METHOD']=='POST' && $_POST['status'] === 'upcoming') ? 'selected' : ''; ?>>Upcoming</option>
            <option value="done"<?php echo ($_SERVER['REQUEST_METHOD']=='POST' && $_POST['status'] === 'done') ? 'selected' : ''; ?>>Done</option>
        </select>
        <button type="submit" class="filter-icon">
            <i class="fas fa-filter"></i>
        </button>
    </div>

<?php

        $dbc = connectServer('localhost','root','',1);
        selectDB($dbc,'mhamad',1);

        $query = "SELECT appointment.*, patient.first_name, patient.last_name, patient.age, patient.gender,patient.phone FROM appointment
                JOIN patient ON appointment.patient_id = patient.id
                WHERE doctor_id = ? AND status != 'pending' AND status != 'queued'";

        if (!isset($_POST['status']) || ($_POST['status']) == 'any') {
            $stmt = $dbc->prepare($query);
            $stmt->bind_param("i",$_SESSION['doctor_id']);
        }
        else {
            $chosen_status = $_POST['status'];
            $query .= " AND status = ? ";
            $stmt = $dbc->prepare($query);
            $stmt->bind_param("is",$_SESSION['doctor_id'],$chosen_status);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        
        //No appointments
        if($result->num_rows == 0){
            echo '
            <div class="message">
                <p>There are no appointments 📅.</p>
            </div>
                <a class="make_app" href="http://localhost/Clinic-Management-System/patient/make_appointment2.php">
                New Appointment
                </a>
            </div>';
        }
        //Display the appointments
        else{
            echo '
        <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th class="hide">Age</th>
                    <th>Gender</th>
                    <th class="hide">Phone</th>
                    <th>Start Date</th>
                    <th>Duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';
            while($appointment = $result->fetch_assoc()){

                //Calculate Duration
                $display_duration = duration($appointment['start_date'],$appointment['end_date']);

                $appointment['start_date'] = new DateTime($appointment['start_date']);
                $appointment['start_date'] = $appointment['start_date']->format('Y-m-d H:i');

                echo '<tr class="even" onclick="window.location=`../includes/appointment.php?app_id='.$appointment['id'].'`">
                <td>'.$appointment['first_name']." ".$appointment['last_name'].'</td>
                <td class="hide">'.$appointment['age'].'</td>
                <td>'.$appointment['gender'].'</td>
                <td class="hide">'.$appointment['phone'].'</td>
                <td>'.$appointment['start_date'].'</td>
                <td>'.$display_duration.'</td>
                <td>'.$appointment['status'].'</td>
                </tr>';
            }
            echo '</tbody>
        </table></div>';
        }
?>
<?php 
mysqli_close($dbc);
?>
</html>
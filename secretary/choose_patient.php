<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Patient</title>
    <link rel="stylesheet" href="../static/css/choose_patient.css">
</head>

<?php

    include '../includes/header.php';

    if (!isset($_SESSION['role'])) {
        header('location:../login.php');
        die();
    }

    if ($_SESSION['role'] != 'secretary' && $_SESSION['role'] != 'doctor') {
        header('location:../index.php');
        die();
    }


    // I wanna add the message class
    if(isset($_GET['message'])){
        echo '<div class="message">' . $_GET['message'] . '</div>';
    }

    $dbc = connectServer('localhost', 'root', '', 0);
    $db = "clinic_db";
    selectDB($dbc, $db, 0);

    // foreach ($_SESSION as $key => $value) {
    //     echo $key . " : " . $value . "<br>" ;
    // }

    
?>

<body style="background-image: url('../static/media/light_blue_bck_img.avif'); background-size: cover; background-repeat: no-repeat;">
<div class="container">
<form method="post" class="login-form">

    <label class="form-label" for="patient_email">Patient Email:</label>
    <input type="email" class="form-input" name="patient_email" id="patient_email" placeholder="Enter patient email">

    <label class="form-label" for="date" class="form-label">Choose Appointment Date:</label>
    <input type="date" class="form-input" id="appointmentDate" name="date" class="form-input">
    <br><br>

    <input class="form-button" type="submit" value="Search" name='search' class="form-button">

    <label class="form-label error">
                <?php
                $patients = get_patients($dbc);

                if (isset($_POST['search'])) {
                    if (!empty($_POST['date']) && isset($_POST['patient_email'])) {
                
                        $patientEmail = $_POST['patient_email'];
                
                        date_default_timezone_set('Asia/Beirut');
                        $current_date = date('Y-m-d');
                        $filtered_date = new DateTime($_POST['date']);
                        $filtered_date = $filtered_date->format('Y-m-d');
                
                        if (in_array($patientEmail, $patients)) {
                            $_SESSION['patient_id'] = get_patient_id_from_email($dbc, $patientEmail);
                            // Get the date and check its conditions
                            if ($filtered_date > $current_date) {
                                $current_doctor = get_doctor_info($dbc, $_SESSION['doctor_id']);
                                header('location:../patient/available_doctors.php?date='.$_POST['date'].'&specialization='.$current_doctor['specialization'].
                                        '&city='.$current_doctor['city'] . '&doctor_id=' . $_SESSION['doctor_id'] );
                            }
                            else {
                                echo "Date is invalid!";
                                die();
                            }
                        } else {
                            echo "Email not found !";
                            die();
                        }
                    }
                
                    else {
                        echo "Please enter both email and specific date!";
                        die();
                    }
                }
                ?>
            </label>

</form>
</div>


</body>
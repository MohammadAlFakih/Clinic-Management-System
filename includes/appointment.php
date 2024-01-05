<link rel="stylesheet" href="../static/css/appointment.css" type="text/css">
<?php
include '../includes/header.php';
if(!isset($_SESSION['role'])){
    header("location:../login.php");
    die();
}
$dbc = connectServer('localhost','root','',1);
selectDB($dbc,'mhamad',1);

//Check if this appointment is for the requesting patient
$valid = check_app_for_patient($dbc,$_SESSION['patient_id'],$_GET['app_id']);
if(!$valid){
    header("location:../patient/appointments.php");
    die();
}

$appointment = get_appointment($dbc,$_GET['app_id']);
echo '
<div class="appointment-container">
    <div class="info-container">
    <div class="row">
    <div class="col">
    <h1>My Appointment<span class="status">'.$appointment['status'].'</span>';
    if($appointment['status'] == 'pending'){
        echo '<a href="../includes/cancel_appointment.php?app_id='.$_GET['app_id'].'&patient_id='.$_SESSION['patient_id'].'" class="cancel">Cancel</a>';
    }
    else if($appointment['status'] == 'delayed'){
        echo '<a href="../includes/cancel_appointment.php?app_id='.$_GET['app_id'].'&patient_id='.$_SESSION['patient_id'].'" class="cancel remove">Remove</a>';
    }
    echo'</h1>
    <span class="note">'.$appointment['details'].'</span>
    </div>
    </div>
    <div class="row">
        <div class="info-item">
            <div class="info-label">Doctor Name 👨‍⚕️:</div>
            <div class="info-value">'.$appointment['first_name']." ".$appointment['last_name'].' ('.$appointment['alias'].')</div>
        </div>

        <div class="info-item">
            <div class="info-label">Patient Name 👨‍💼:</div>
            <div class="info-value">'.$_SESSION['patient_name'].'</div>
        </div></div>

        <div class="row pers">
        <div class="info-item">
            <div class="info-label prescription">Details ⚕️:</div>
            <div class="info-value">
            '.$appointment['document'].'</div>
        </div>
        </div>

        <div class="row">
        <div class="info-item">
            <div class="info-label">Date 📅:</div>
            <div class="info-value">'.(new DateTime($appointment['start_date']))->format('Y-m-d').'</div>
        </div>

        <div class="info-item">
            <div class="info-label">Start Hour 🕒:</div>
            <div class="info-value">'.(new DateTime($appointment['start_date']))->format('H:i').'</div>
        </div>

        <div class="info-item">
            <div class="info-label">Duration:</div>
            <div class="info-value">'.duration($appointment['start_date'],$appointment['end_date']).'</div>
        </div>
        </div>
        
        <div class="row">
        <div class="info-item">
            <div class="info-label">Address 📌 :</div>
            <div class="info-value">'.$appointment['city_name'].', Room '.$appointment['room'].'</div>
        </div>

        <div class="info-item bill">
            <div class="info-label">Bill:</div>
            <div class="info-value"><span class="highlight">$'.$appointment['bill'].'</span></div>
        </div>
        </div>
        <p class="note">* Please arrive 15 minutes before the appointment.</p>
    </div>
    </div>';
    echo '<div class="contact_container">
            <img src="../static/media/secretary_default.jpg" alt="LOAD IMAGE" />
            <p class="contact_info">
            Your health is our priority, and we understand that circumstances may change.
            If, for any reason, you need to edit or remove this appointment,
            please feel free to reach out to our dedicated secretary<br><br><span class="secretary_info">
            '.$appointment['sec_fname']." ".$appointment['sec_lname'].'<br>
            '.$appointment['sec_phone'].'</span><br><br>
            They will be more than happy to assist you and ensure that your healthcare needs are met seamlessly.
            Your cooperation is greatly appreciated, and we look forward to serving you.
            </p>
            <div>';
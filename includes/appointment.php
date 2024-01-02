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
$appoinment = get_appointment($dbc,$_GET['app_id']);
echo '
<div class="appointment-container">
    <div class="info-container">
    <div class="row">
    <div class="col">
    <h1>My Appointment</h1>
    <span class="note">'.$appoinment['details'].'</span>
    </div>
    </div>
    <div class="row">
        <div class="info-item">
            <div class="info-label">Doctor Name 👨‍⚕️:</div>
            <div class="info-value">'.$appoinment['first_name']." ".$appoinment['last_name'].' ('.$appoinment['alias'].')</div>
        </div>

        <div class="info-item">
            <div class="info-label">Patient Name 👨‍💼:</div>
            <div class="info-value">'.$_SESSION['patient_name'].'</div>
        </div></div>

        <div class="row pers">
        <div class="info-item">
            <div class="info-label prescription">Details ⚕️:</div>
            <div class="info-value">
            Routine Checkup Take medication A, 1 tablet daily after meals. Follow up in one week.
            Routine Checkup Take medication A, 1 tablet daily after meals. Follow up in one week.
            Routine Checkup Take medication A, 1 tablet daily after meals. Follow up in one week.</div>
        </div>
        </div>

        <div class="row">
        <div class="info-item">
            <div class="info-label">Date 📅:</div>
            <div class="info-value">'.(new DateTime($appoinment['start_date']))->format('Y-m-d').'</div>
        </div>

        <div class="info-item">
            <div class="info-label">Start Hour 🕒:</div>
            <div class="info-value">'.(new DateTime($appoinment['start_date']))->format('H:i').'</div>
        </div>

        <div class="info-item">
            <div class="info-label">Duration:</div>
            <div class="info-value">'.duration($appoinment['start_date'],$appoinment['end_date']).'</div>
        </div>
        </div>
        
        <div class="row">
        <div class="info-item">
            <div class="info-label">Address 📌 :</div>
            <div class="info-value">'.$appoinment['city_name'].', Room '.$appoinment['room'].'</div>
        </div>

        <div class="info-item bill">
            <div class="info-label">Bill:</div>
            <div class="info-value"><span class="highlight">$'.$appoinment['bill'].'</span></div>
        </div>
        </div>
        <p class="note">* Please arrive 15 minutes before the appointment.</p>
    </div>
    </div><br>';

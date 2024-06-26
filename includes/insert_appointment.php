<?php
session_start();
include "../db_utils/DB_Functions.php";
include "../includes/functions.php";
if(!isset($_SESSION['role'])){
    header("Location:../login.php");
    die();
}
if(!isset($_SESSION['date'])
|| !isset($_SESSION['doctor_id']) || !isset($_SESSION['patient_id'])
|| !isset($_SESSION['department_id'])){
    header("Location:".$_SESSION['last_url']);
    die();
}

if ($_SESSION['role'] == 'patient') {
    $app_status = 'pending';
}
elseif (in_array($_SESSION['role'], array('doctor', 'secretary'))) {
    $app_status = 'upcoming';
}

$dbc = connectServer('localhost', 'root', '', 0);
selectDB($dbc,"clinic_db",1);


//Check if there exists a booked appointment that overlaps with the entered appoitnment => status = queued
$query = "SELECT id
            FROM appointment
            WHERE doctor_id = ? AND status != 'pending' AND status!= 'queued'
            AND (( ? >= start_date AND ? < end_date) OR ( ? > start_date AND ? <= end_date)
            OR ( ? < start_date AND ? > end_date) OR (?=start_date AND ? = end_date))";
$stmt = $dbc->prepare($query);
$stmt->bind_param("issssssss",$_SESSION['doctor_id'],$_SESSION['start_date'],$_SESSION['start_date'],$_SESSION['end_date'],
$_SESSION['end_date'],$_SESSION['start_date'],$_SESSION['end_date'],$_SESSION['start_date'],$_SESSION['end_date']);
$stmt->execute();
$result = $stmt->get_result();
$new_status = $app_status;
// Shouf mhmd shou baddo y2elle eza doctor 3am ye7joz app w sar fi overlap ma3 upcoming app tene
if($result && mysqli_num_rows($result)>0){
    $new_status = "queued";
}

$query = "INSERT INTO appointment (department_id,doctor_id,patient_id,start_date,end_date,status)
            VALUES (?,?,?,?,?,?)";
$stmt = $dbc->prepare($query);
$stmt->bind_param("iiisss",$_SESSION['department_id'],$_SESSION['doctor_id'],$_SESSION['patient_id'],
                            $_SESSION['start_date'],$_SESSION['end_date'],$new_status);
$stmt->execute();
$stmt->close();
$dbc->close();

if ($_SESSION['role'] == 'patient') {
    unset($_SESSION['date']);
    unset($_SESSION['doctor_id']);
    unset($_SESSION['last_url']);
    unset($_SESSION['department_id']);
    unset($_SESSION['start_date']);
    unset($_SESSION['end_date']);
    unset($_SESSION['work_start_hour']);
    unset($_SESSION['work_end_hour']);
    header("Location:../index.php?message=Your appointment request is currently pending, and you will be notified once it is accepted ✅.");
}

else {
    unset($_SESSION['date']);
    unset($_SESSION['last_url']);
    unset($_SESSION['department_id']);
    unset($_SESSION['start_date']);
    unset($_SESSION['end_date']);
    unset($_SESSION['patient_id']);
    unset($_SESSION['patient_name']);
    unset($_SESSION['work_start_hour']);
    unset($_SESSION['work_end_hour']);
    // We have to notify the patient
    header("Location:../index.php?message=The appointment you entered has been successfully registered. The patient will be notified ✅.");

}
<?php
session_start();
include "../db_utils/DB_Functions.php";
include "./functions.php";
$dbc = connectServer('localhost','root','',1);
selectDB($dbc,'mhamad',1);
if(!isset($_SESSION['role'])){
    header('location:../login.php');
    die();
}
if(!isset($_GET['app_id'])){
    header('location:../index.php');
    die();
}
if($_SESSION['role']=='patient') {
    $error = false;

    $app_id = $_GET['app_id'];

    //Friend code
    if(!is_numeric($app_id)) $error = true;
    if($error){
        //header location
    }

    $query = "SELECT status FROM appointment 
            WHERE id =? ";

    $stmt = $dbc->prepare($query);
    $stmt->bind_param("i",$app_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row =$result -> fetch_assoc();

    //Check if this appointment is not for this patient
    if((!check_app_for_patient($dbc,$_SESSION['patient_id'],$app_id)) || ($row['status'] != 'pending')) {
        $stmt->close();
        mysqli_close($dbc);
        header('location:../patient/appointments.php');
        die();
    }
}

// For the doctor
elseif ($_SESSION['role'] == 'doctor') {
    if(!check_app_for_doctor($dbc,$_SESSION['doctor_id'],$app_id)) {
        $stmt->close();
        mysqli_close($dbc);
        header('location:../doctor/appointments.php');
        die();
    }
}

$query = 'DELETE FROM appointment WHERE id = ? ';
$stmt = $dbc->prepare($query);
$stmt->bind_param("i",$_GET['app_id']);
$stmt->execute();

$stmt->close();
mysqli_close($dbc);
header('location:../'.$_SESSION['role'].'/appointments.php');
?>
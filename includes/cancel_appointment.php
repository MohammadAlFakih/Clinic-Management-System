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
    mysqli_close($dbc);
        header('location:../index.php');
        die();
}

if($_SESSION['role'] != 'patient' && !isset($_GET['patient_id'])){
    mysqli_close($dbc);
    header('location:../patient/index.php');
    die();
}

$query = "SELECT status FROM appointment 
            WHERE id =? AND patient_id = ?";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("ii",$_GET['app_id'],$_GET['patient_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    //Check if this appointment is not for this patient
    if($result && mysqli_num_rows($result)==0){
        header('location:../patient/appointments.php');
        $stmt->close();
        mysqli_close($dbc);
        die();
    }
    $row =$result -> fetch_assoc();

    //Check if the status is not pending
    if($row['status'] != 'pending' && $row['status'] != 'delayed' && $row['status']!='queued' && $_SESSION['role']=='patient'){
        $stmt->close();
        mysqli_close($dbc);
        header('location:../patient/appointments.php');
        die();
    }

    //Notifie the patient that his appointment has been removed
    if($_SESSION['role'] != 'patient'){
        $query = 'INSERT INTO notifications (sender,receiver,reason) VALUES
        (?,?,"remove")';
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("ii",$_SESSION['doctor_id'],$_GET['patient_id']);
        $stmt->execute();
    }


$query = 'DELETE FROM appointment WHERE id = ? ';
$stmt = $dbc->prepare($query);
$stmt->bind_param("i",$_GET['app_id']);
$stmt->execute();

$stmt->close();
mysqli_close($dbc);
if($_SESSION['role'] == 'patient')
    header('location:../patient/appointments.php');
else if($_SESSION['role'] == 'secretary')
    header('location:../secretary/requests.php');
else{
    header('location:../doctor/appointments.php');
}
?>

    
<?php
session_start();
include "../db_utils/DB_Functions.php";
include "./functions.php";
$dbc = connectServer('localhost','root','',0);
selectDB($dbc,'clinic_db',0);
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

$query = "SELECT status,start_date,end_date,id FROM appointment 
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

//No need to check overlapped appointment
if($row['status'] == 'pending' || $row['status'] == 'queued'){
    $query = 'DELETE FROM appointment WHERE id = ? ';
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("i",$_GET['app_id']);
    $stmt->execute();
}
else{
    //Get all the appointments that overlap with the new unavailabile slot
    $sql = " SELECT *
    FROM appointment
    WHERE doctor_id = ? AND status='queued' AND
            (( ? >= start_date AND ? < end_date) OR ( ? > start_date AND ? <= end_date)
            OR ( ? < start_date AND ? > end_date) OR (? >= start_date AND ? <= end_date))";
    $stmt = $dbc->prepare($sql);
    $stmt->bind_param("issssssss", $_SESSION['doctor_id'], $row['start_date'],$row['start_date'],$row['end_date'],
    $row['end_date'],$row['start_date'],$row['end_date'],$row['start_date'],$row['end_date']);
    $stmt->execute();
    $result = $stmt->get_result();

    //Remove the appointment
    $query = 'DELETE FROM appointment WHERE id = ? ';
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("i",$_GET['app_id']);
    $stmt->execute();

    //Iterate through this appointment and check if there is no other accepted appointment overlap with them
    //then update them to pending again
    while($app = $result->fetch_assoc()){
        if(!overlap_with_accepted($dbc,$app['start_date'],$app['end_date'],$_SESSION['doctor_id'])){
            $query = 'UPDATE appointment set status="pending" WHERE id = ? ';
            $stmt = $dbc->prepare($query);
            $stmt->bind_param("i",$app['id']);
            $stmt->execute();
        }
    }
}
$stmt->close();
mysqli_close($dbc);
if($_SESSION['role'] == 'patient')
    header('location:../patient/appointments.php');
else{
    header('location:../secretary/requests.php');
}
?>

    
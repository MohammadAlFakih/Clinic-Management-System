<?php
include "../includes/header.php";

if(!isset($_SESSION['role'])){
    header('location:../login.php');
    die();
}

if($_SESSION['role'] == 'patient' || !isset($_GET['app_id']) || !isset($_SESSION['doctor_id'])){
    header('location:../index.php');
    die();
}

$app_id = $_GET['app_id'];
$today = date('Y-m-d');

$dbc = connectServer('localhost','root','',0);
selectDB($dbc,'mhamad',0);

$query = "SELECT app.status,app.doctor_id,app.start_date,app.end_date,app.patient_id
            FROM appointment app
            WHERE app.id = ? AND DATE(app.start_date)>= ?";
$stmt = $dbc->prepare($query);
$stmt->bind_param('is',$app_id,$today);
$stmt->execute();
$result = $stmt->get_result();

if(mysqli_num_rows($result) == 0){
    header('location:../index.php');
    mysqli_close($dbc);
    die();
}
else{
    $app = $result->fetch_assoc();
    if($app['doctor_id'] != $_SESSION['doctor_id'] || $app['status']!='pending'){
        header('location:../index.php');
        mysqli_close($dbc);
        $stmt->close();
        die();
    }
    else{
        //Update the appointment to upcoming
        $query = "UPDATE appointment SET status = 'upcoming' WHERE id = ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param('i',$app_id);
        $stmt->execute();

        //Add accept notification for the new appointment
        $query = "INSERT INTO notifications (appointment_id,sender,receiver,reason)
        VALUES (?,?,?,'accepted')";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param('sii',$app_id,$_SESSION['doctor_id'],$app['patient_id']);
        $stmt->execute();

        //Make all the appointment that overlap with the current appointment queued
        $query = "UPDATE appointment SET status = 'queued'
         WHERE start_date < ? AND end_date > ? AND id!=?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param('ssi',$app['end_date'],$app['start_date'],$_GET['app_id']);
        $stmt->execute();
    }
}
$stmt->close();
mysqli_close($dbc);
header('location:requests.php');
?>
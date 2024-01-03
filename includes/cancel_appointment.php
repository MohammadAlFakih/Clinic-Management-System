<?php
session_start();
include "../db_utils/DB_Functions.php";
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
if($_SESSION['role']=='patient'){
    $error = false;

    $app_id = $_GET['app_id'];

    //Friend code
    if(!is_numeric($app_id)) $error = true;
    if($error){
        //header location
    }

    

    $query = "SELECT status FROM appointment 
            WHERE id =? AND patient_id = ?";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("ii",$_GET['app_id'],$_SESSION['patient_id']);
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
    if($row['status'] != 'pending'){
        $stmt->close();
        mysqli_close($dbc);
        header('location:../patient/appointments.php');
        die();
    }
}
$query = 'DELETE FROM appointment WHERE id = ? ';
$stmt = $dbc->prepare($query);
$stmt->bind_param("i",$_GET['app_id']);
$stmt->execute();

$stmt->close();
mysqli_close($dbc);
header('location:../patient/appointments.php');
?>
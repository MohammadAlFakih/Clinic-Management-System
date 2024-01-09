<?php 
include "../includes/header.php";
if(!isset($_SESSION['role'])){
    header("location:../login.php");
    die();
}
if($_SESSION['role']=='patient' || $_SERVER['REQUEST_METHOD']=="GET"){
    header("location:../index.php");
    die();
}
if(empty($_POST['new_start_date']) || empty($_POST['new_start_hour']) || empty($_POST['new_end_hour'])){
    header("location:requests.php");
    die();
}

$new_start_date = $_POST['new_start_date'];
$new_start_hour = $_POST['new_start_hour'];
$new_end_hour = $_POST['new_end_hour'];

$new_end_date = $new_start_date." ".$new_end_hour;
$new_start_date .= " ".$new_start_hour;

if($new_end_date <= $new_start_date){
    header("location:requests.php");
    die();
}

$dbc = connectServer('localhost','root','',1);
selectDB($dbc,'mhamad',1);

//Import the schedule on the given date
$day_of_week = strtolower(date("l",strtotime($new_start_date)));
$query = "SELECT *
            FROM week_schedule
            WHERE day = ?";
$stmt = $dbc->prepare($query);
$stmt->bind_param("s",$day_of_week);
$stmt->execute();
$result = $stmt->get_result();
if(!$result || mysqli_num_rows($result)==0){
    mysqli_close($dbc);
    header("location:requests.php?message='Error in db'");
    die();
}
$schedule = $result->fetch_assoc();
//Check if the new date is outside the shcedule
if(time_to_float($schedule['start_hour'])>time_to_float($new_start_hour)
 || time_to_float($schedule['end_hour'])<time_to_float($new_end_hour)){
    mysqli_close($dbc);
    $stmt->close();
    header("location:requests.php?message=The choosen appointment is outside the schedule of doctor");
    die();
}

//Check if the new date overlap with some unavailable hours 
$query = "SELECT *
            FROM unavailable_slots
            WHERE doctor_id=".$_SESSION['doctor_id']." AND 
            ( ? >= start_date AND ? < end_date) OR ( ? > start_date AND ? <= end_date)
            OR ( ? < start_date AND ? > end_date) OR (?=start_date AND ? = end_date)";
$stmt = $dbc->prepare($query);
$stmt->bind_param("ssssssss",$new_start_date,$new_start_date,$new_end_date,
$new_end_date,$new_start_date,$new_end_date,$new_start_date,$new_end_date);
$stmt->execute();
$result = $stmt->get_result();
if($result && mysqli_num_rows($result)>0){
    mysqli_close($dbc);
    $stmt->close();
    header("location:requests.php?message=The choosen appointment overlap with some unavailable hours");
    die();
}

//Check if no upcoming appointments overlap with this appointment then put it as pending
$query = "SELECT id
            FROM appointment
            WHERE doctor_id = ? AND status != 'pending' AND status!= 'queued'
            AND (( ? >= start_date AND ? < end_date) OR ( ? > start_date AND ? <= end_date)
            OR ( ? < start_date AND ? > end_date) OR (?=start_date AND ? = end_date))";
$stmt = $dbc->prepare($query);
$stmt->bind_param("issssssss",$_SESSION['doctor_id'],$new_start_date,$new_start_date,$new_end_date,
$new_end_date,$new_start_date,$new_end_date,$new_start_date,$new_end_date);
$stmt->execute();
$result = $stmt->get_result();

//Update but let status = queued
$message="";
if($result && mysqli_num_rows($result)>0){
    $query = "UPDATE appointment SET start_date=?,end_date=?
                WHERE id=?";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("ssi",$new_start_date,$new_end_date,$_POST['app_id']);
    $stmt->execute();
    $message="The appointment is updated but still queued";
}
//Update with the new status = pending
else{
    $query = "UPDATE appointment SET start_date=?,end_date=?,status='pending'
                WHERE id=?";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("ssi",$new_start_date,$new_end_date,$_POST['app_id']);
    $stmt->execute();
    $message = "The appointment is updated and ready to be accepted";
}

$stmt->close();
mysqli_close($dbc);
header("location:requests.php?message=".$message);
?>
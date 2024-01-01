<?php
session_start();
include "../db_utils/DB_Functions.php";
if(!isset($_SESSION['role'])){
    header("Location:../login.php");
    die();
}
if(!isset($_SESSION['date_time']) || !isset($_SESSION['duration']) 
|| !isset($_SESSION['doctor_id']) || !isset($_SESSION['patient_id'])
|| !isset($_SESSION['department_id'])){
    echo "Not Found";//header("Location:".$_SESSION['last_url']);
    die();
}

$dbc = connectServer('localhost', 'root', '', 1);
selectDB($dbc,"mhamad",1);

$query = "INSERT INTO appointment (department_id,doctor_id,patient_id,date,duration)
            VALUES (?,?,?,?,?)";
$stmt = $dbc->prepare($query);
$stmt->bind_param("iiisd",$_SESSION['department_id'],$_SESSION['doctor_id'],$_SESSION['patient_id'],
                            $_SESSION['date_time'],$_SESSION['duration']);
$stmt->execute();
$stmt->close();
$dbc->close();
unset($_SESSION['department_id']);
unset($_SESSION['doctor_id']);
unset($_SESSION['date_time']);
unset($_SESSION['duration']);
header("Location:../index.php?message=Your appointment request is currently pending, and you will be notified once it is accepted ✅.");
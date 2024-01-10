<?php
session_start();
include "../db_utils/DB_Functions.php";
include "../includes/functions.php";
if(!isset($_SESSION['role'])){
    header("Location:../login.php");
    die();
}

// foreach ($_SESSION as $key => $value) {
//     echo $key . " : " . $value . "<br>";
// }


$dbc = connectServer('localhost', 'root', '', 1);
selectDB($dbc,"mhamad",1);

$query = "INSERT INTO unavailable_slots (doctor_id,start_date,end_date,department_id)
            VALUES (?,?,?,?) ";

$stmt = $dbc->prepare($query);
$stmt->bind_param("issi",$_SESSION['doctor_id'],$_SESSION['start_date'],$_SESSION['end_date'], $_SESSION['department_id']);
$stmt->execute();
$stmt->close();
$dbc->close();

unset($_SESSION['department_id']);
unset($_SESSION['start_date']);
unset($_SESSION['end_date']);
header("Location:".$_SESSION['previous_url']."&message=Done ✅");
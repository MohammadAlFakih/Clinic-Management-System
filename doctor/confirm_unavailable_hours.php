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


$dbc = connectServer('localhost', 'root', '', 0);
selectDB($dbc,"clinic_db",0);

$query = "INSERT INTO unavailable_slots (doctor_id,start_date,end_date,department_id)
            VALUES (?,?,?,?) ";

$stmt = $dbc->prepare($query);
$stmt->bind_param("issi",$_SESSION['doctor_id'],$_SESSION['start_date'],$_SESSION['end_date'], $_SESSION['department_id']);
$stmt->execute();
$stmt->close();

//Get all the appointments that overlap with the new unavailabile slot
$sql = " SELECT id
FROM appointment
WHERE doctor_id = ? AND DATE(start_date) = DATE(?) AND status != 'pending' AND status != 'queued' AND
        (( ? >= start_date AND ? < end_date) OR ( ? > start_date AND ? <= end_date)
        OR ( ? < start_date AND ? > end_date) OR (? >= start_date AND ? <= end_date))";
$stmt = $dbc->prepare($sql);
$stmt->bind_param("isssssssss", $_SESSION['doctor_id'],$_SESSION['start_date'], $_SESSION['start_date'],$_SESSION['start_date'],$_SESSION['end_date'],
$_SESSION['end_date'],$_SESSION['start_date'],$_SESSION['end_date'],$_SESSION['start_date'],$_SESSION['end_date']);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

//Delay the overlapped appointments
if(mysqli_num_rows($result) > 0) {
    $appointmens_to_delay = [];
    while($row = $result->fetch_assoc()){
        $appointmens_to_delay[] = $row['id'];
    }
    foreach($appointmens_to_delay as $app_id){
        delay_appointment($_SESSION['start_date'],$app_id);
    }
}

$dbc->close();

unset($_SESSION['department_id']);
unset($_SESSION['start_date']);
unset($_SESSION['end_date']);
unset($_SESSION['work_start_hour']);
unset($_SESSION['work_end_hour']);
header("Location:".$_SESSION['previous_url']."&message=Done ✅");
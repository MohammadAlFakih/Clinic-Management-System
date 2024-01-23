<?php
include '../includes/header.php';
if(!isset($_SESSION['role'])){
    header('location:../login.php');
    die();
}
if($_SESSION['role'] == 'patient'){
    header('location:../index.php');
    die();
}
$_SESSION['doctor_id'] = 1;
if($_SERVER['REQUEST_METHOD'] == 'POST' || !isset($_GET['start_date']) || !isset($_GET['app_id'])){
    header('location:../index.php');
    die();
}

$_SESSION['doctor_id'] = 1;

$dbc = connectServer("localhost","root","",1);
selectDB($dbc,"clinic_db",1);

//Get the appointment date
$query = "SELECT start_date, end_date,patient_id FROM appointment WHERE id = ?";
$stmt = $dbc->prepare($query);
$stmt->bind_param("i",$_GET['app_id']);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if(mysqli_num_rows($result)==0){
    mysqli_close($dbc);
    header('location:../index.php');
    die();
}

$appointment = $result->fetch_assoc();
$appointment_duration = float_duration($appointment['start_date'],$appointment['end_date']);

$start_day = new DateTime((new DateTime($_GET['start_date']))->format('Y-m-d'));
$start_date = new DateTime((new DateTime($_GET['start_date']))->format('Y-m-d'));
//Start the search from the next day
$start_day->modify("+1 day");
$daysArray = array();

// Loop to get the current day and the next 6 days
while(count($daysArray) < 5 ) {
    // Format the date as desired
    $formattedDate = $start_day->format('Y-m-d');
    
    // Add the formatted date to the array
    if((new DateTime($formattedDate))->format('l')!='Sunday' && (new DateTime($formattedDate))->format('l')!='Saturday')
        $daysArray[] = $formattedDate;
    
    // Move to the next day
    $start_day->modify('+1 day');
}

$booked = false; //Flag to check at the end if the appointment is delayed or should be removed

foreach($daysArray as $day){
    
    if($booked)
        break;

    //Get booked appointment on this day
    $query = "SELECT start_date,end_date
            FROM appointment
            WHERE DATE(start_date) = ? AND doctor_id = ? AND status != 'pending' AND status != 'queued'
            ORDER BY start_date ASC";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("si", $day,$_SESSION['doctor_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $booked_slots = [];
    if($result){
        while($row = $result->fetch_assoc()){
            $booked_slots[] = $row;
        }
    }

    //Get unavailable hour on this day
    $query = "SELECT start_date,end_date
            FROM unavailable_slots
            WHERE DATE(start_date) = ? AND doctor_id = ?
            ORDER BY start_date ASC";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("si", $day,$_SESSION['doctor_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $unavailable_slots = [];
    if($result){
        while($row = $result->fetch_assoc()){
            $unavailable_slots[] = $row;
        }
    }

    $sorted_busy_slots = [];
    //Sort results by start_date
    $i=0;$j=0;
    while($i<count($booked_slots) && $j<count($unavailable_slots)){
        if($booked_slots[$i] < $unavailable_slots[$j])
            $sorted_busy_slots[] = $booked_slots[$i++];
        else
            $sorted_busy_slots[] = $unavailable_slots[$j++];
    }
    while($i<count($booked_slots)){
        $sorted_busy_slots[] = $booked_slots[$i++];
    }
    while($j<count($unavailable_slots)){
        $sorted_busy_slots[] = $unavailable_slots[$j++];
    }

    //Get the start and end hours on this day
    $query = "SELECT start_hour,end_hour
            FROM week_schedule
            WHERE day = ? AND doctor_id = ?";
    $stmt = $dbc->prepare($query);
    $current_day_of_week = strtolower((new DateTime($day))->format('l'));
    $stmt->bind_param("si",$current_day_of_week,$_SESSION['doctor_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedule = $result -> fetch_assoc();
    //Set the start and end hours
    $start_hour = $schedule['start_hour'];
    $end_hour = $schedule['end_hour'];

    //Date of next day
    $start_date->modify("+1 day");
    $new_date = $start_date->format('Y-m-d');
    //Two pointers
    $last_end = $new_date." ".$start_hour;
    if(count($sorted_busy_slots)>0){
        $nearest_start = $sorted_busy_slots[0]['start_date'];
    }
    else{
        $nearest_start = $new_date." ".$end_hour;
    }

    //Iterate the sorted busy slots array with two pointer, one for the end of current busy slot and the other
    // for the start of the nearest busy slot until find a free slot between these two pointers
    //If Not go to the next day

    //<---------------------------------------------------------------->
    for ($i=0;$i<count($sorted_busy_slots);$i++) {
        if(float_duration($last_end,$nearest_start)>=$appointment_duration){
            $booked = true;
            $new_start_datetime = $last_end;
            $new_end_hour = float_to_time(time_to_float($new_start_datetime)+$appointment_duration/60);
            $new_end_datetime = $new_date . ' ' . $new_end_hour;
            $query = "UPDATE appointment SET start_date = ?, end_date = ?, status = 'delayed'
                        WHERE id = ? ";
            $stmt = $dbc->prepare($query);
            $stmt->bind_param("ssi",$new_start_datetime,$new_end_datetime,$_GET['app_id']);
            $stmt->execute();
            break;
        }
    if($i==count($sorted_busy_slots)-1){
        $nearest_start = $end_hour;
    }
    else{
        $nearest_start = $sorted_busy_slots[$i+1]['start_date'];
    }
    $last_end = $sorted_busy_slots[$i]['end_date'];
    }
    //<---------------------------------------------------------------->

    //Check the slot between the end of last appointment and the end_work_hour
    if(float_duration($last_end,$nearest_start)>=$appointment_duration){
        $booked = true;
        $new_start_datetime = $last_end;
        $new_end_hour = float_to_time(time_to_float($new_start_datetime)+$appointment_duration/60);
        $new_end_datetime = $new_date . ' ' . $new_end_hour;
        $query = "UPDATE appointment SET start_date = ?, end_date = ?, status = 'delayed'
                    WHERE id = ? ";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("ssi",$new_start_datetime,$new_end_datetime,$_GET['app_id']);
        $stmt->execute();
    }
}

if($booked)
{
    //Send a notification to the patient
    $query = "INSERT INTO notifications (sender,receiver,message,reason)
    VALUES (?,?,?,'delay')";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("iis",$_SESSION['doctor_id'],$appointment['patient_id'],$new_start_datetime);
    $stmt->execute();
}
else{
    $query = "INSERT INTO notifications (sender,receiver,reason)
    VALUES (?,?,'remove')";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("iis",$_SESSION['doctor_id'],$appointment['patient_id']);
    $stmt->execute();
}
$stmt->close();
mysqli_close($dbc);

header('location:../index.php');

?>
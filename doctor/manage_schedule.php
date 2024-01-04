<?php
    include '../includes/header.php';


    if (!isset($_SESSION['role'])) {
        header('location:../login.php');
        die();
    }

    if ($_SESSION['role'] != 'doctor') {
        header('location:../index.php');
        die();
    }
?>

<link rel="stylesheet" href="http://localhost/Clinic-Management-System/static/css/bars.css">
<div class="container">
<div class="work">
    <p>Work hours:🟢</p>
    <p>Unavailable hours:⚫</p>
    <p>Busy hours:🔴</p>
</div>

<?php
    
    //Display all the week scedule if date is not specified
    if (!isset($_GET['date'])) {


    $dbc = connectServer('localhost', 'root', '', 1);
    $db = "mhamad";
    selectDB($dbc, $db, 1);

    date_default_timezone_set('Asia/Beirut');
    
    $today = date("Y-m-d");
    // echo $today.'<br>';
    $day_name = strtolower(date("l", strtotime($today)));
    // echo $day_name . '<br><br>';

    $query = "SELECT day, start_hour, end_hour FROM week_schedule WHERE doctor_id = ? ";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param('i',$_SESSION['doctor_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $weekScheduleArray = array();

    while($row = $result->fetch_assoc()){
        $weekScheduleArray[] = $row;
    }

    $stmt->close();

    $weekDays = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');

    $currentIndex = array_search(($day_name), $weekDays);

    // Create a new array starting from the current day to the end
    $sortedWeekSchedule = array_merge(
        array_slice($weekScheduleArray, $currentIndex),
        array_slice($weekScheduleArray, 0, $currentIndex));

    // $index = 0;

    // foreach ($sortedWeekSchedule as $dayInfo) {
    //     echo "Day " . $index . " : " . $dayInfo['day'] . "<br>";
    //     echo "Start Hour: " . $dayInfo['start_hour'] . "<br>";
    //     echo "End Hour: " . $dayInfo['end_hour'] . "<br>";
    //     echo "<hr>";
    //     $index++;
    // }

    $index = 0;

    foreach ($sortedWeekSchedule as $dayInfo) {

        $desiredDate = date("Y-m-d", strtotime($today . "+" . $index . " day"));

        $dateTimeObject = new DateTime($desiredDate);


        echo '
        <hr>
        <div class="weekDay">
        <a class="no-underline" href="' . $_SERVER['REQUEST_URI'] . '?&date=' . $desiredDate . '"><p class="edges"> '. $dateTimeObject->format('l, n/j/Y') .'</p></a>
        <div class="edges">
        <p>';
        echo substr($dayInfo['start_hour'],0,5);
        echo '
        </p>
        <p>';
        echo substr($dayInfo['end_hour'],0,5);
        echo '
        </p>
        </div>';

        //Doctor unavailable time
        $query = "SELECT un.start_date, un.end_date
            FROM unavailable_slots un
            WHERE un.doctor_id = ? AND DATE(un.start_date) =?
            ORDER BY un.start_date ASC";

        $unavailable_time = array();
        $booked_time = [];

        // echo $desiredDate . '<br>';

        $stmt = $dbc->prepare($query);
        $stmt->bind_param("is",$_SESSION['doctor_id'], $desiredDate);
        $stmt->execute();
        $appointments = $stmt->get_result();
        $stmt->close();


        while($row = $appointments->fetch_assoc()){
            $unavailable_time[] = array('start_date' => $row['start_date'], 'end_date' => $row['end_date']);
        }

        //Doctor Appointments dates
        $query = "SELECT app.start_date, app.end_date
                FROM appointment app
                WHERE app.doctor_id = ? AND DATE(app.start_date) = ?
                ORDER BY app.start_date ASC";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("is",$_SESSION['doctor_id'],$desiredDate);
        $stmt->execute();
        $appointments = $stmt->get_result();
        $stmt->close();
        
        while($row = $appointments->fetch_assoc()){
            $booked_time[] = array('start_date' => $row['start_date'], 'end_date' => $row['end_date']);
        }

        // echo "Unavailable Time: <br>";
        // foreach ($unavailable_time as $dayInfo) {
        //     echo "Start Hour: " . $dayInfo['start_date'] . "<br>";
        //     echo "End Hour: " . $dayInfo['end_date'] . "<br>";
        //     echo "<hr>";
        // }

        // echo "Booked Time: <br>";
        // foreach ($booked_time as $dayInfo) {
        //     echo "Start Hour: " . $dayInfo['start_date'] . "<br>";
        //     echo "End Hour: " . $dayInfo['end_date'] . "<br>";
        //     echo "<hr>";
        // }
    
        echo '
        <div class="work_hours_bar"></div>
        <div class="unavailable_hours">
        ';
        
        $result = merge_intervals($unavailable_time ,$dayInfo['start_hour'] ,$dayInfo['end_hour']);
        $merged_time= $result[0];
        $merged_bars = $result[1];
        $last_start=0;
        for($i=0;$i<count($merged_bars);$i++){
            //echo $merged_bars[$i]['width'];
            echo '<div style="width:'.$merged_bars[$i]['start']-$last_start.'vw"></div>';
            echo '<div class="unavailable_hours_bar" style="width:'.$merged_bars[$i]['width'].'vw;">'.
            float_to_time($merged_time[$i]['start_date']+time_to_float($dayInfo['start_hour']))." till ".
            float_to_time($merged_time[$i]['end_date']+time_to_float($dayInfo['start_hour'])).'</div>';
            $last_start = $merged_bars[$i]['start']+$merged_bars[$i]['width'];
        }
        echo '
        </div>
        <div class="unavailable_hours">
        ';

        $result = merge_intervals($booked_time,$dayInfo['start_hour'],$dayInfo['end_hour']);
        $merged_time= $result[0];
        $merged_bars = $result[1];
        $last_start=0;
        for($i=0;$i<count($merged_bars);$i++){
            echo '<div style="width:'.$merged_bars[$i]['start']-$last_start.'vw"></div>';
            echo '<div class="booked_hours_bar" style="width:'.$merged_bars[$i]['width'].'vw;">'.
            float_to_time($merged_time[$i]['start_date']+time_to_float($dayInfo['start_hour']))." till ".
            float_to_time($merged_time[$i]['end_date']+time_to_float($dayInfo['start_hour'])).'</div>';
            $last_start = $merged_bars[$i]['start']+$merged_bars[$i]['width'];
        }
        echo '
        </div>
        </div> 
        <hr>
        '; //last div for the weekDay class

        $index++;
    }
    }
    // Show and configure the specific day schedule
    else {
        echo "HIIIII";
    }

?>
</div>

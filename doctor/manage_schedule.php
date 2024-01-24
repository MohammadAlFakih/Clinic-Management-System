<?php
    include '../includes/header.php';


    if (!isset($_SESSION['role'])) {
        header('location:../login.php');
        die();
    }

    if ($_SESSION['role'] == 'patient') {
        header('location:../index.php');
        die();
    }
?>

<link rel="stylesheet" href="http://localhost/Clinic-Management-System/static/css/bars.css">
<link rel="stylesheet" href="../static/css/unavailable_hours.css">
<div class="container">
<div class="work">
    <p>Work hours:🟢</p>
    <p>Unavailable hours:⚫</p>
    <p>Busy hours:🔴</p>
</div>

<?php

    //Display all the week scedule if date is not specified
    if (!isset($_GET['date'])) {


    $dbc = connectServer('localhost', 'root', '', 0);
    $db = "clinic_db";
    selectDB($dbc, $db, 0);

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

    // foreach ($sortedWeekSchedule as $dayyy) {
    //     echo "Day " . $dayyy['day'] . "<br>";
    //     echo "Start Hour: " . $dayyy['start_hour'] . "<br>";
    //     echo "End Hour: " . $dayyy['end_hour'] . "<br>";
    //     echo "<hr>";
    // }

    $index = 0;

    foreach ($sortedWeekSchedule as $dayInfo) {

        // Don't show days off and check validity

        if ($dayInfo['start_hour'] != $dayInfo['end_hour']) {

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
            <div class="container">
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

    }
    $index++;
    }
    }

    // Show and configure the specific day schedule
    else {

        if(isset($_GET['message'])){
            if ($_GET['message'] == "Done ✅") {
                echo '<div class="message2">' . $_GET['message'] . '</div>';
            }
            else {
                echo '<div class="message">' . $_GET['message'] . '</div>';
            }
        }

        $_SESSION['previous_url'] = $_SERVER['REQUEST_URI'];

        $chosen_date = $_GET['date'];

        $day = date("l", strtotime($chosen_date));
        $day = strtolower($day);

        $dbc = connectServer('localhost', 'root', '', 0);
        $db = "clinic_db";
        selectDB($dbc, $db, 0);

        $query = "SELECT start_hour, end_hour FROM week_schedule WHERE doctor_id = ? AND day = ? ";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param('is',$_SESSION['doctor_id'], $day);
        $stmt->execute();
        $result = $stmt->get_result();

        $schedule = $result->fetch_assoc();

        $unavailable_time = array();
        $booked_time = [];

        //Doctor unavailable time
        $query = "SELECT un.start_date, un.end_date
            FROM unavailable_slots un
            WHERE un.doctor_id = ? AND DATE(un.start_date) =?
            ORDER BY un.start_date ASC";

        $stmt = $dbc->prepare($query);
        $stmt->bind_param("is",$_SESSION['doctor_id'], $chosen_date);
        $stmt->execute();
        $appointments = $stmt->get_result();
        $stmt->close();
        $full_available = false;
        if ($appointments->num_rows == 0) {
            $full_available = true;
        }


        while($row = $appointments->fetch_assoc()){
            $unavailable_time[] = array('start_date' => $row['start_date'], 'end_date' => $row['end_date']);
        }

        //Doctor Appointments dates
        $query = "SELECT app.start_date, app.end_date
                FROM appointment app
                WHERE app.doctor_id = ? AND DATE(app.start_date) = ?
                ORDER BY app.start_date ASC";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("is",$_SESSION['doctor_id'],$chosen_date);
        $stmt->execute();
        $appointments = $stmt->get_result();
        $stmt->close();
        
        while($row = $appointments->fetch_assoc()){
            $booked_time[] = array('start_date' => $row['start_date'], 'end_date' => $row['end_date']);
        }

        $dateTimeObject = new DateTime($chosen_date);

        echo '
        <hr>
        <div class="weekDay">
        <p class="edges"> '. $dateTimeObject->format('l, n/j/Y') .'</p>
        <div class="edges">
        <p>';
        echo substr($schedule['start_hour'],0,5);
        echo '
        </p>
        <p>';
        echo substr($schedule['end_hour'],0,5);
        echo '
        </p>
        </div>
        <div class="container">
        <div class="work_hours_bar"></div>
        <div class="unavailable_hours">
        ';
        
        $result = merge_intervals($unavailable_time ,$schedule['start_hour'] ,$schedule['end_hour']);
        $merged_time= $result[0];
        $merged_bars = $result[1];
        $last_start=0;
        for($i=0;$i<count($merged_bars);$i++){
            //echo $merged_bars[$i]['width'];
            echo '<div style="width:'.$merged_bars[$i]['start']-$last_start.'vw"></div>';
            echo '<div class="unavailable_hours_bar" style="width:'.$merged_bars[$i]['width'].'vw;">'.
            float_to_time($merged_time[$i]['start_date']+time_to_float($schedule['start_hour']))." till ".
            float_to_time($merged_time[$i]['end_date']+time_to_float($schedule['start_hour'])).'</div>';
            $last_start = $merged_bars[$i]['start']+$merged_bars[$i]['width'];
        }
        echo '
        </div>
        <div class="unavailable_hours">
        ';

        $result = merge_intervals($booked_time,$schedule['start_hour'],$schedule['end_hour']);
        $merged_time= $result[0];
        $merged_bars = $result[1];
        $last_start=0;
        for($i=0;$i<count($merged_bars);$i++){
            echo '<div style="width:'.$merged_bars[$i]['start']-$last_start.'vw"></div>';
            echo '<div class="booked_hours_bar" style="width:'.$merged_bars[$i]['width'].'vw;">'.
            float_to_time($merged_time[$i]['start_date']+time_to_float($schedule['start_hour']))." till ".
            float_to_time($merged_time[$i]['end_date']+time_to_float($schedule['start_hour'])).'</div>';
            $last_start = $merged_bars[$i]['start']+$merged_bars[$i]['width'];
        }

        //Get the department id of the doctor for the unavailable slots table
        $query = " SELECT department_id FROM doctor WHERE id = ? ;";

        $stmt = $dbc->prepare($query);
        $stmt->bind_param('i',$_SESSION['doctor_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $row = $result->fetch_assoc();
        $dep_id = $row['department_id'];

        $_SESSION['last_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['department_id'] = $dep_id;
        $_SESSION['work_start_hour'] = $schedule['start_hour'];
        $_SESSION['work_end_hour'] = $schedule['end_hour'];

        echo '
        </div>
        </div> 
        <hr>

        <form method="POST" action="unavailable_hours.php">
        <input type="hidden" name="department_id" value="'. $dep_id. '">
        <input type="hidden" name="date" value="'. $_GET["date"]. '">
        <div class="choose_time">
        <div class="col">
        <div class="col">
            <p class="styled-label" >Choose your unavailable hours for this day</p>
        </div>
        <div class="row">
        <label class="styled-label" for="startTime">From:</label>
        <input class="styled-input" type="time" name="start_hour" required>
        </div>
        <div class="row">
        <label class="styled-label" for="endTime">To:</label>
        <input class="styled-input" type="time" name="end_hour" required>
        </div>
        <div class="row">
        <button class="styled-button" type="submit">Submit</button>
        </div>
        </div>
        </form>';
        
        if (!$full_available) {
            $_SESSION['date'] = $_GET['date'];
            echo '
            <form method="post" action="clear_unavailable_hours.php">
                <button class="styled-button-on" type="submit">Clear Unavailable Hours</button>
            </form>
            
            </div>
            ';
        }
        else {
        echo '
        <div>
            <div class="styled-button-off">Clear Unavailable Hours</div>
        </div>
        
        </div>
        ';
        }
    }

?>
</div>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/appointments.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Appointments</title>
</head>
        

<?php
    include '../includes/header.php';
    if(!isset($_SESSION['role']))
        header("location:../login.php");

    if($_SESSION['role'] != 'doctor' && $_SESSION['role'] != 'secretary') {
        header("location:../index.php");
        die();
    }
    if(isset($_GET['message'])){
        echo '<div class="message">' . $_GET['message'] . '</div>';
    }
?>
    <div class="container">
        <h1>Appointments List</h1>
        <form method="post" action="appointments.php">
        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="any" <?php echo ($_SERVER['REQUEST_METHOD']=='POST' && $_POST['status'] === 'any') ? 'selected' : ''; ?>>Any</option>
            <option value="upcoming"<?php echo ($_SERVER['REQUEST_METHOD']=='POST' && $_POST['status'] === 'upcoming') ? 'selected' : ''; ?>>Upcoming</option>
            <option value="done"<?php echo ($_SERVER['REQUEST_METHOD']=='POST' && $_POST['status'] === 'done') ? 'selected' : ''; ?>>Done</option>
        </select>

        <label for="filter_option">Filter By Date:</label>
        <!-- <select name="filter_option" id="filter_option">
            <option value="all" <?php echo ($_POST['filter_option'] === 'all') ? 'selected' : ''; ?>>All Days</option>
            <option value="between_2_days" <?php echo ($_POST['filter_option'] === 'between_2_days') ? 'selected' : ''; ?>>Between 2 Days</option>
            <option value="single_day" <?php echo ($_POST['filter_option'] === 'single_day') ? 'selected' : ''; ?>>Single Day</option>
        </select> -->

        <select name="filter_option" id="filter_option">
            <option value="all">All Days</option>
            <option value="between_2_days">Between 2 Days</option>
            <option value="single_day">Single Day</option>
        </select>

        <div id="dateFilter" style="display: none;">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">

            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
        </div>

        <div id="singleDayFilter" style="display: none;">
            <label for="single_date">Specific Date:</label>
            <input type="date" name="single_date" id="single_date" value="<?php echo isset($_POST['single_date']) ? $_POST['single_date'] : ''; ?>">
        </div>

        <button type="submit" class="filter-icon">
            <i class="fas fa-filter"></i>
        </button>
        </form>
    </div>
        
<?php

        $dbc = connectServer('localhost','root','',1);
        selectDB($dbc,'mhamad',1);

        $query = "SELECT appointment.*, patient.first_name, patient.last_name, patient.age, patient.gender,patient.phone FROM appointment
                JOIN patient ON appointment.patient_id = patient.id
                WHERE doctor_id = ? AND status != 'pending' AND status != 'queued'";

        if (!isset($_POST['status']) || ($_POST['status']) == 'any') {
            if (!isset($_POST['filter_option']) || ($_POST['filter_option'] == 'all')) {
                $stmt = $dbc->prepare($query);
                $stmt->bind_param("i",$_SESSION['doctor_id']);
            }
            elseif ($_POST['filter_option'] == 'single_day') {
                $chosen_day = $_POST["single_date"];
                $query .= " AND DATE(appointment.start_date) = ? ";
                $stmt = $dbc->prepare($query);
                $stmt->bind_param("is",$_SESSION['doctor_id'], $chosen_day);
                // $chosen_day = new DateTime($chosen_day);
                // $chosen_day = $chosen_day->format('Y-m-d');
                echo '<div>
                        <p>'.$chosen_day.'</p>
                    </div>';
            }
            elseif ($_POST['filter_option'] == 'between_2_days') {
                $startDate = $_POST["start_date"];
                $endDate = $_POST["end_date"];
                if ($startDate <= $endDate) {
                    $query .= " AND DATE(appointment.start_date) BETWEEN ? AND ? ";
                    $stmt = $dbc->prepare($query);
                    $stmt->bind_param("iss", $_SESSION['doctor_id'], $startDate, $endDate);
                    echo '<div>
                            <p>From '.$startDate.' -> To ' . $endDate.'</p>
                        </div>';
                }
                else {
                    header('location:appointments.php?message=Choose two valid dates!');
                    die();
                }
            }
        }
        else {
            $chosen_status = $_POST['status'];
            $query .= " AND status = ? ";

            if (!isset($_POST['filter_option']) || ($_POST['filter_option'] == 'all')) {
                $stmt = $dbc->prepare($query);
                $stmt->bind_param("is",$_SESSION['doctor_id'],$chosen_status);
            }
            elseif ($_POST['filter_option'] == 'single_day') {
                $chosen_day = $_POST["single_date"];
                $query .= " AND DATE(appointment.start_date) = ? ";
                $stmt = $dbc->prepare($query);
                $stmt->bind_param("iss",$_SESSION['doctor_id'] ,$chosen_status ,$chosen_day);
                echo '<div>
                        <p>'.$chosen_day.'</p>
                    </div>';
            }
            elseif ($_POST['filter_option'] == 'between_2_days') {
                $startDate = $_POST["start_date"];
                $endDate = $_POST["end_date"];
                if ($startDate <= $endDate) {
                    $query .= " AND DATE(appointment.start_date) BETWEEN ? AND ? ";
                    $stmt = $dbc->prepare($query);
                    $stmt->bind_param("isss", $_SESSION['doctor_id'],$chosen_status, $startDate, $endDate);
                    echo '<div>
                            <p>From '.$startDate.' -> To ' . $endDate.'</p>
                        </div>';
                }
                else {
                    header('location:appointments.php?message=Choose two valid dates!');
                    die();
                }
        }
    }

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        
        //No appointments
        if($result->num_rows == 0){
            echo '
            <div class="message">
                <p>There are no appointments 📅.</p>';
            // echo '</div>
            //     <a class="make_app" href="http://localhost/Clinic-Management-System/patient/make_appointment2.php">
            //     New Appointment
            //     </a>
            // </div>';
        }
        //Display the appointments
        else{
            echo '
        <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th class="hide">Age</th>
                    <th>Gender</th>
                    <th class="hide">Phone</th>
                    <th>Start Date</th>
                    <th>Duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';
            while($appointment = $result->fetch_assoc()){

                //Calculate Duration
                $display_duration = duration($appointment['start_date'],$appointment['end_date']);

                $appointment['start_date'] = new DateTime($appointment['start_date']);
                $appointment['start_date'] = $appointment['start_date']->format('Y-m-d H:i');

                echo '<tr class="even" onclick="window.location=`../includes/appointment.php?app_id='.$appointment['id'].'`">
                <td>'.$appointment['first_name']." ".$appointment['last_name'].'</td>
                <td class="hide">'.$appointment['age'].'</td>
                <td>'.$appointment['gender'].'</td>
                <td class="hide">'.$appointment['phone'].'</td>
                <td>'.$appointment['start_date'].'</td>
                <td>'.$display_duration.'</td>
                <td>'.$appointment['status'].'</td>
                </tr>';
            }
            echo '</tbody>
        </table></div>';
        }
?>
<?php 
mysqli_close($dbc);
?>

<script>
    document.getElementById('filter_option').addEventListener('change', function() {
        var dateFilter = document.getElementById('dateFilter');
        var singleDayFilter = document.getElementById('singleDayFilter');

        if (this.value === 'between_2_days') {
            dateFilter.style.display = 'block';
            singleDayFilter.style.display = 'none';
        } else if (this.value === 'single_day') {
            dateFilter.style.display = 'none';
            singleDayFilter.style.display = 'block';
            singleDayInput.value = ''; // Clear the input value
        } else {
            dateFilter.style.display = 'none';
            singleDayFilter.style.display = 'none';
        }
    });
</script>

</html>
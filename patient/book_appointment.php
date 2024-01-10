<?php
    include "../includes/header.php";
    if(!isset($_SESSION['role'])){
        header('location:../login.php');
        die();
    }

    if(!isset($_SESSION['date'])
        || !isset($_SESSION['selected_doctor_id'])
        || !isset($_SESSION['department_id'])
        || !isset($_POST['start_hour']) || !isset($_POST['end_hour'])){
    header("Location:".$_SESSION['last_url']);
    die();
    }

    if (($_SESSION['role'] == 'patient' && !isset($_SESSION['patient_id'])) 
        || (in_array($_SESSION['role'], array('doctor', 'secretary')) && !isset($_SESSION['selected_patient_id']))) {
        header("Location:".$_SESSION['last_url']);
        die();
    }

    // Get the selected patient name from the selected_patient_id inside the session
    if ($_SESSION['role'] == 'doctor' || $_SESSION['role'] == 'secretary') {
        $dbc = connectServer('localhost', 'root', '', 1);
        $db = "mhamad";
        selectDB($dbc, $db, 1);
        $query = " SELECT first_name, last_name FROM patient 
                WHERE id = ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param('i', $_SESSION['selected_patient_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $full_name = $row['first_name'] . ' ' . $row['last_name'];
        $_SESSION['selected_patient_name'] = $full_name;
    }


    $dbc = connectServer('localhost', 'root', '', 1);
    selectDB($dbc,"mhamad",1);

    $start_date = $_SESSION['date']." ".$_POST['start_hour'];
    $end_date = $_SESSION['date']." ".$_POST['end_hour'];

    //Check if the chosen date is between start and end hour
    $valid_interval = validate_interval($_POST['start_hour'],$_POST['end_hour'],$_SESSION['work_start_hour'],$_SESSION['work_end_hour']);
    if(!$valid_interval){
        header("Location:".$_SESSION['last_url']."&message=Please choose two valid start and end hour.");
        $dbc->close();
        die();
    }

    //Check if the patient choose an unvailable date
    $query = "SELECT *
                FROM unavailable_slots
                WHERE doctor_id = ? AND (( ? > start_date AND ? < end_date) OR ( ? > start_date AND ? < end_date)
                OR ( ? = start_date AND ? = end_date ) OR ( ? <= start_date AND ? >= end_date))";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("issssssss",$_SESSION['selected_doctor_id'],$start_date,$start_date,$end_date,$end_date,$start_date,$end_date,$start_date,$end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result && mysqli_num_rows($result)>0){
        header("Location:".$_SESSION['last_url']."&message=".$_SESSION['selected_doctor_id']."Please make sure that your choosen date doesn't overlap with unvailable hours.");
        $stmt->close();
        $dbc->close();
        die();
    }

    //Check if he is a spammer
    $today = date("Y-m-d");
    $query = "SELECT id
                FROM appointment
                WHERE patient_id = ? AND DATE(start_date) >= ?;";
    $stmt = $dbc->prepare($query);
    if ($_SESSION['role'] == 'patient')
        $stmt->bind_param("is",$_SESSION['patient_id'],$today);
    elseif ($_SESSION['role'] == 'doctor' || $_SESSION['role'] == 'secretary')
        $stmt->bind_param("is",$_SESSION['selected_patient_id'],$today);
    $stmt->execute();
    $result = $stmt->get_result();
    $maximum_number_of_appointment = 5;
    if($result && mysqli_num_rows($result)>=$maximum_number_of_appointment){
        header("Location:".$_SESSION['last_url']."&message=You have reached your maximum available number of appointments.");
        $stmt->close();
        $dbc->close();
        die();
    }

    $_SESSION['start_date'] = $_SESSION['date']." ".$_POST['start_hour'];
    $_SESSION['end_date'] = $_SESSION['date']." ".$_POST['end_hour'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/book_appointment.css">
    <title>Document</title>
<body>
<div class="big_container">
<div class="container">
    <h1>New Appointment</h1>
    <hr>
    <div class="doctor_info">
        <div class="col">
            <div class="doctor">With Dc. <?php echo $_POST['doctor_name'];?></div>
            <div class="specialization">👨‍⚕️ <?php echo ucfirst($_POST['specialization']);?></div>
        </div>
        <div class="col">
            <div class="doctor">Adress 📌   </div>
            <div class="specialization"><?php echo ucfirst($_POST['city']);?></div>    
        </div>
    </div>
    <?php
        if ($_SESSION['role'] == 'patient')
            echo'<div class="col">Patient:'.$_SESSION['patient_name'].'</div>';
        elseif ($_SESSION['role'] == 'doctor' || $_SESSION['role'] == 'secretary')
            echo'<div class="col">Patient:'.$_SESSION['selected_patient_name'].'</div>';

    ?>
    <div class="col">
        <div class="date">Date: <?php echo $_SESSION['date'];?></div>
        <div class="date">At  
        <?php 
            echo $_POST['start_hour'];
        ?>
        <div class="date">Duration : <?php echo duration($_POST['start_hour'],$_POST['end_hour'])?></div>
    </div>
    </div>
    <div class="form_buttons">
    <a href="<?php echo $_SESSION['last_url']?>" class="form_button back">Back</a>
    <form method="post" action="../includes/insert_appointment.php">
    <input type="submit" value="Confirm" class="form_button">
    </form>
    </div>
    </div>
    </div>
</body>
</html>
<?php
    $stmt->close();
    mysqli_close($dbc);
?>
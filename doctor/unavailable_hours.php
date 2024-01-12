<?php
    include "../includes/header.php";
    if(!isset($_SESSION['role'])){
        header('location:../login.php');
        die();
    }

    if($_SESSION['role'] != 'doctor' && $_SESSION['secretary']) {
        header("location:../index.php");
        die();
    }

    $dbc = connectServer('localhost', 'root', '', 1);
    selectDB($dbc,"mhamad",1);

    $start_date = $_POST['date']." ".$_POST['start_hour'];
    $end_date = $_POST['date']." ".$_POST['end_hour'];

    
    //Check if the chosen date is between start and end hour
    $valid_interval = validate_interval($_POST['start_hour'],$_POST['end_hour'],$_SESSION['work_start_hour'],$_SESSION['work_end_hour']);
    if(!$valid_interval){
        header("Location:".$_SESSION['previous_url']."&message=Please choose two valid start and end hours.");
        $dbc->close();
        die();
    }

    //Check if the doctor chose the same unvailable period twice
    $query = "SELECT *
                FROM unavailable_slots
                WHERE doctor_id = ? AND ( ? = start_date AND ? = end_date ) ";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("iss",$_SESSION['doctor_id'],$start_date,$end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if($result && mysqli_num_rows($result)>0){
        header("Location:".$_SESSION['previous_url']."&message=Unavailable Period already registered!");
        $dbc->close();
        die();
    }

    $sql = " SELECT COUNT(*) AS overlap_count
    FROM appointment
    WHERE doctor_id = ? AND DAY(start_date) = DAY(?) AND (
        (? >= start_date AND ? < end_date)
        OR (? > start_date AND ? <= end_date))";


    $stmt = $dbc->prepare($sql);
    $stmt->bind_param("isssss", $_SESSION['doctor_id'],$_POST['date'], $start_date, $start_date, $end_date, $end_date);
    $stmt->execute();
    $stmt->bind_result($overlapCount);
    $stmt->fetch();
    $stmt->close();
    
    if($overlapCount > 0) {
        header("Location:".$_SESSION['previous_url']."&message=Overlap with a booked appointment! To be continued...");
        $dbc->close();
        die();
    }


    $_SESSION['start_date'] = $_POST['date']." ".$_POST['start_hour'];
    $_SESSION['end_date'] = $_POST['date']." ".$_POST['end_hour'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/confirm_unavailable_hours.css">
    <title>Unavailable Period</title>
<body>
<div class="big_container">
<div class="container">
    <h1>Confirm Unavailable Period</h1>
    <hr>
    <div class="col">
        <div class="date">Date: <?php echo $_POST['date'];?></div>
        <div class="date">From:  
        <?php 
            echo $_POST['start_hour'];
        ?>
        <div class="date">To: 
        <?php 
            echo $_POST['end_hour'];
        ?>
        <div class="date">Duration : <?php echo duration($_POST['start_hour'],$_POST['end_hour'])?></div>
    </div>
    </div>
    <div class="form_buttons">
    <a href="<?php echo $_SESSION['last_url']?>" class="form_button back">Back</a>
    <form method="post" action="../doctor/confirm_unavailable_hours.php">
    <input type="submit" value="Confirm" class="form_button">
    </form>
    </div>
    </div>
    </div>
</body>
</html>

<?php
    mysqli_close($dbc);
?>
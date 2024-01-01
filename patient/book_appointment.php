<?php
    include "../includes/header.php";
    if(!isset($_SESSION['role'])){
        header('location:../login.php');
        die();
    }
    
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
    <div class="col">Patient: <?php echo "  ".$_SESSION['patient_name']?></div>
    <div class="col">
        <div class="date">Date: <?php echo $_POST['date'];?></div>
        <div class="date">At  
        <?php 
        $slots = [];
        foreach($_POST['slots'] as $slot=>$value){
            $slots[]=$value;
        }
        if(count($slots)==0){
            header('location:'.$_SESSION['last_url']);
        }
        else if(count($slots)==1){
            //start hour of doctor + index of choosen slot
            $start_hour = $_POST['start_hour'] + $slots[0]/2;
            $duration = 0.5;
        }
        else if(count($slots)==2 && $duration=$slots[1]-$slots[0]==1){
            $start_hour = $_POST['start_hour'] + $slots[0]/2;
            $duration = 1;
        }
        else{
            header('location:'.$_SESSION['last_url']."&message=Please choose one or two valid slots");
        }
        $start_hour = float_to_hour($start_hour);
        echo $start_hour." Duration: ";
        if($duration==0.5){
            echo "30 minutes";
        }
        else if($duration==1){
            echo "1 hour";
        }
        
        $dateTimeString = $_POST['date'] . ' ' . $start_hour;
        // Create DateTime object
        //$dateTimeObject = DateTime::createFromFormat('Y-m-d H:i', $dateTimeString);
        $_SESSION['date_time'] = $dateTimeString;
        $_SESSION['duration'] = $duration;

        ?>
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

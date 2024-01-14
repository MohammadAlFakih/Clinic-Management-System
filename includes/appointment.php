<link rel="stylesheet" href="../static/css/appointment.css" type="text/css">
<?php
include '../includes/header.php';
if(!isset($_SESSION['role'])){
    header("location:../login.php");
    die();
}
$dbc = connectServer('localhost','root','',1);
selectDB($dbc,'mhamad',1);

if($_SERVER['REQUEST_METHOD'] == 'get'){
//Check if this appointment is for the requesting patient
if ($_SESSION['role'] == 'patient') {
    if($_SERVER['REQUEST_METHOD'] == 'GET')
        $app_id = $_GET['app_id'];
    else
        $app_id = $_POST['app_id'];
    $valid = check_app_for_patient($dbc,$_SESSION['patient_id'],$app_id);
    if(!$valid){
        mysqli_close($dbc);
        header("location:../patient/appointments.php");
        die();
    }
}
//Check if this appointemnt is for the requesting doctor doctor
else{
    if($_SERVER['REQUEST_METHOD'] == 'GET')
        $app_id = $_GET['app_id'];
    else
        $app_id = $_POST['app_id'];
    $valid = check_app_for_doctor($dbc,$_SESSION['doctor_id'],$app_id);
    if(!$valid){
        mysqli_close($dbc);
        header("location:../doctor/appointments.php");
        die();
    }
}
}

//Check if the request method is post and the role is patient
if($_SESSION['role'] == 'patient' && $_SERVER['REQUEST_METHOD'] == 'post'){
    header("location:../index.php");
    mysqli_close($dbc);
    die();
}

//Saving the new information after submitting the form
if(isset($_POST['save'])){
    $valid = check_app_for_doctor($dbc,$_SESSION['doctor_id'],$_POST['app_id']);
    if(!$valid){
        mysqli_close($dbc);
        header("location:../doctor/appointments.php");
        die();
    }
    //Successfully updated 
    $update_attempt = update_appointment($dbc,$_POST);
    if($update_attempt[0]){ //$update_attempt[0] is true if the update is successful, false otherwise
        echo "<div class='message success'>Appointment updated successfully</div>";
    }
    else{
        echo "<div class='message fail'>".$update_attempt[1]."</div>"; //$update_attempt[1] is the message
    }
    $_GET['edit']=1;
}

    if($_SERVER['REQUEST_METHOD'] == 'GET')
        $app_id = $_GET['app_id'];
    else
        $app_id = $_POST['app_id'];
    $appointment = get_appointment($dbc,$app_id);
    echo '
    <div class="appointment-container">
        <form method="post" action="appointment.php">
        <input type="hidden" value="'.$appointment['id'].'" name="app_id">
        <div class="info-container">
        <div class="row">
        <div class="col">

        <h1>My Appointment<span class="status">';
        if($appointment['status']=='queued')
                        echo 'pending';
                    else
                        echo $appointment['status'];
        echo '</span>';
        if($appointment['status'] == 'pending' || $appointment['status']=='queued'){
            echo '<a href="../includes/cancel_appointment.php?app_id='.$app_id.'&patient_id='.$_SESSION['patient_id'].'" class="cancel">Cancel</a>';
        }

        elseif ($_SESSION['role'] != 'patient') {
            echo '<a href="../includes/cancel_appointment.php?app_id='.$app_id.'&patient_id='.$appointment['patient_id'].'" class="cancel">Remove</a>';
            if(!isset($_GET['edit']) && !isset($_POST['show_schedule']))
                echo "<a href='../includes/appointment.php?app_id=".$app_id."&edit=1' class='edit' >Edit</a>";
            else
                echo "<input type='submit' class='edit' value='Save' name='save'>";
        }
        //Added by Mhamad
        else if($appointment['status'] == 'delayed'){
            echo '<a href="../includes/cancel_appointment.php?app_id='.$app_id.'&patient_id='.$_SESSION['patient_id'].'" class="cancel remove">Remove</a>';
        }
        echo'</h1>
        <span class="note">'.$appointment['details'].'</span>
        </div>
        </div>
        <div class="row">
            <div class="info-item">
                <div class="info-label">Doctor Name 👨‍⚕️:</div>
                <div class="info-value">'.$appointment['first_name']." ".$appointment['last_name'].' ('.$appointment['alias'].')</div>
            </div>

            <div class="info-item">
                <div class="info-label">Patient Name 👨‍💼:</div>
                <div class="info-value">'.$appointment['pa_fname']." ".$appointment['pa_lname'].'</div>
            </div></div>

            <div class="row pers">
                <div class="col det">
                    <div class="info-label prescription">Details :</div>
                    <div class="info-value">';
                    if(!isset($_GET['edit']) && !isset($_POST['show_schedule']))
                        echo $appointment['document_details'];
                    else if(isset($_POST['show_schedule']))
                        echo '<textarea name="new_details" rows=15 cols=35>'.$_POST['new_details'].'</textarea>';
                    else{
                        echo '<textarea name="new_details" rows=15 cols=35>'.$appointment['document_details'].'</textarea>';
                    }
                echo'</div>
                </div>
                <div class="col">
                    <div class="info-label prescription">Prescription ⚕️:</div>
                    <div class="info-value">';
                    if(!isset($_GET['edit']) && !isset($_POST['show_schedule']))
                        echo $appointment['prescription'];
                    else if(isset($_POST['show_schedule']))
                        echo '<textarea name="new_prescription" rows=15 cols=35 >'.$_POST['new_prescription'].'</textarea>';
                    else{
                        echo '<textarea name="new_prescription" rows=15 cols=35 >'.$appointment['prescription'].'</textarea>';
                    }
                echo'</div>
                </div>
            </div>

            <div class="row">
            <div class="info-item">
                <div class="info-label">Date 📅:</div>';

                if(!isset($_GET['edit']) && !isset($_POST['show_schedule']))
                    echo '<div class="info-value">'.(new DateTime($appointment['start_date']))->format('Y-m-d').'</div>';
                else if(isset($_POST['show_schedule']))
                    echo '<input type="date" name="new_start_date" value="'.(new DateTime($_POST['new_start_date']))->format('Y-m-d').'">
                    <input type="submit" name="show_schedule" value="Show Schedule" class="show-sc">';
                else{
                    //Show button that send a post request with current data and allow the server to resend the schedule
                    //on the chosen date
                    echo '<input type="date" name="new_start_date" value="'.(new DateTime($appointment['start_date']))->format('Y-m-d').'">
                            <input type="submit" name="show_schedule" value="Show Schedule" class="show-sc">';
                }

            echo '</div>
            <div class="info-item">
                <div class="info-label">Start Hour 🕒:</div>';
                if(!isset($_GET['edit']) && !isset($_POST['show_schedule']))
                    echo '<div class="info-value">'.(new DateTime($appointment['start_date']))->format('H:i').'</div>';
                else if(isset($_POST['show_schedule']))
                    echo '<input type="time" name="new_start_hour" value="'.(new DateTime($_POST['new_start_hour']))->format('H:i').'">';
                else
                    echo '<input type="time" name="new_start_hour" value="'.(new DateTime($appointment['start_date']))->format('H:i').'">';
            echo '</div>

            <div class="info-item">
                <div class="info-label">End Hour :</div>';
            if(!isset($_GET['edit']) && !isset($_POST['show_schedule']))
                echo '<div class="info-value">'.(new DateTime($appointment['end_date']))->format('H:i').'</div>';
            else if(isset($_POST['show_schedule']))
                echo '<input type="time" name="new_end_hour" value="'.$_POST['new_end_hour'].'">';
            else
                echo '<input type="time" name="new_end_hour" value="'.(new DateTime($appointment['end_date']))->format('H:i').'">';          
            echo '</div>
            </div>
            
            <div class="row">
            <div class="info-item">
                <div class="info-label">Address 📌 :</div>
                <div class="info-value">'.$appointment['city_name'].', Room '.$appointment['room'].'</div>
            </div>

            <div class="info-item bill">
                <div class="info-label">Bill:</div>';
            if(!isset($_GET['edit']) && !isset($_POST['show_schedule']))
                echo '<div class="info-value"><span class="highlight">$'.$appointment['bill'].'</span></div>';
            else if(isset($_POST['show_schedule']))
                echo '<span class="highlight">$  <input type="number" class="new-bill" name="new_bill" value="'.$_POST['new_bill'].'">
                </span>'; 
            else
                echo '<span class="highlight">$  <input type="number" class="new-bill" name="new_bill" value="'.$appointment['bill'].'">
                    </span>'; 
            echo '</div>
            </div>
            <p class="note">* Please arrive 15 minutes before the appointment.</p>
        </div>
        </div></form>';
    
    

if ($_SESSION['role'] == 'patient') {
    echo '<div class="contact_container">
            <img src="../static/media/secretary_default.jpg" alt="LOAD IMAGE" />
            <p class="contact_info">
            Your health is our priority, and we understand that circumstances may change.
            If, for any reason, you need to edit or remove this appointment,
            please feel free to reach out to our dedicated secretary<br><br><span class="secretary_info">
            '.$appointment['sec_fname']." ".$appointment['sec_lname'].'<br>
            '.$appointment['sec_phone'].'</span><br><br>
            They will be more than happy to assist you and ensure that your healthcare needs are met seamlessly.
            Your cooperation is greatly appreciated, and we look forward to serving you.
            </p>
            <div>';
}

//Show the schedule information
else if(isset($_POST['show_schedule'])){
    echo '<div class="new-date">'.$_POST['new_start_date'].'</div>';
    $data = get_doctor_info($dbc,$_SESSION['doctor_id']);
    $data['date']=$_POST['new_start_date'];
    $doctor = get_doctor($data, $dbc,$_SESSION['role']);
    $_SESSION['department_id'] = $doctor['department_id'];
    include "../includes/draw_work_hours.php";
}
mysqli_close($dbc);
?>
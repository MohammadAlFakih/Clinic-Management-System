<?php
    include '../includes/header.php';
    if(!isset($_SESSION['role'])){
        header('location:../login.php');
        die();
    }
    if($_SERVER['REQUEST_METHOD']=='GET' && (!isset($_GET['city']) || !isset($_GET['specialization'])
     || !isset($_GET['date']))){
        header('location:make_appointment.php');
        die();
    }
    else{
        $dbc = connectServer('localhost', 'root', '', 1);
        $db = "mhamad";
        selectDB($dbc, $db, 1);
        $available_doctors = get_doctors($_GET,$dbc);
        if(count($available_doctors) == 0){
            echo "<div class='error'>No doctors are available for this appointment</div>";
        }
        foreach($available_doctors as $doctor){
            echo $doctor['first_name']." ".$doctor['last_name']." IN ".$doctor['city']." and ".$doctor['sq'];
        }
    }
?>
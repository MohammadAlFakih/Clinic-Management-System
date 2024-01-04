<?php
    include($_SERVER['DOCUMENT_ROOT'] . '/Clinic-Management-System/includes/header.php');
    $dbc = connectServer('localhost', 'root', '', 1);
    selectDB($dbc, 'mhamad', 1);

    if (!isset($_SESSION['role']))
        header("location:../login.php");
    else if ($_SESSION['role'] != 'patient') {
        header("location:../secretary/choose_patient.php");
    }

    if (isset($_POST['search'])) {
        $current_date = date('Y-m-d');
        $filted_date = new DateTime($_POST['date']);
        $filted_date = $filted_date->format('Y-m-d');
        if (!isset($_POST['cities']) || !isset($_POST['specialization']) || !isset($_POST['date']) || $filted_date <= $current_date) {
            echo "<div class='error'>Please enter valid filtering information </div>";
            die();
        } else {
            header("Location: available_doctors.php?city=".$_POST['cities'].'&specialization='.$_POST['specialization'].'&date='.$_POST['date']);
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/login.css">
    <title>Make_appointment</title>
</head>
<body>
    <div class="container">
        <form action="make_appointment2.php" method="POST" class="login-form">
            <label for="cities" class="form-label">Select City:</label>
                <select id="cities" name="cities" class="form-input">
                    
                    <?php 
                    //Display cities
                    $cities = get_cities($dbc);
                    foreach($cities as $city){
                        echo '<option value="'.$city.'">'.$city.'</option>';
                    }
                    ?>
                </select>

                <label for="specialization" class="form-label">Select Doctor's Specialization:</label>
                <select id="specialization" name="specialization" class="form-input">
                <?php 
                    //Display Specializations
                    $specializations = get_specializations($dbc);
                    foreach($specializations as $specialization){
                        echo '<option value="'.$specialization.'">'.$specialization.'</option>';
                    }
                    ?>
                </select>

                <label for="date" class="form-label">Choose Appointment Date:</label>
                <input type="date" id="appointmentDate" name="date" class="form-input">
                <br><br>
                <input type="submit" value="Search" name='search' class="form-button">
        </form>
    </div>
</body>
</html>

<?php
    mysqli_close($dbc);
?>

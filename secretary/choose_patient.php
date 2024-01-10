<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Patient</title>
    <link rel="stylesheet" href="../static/css/choose_patient.css">
</head>

<body>

</body>

</html>

<?php

    include '../includes/header.php';

    if (!isset($_SESSION['role'])) {
        header('location:../login.php');
        die();
    }

    if ($_SESSION['role'] != 'secretary' && $_SESSION['role'] != 'doctor') {
        header('location:../index.php');
        die();
    }

    $dbc = connectServer('localhost', 'root', '', 1);
    $db = "mhamad";
    selectDB($dbc, $db, 1);

    // foreach ($_SESSION as $key => $value) {
    //     echo $key . " : " . $value . "<br>" ;
    // }

    
?>

<body>

<form method="post">
    <label for="patient_email">Patient Email:</label>
    <input type="email" name="patient_email" id="patient_email" placeholder="Enter patient email">
    <input type="submit" name="submit_email" value="Submit">
</form>

<p>OR</p>

<form method="post">
    <label for="patient_dropdown">Select Patient:</label>
    <select name="patient_dropdown" id="patient_dropdown">
        <?php

        $patients = get_patients($dbc);

        // Loop through the patients and create options for the dropdown
        foreach ($patients as $patient) {
            echo "<option value='$patient'>$patient</option>";
        }
        ?>
    </select>
    <input type="submit" name="submit_dropdown" value="Submit">
</form>

</body>

<?php

if (isset($_POST['submit_email']) && !isset($_SESSION['selected_patient_id'])) {

    $patientEmail = $_POST['patient_email'];

    if (in_array($patientEmail, $patients)) {
        $_SESSION['selected_patient_id'] = get_patient_id_from_email($dbc, $patientEmail);
        header('location:../patient/make_appointment2.php');
    } else {
        echo "Email not found.";
    }
    

} elseif (isset($_POST['submit_dropdown'])) {

    $patientEmail = $_POST['patient_dropdown'];
    $_SESSION['selected_patient_id'] = get_patient_id_from_email($dbc, $patientEmail);
    header('location:../patient/make_appointment2.php');
}

?>
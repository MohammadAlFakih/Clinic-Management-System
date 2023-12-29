<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/make_appointment.css">
    <title>Make_appointment</title>
</head>
<body>
<?php
include ($_SERVER['DOCUMENT_ROOT'] . '/Clinic-Management-System/includes/header.php');
if (!isset($_SESSION['role']))
    header("location:../login.php");
else if ($_SESSION['role'] != 'patient') {
    header("location:../secretary/choose_patient.php");
}
?>
<div class="container">
<form action="your_processing_script.php" method="post" class="form-container">
    <label for="cities" class="form-label">Select City:</label>
    <select id="cities" name="cities" class="form-input">
        <option value="beirut">Beirut</option>
        <option value="tripoli">Tripoli</option>
        <option value="sidon">Sidon</option>
        <option value="jounieh">Jounieh</option>
        <option value="tyre">Tyre</option>
        <option value="byblos">Byblos</option>
        <option value="baalbek">Baalbek</option>
        <option value="zahle">Zahle</option>
        <option value="nabatieh">Nabatieh</option>
        <option value="ain-dara">Ain Dara</option>
        <option value="saida">Saida</option>
        <option value="batroun">Batroun</option>
        <option value="anjar">Anjar</option>
        <option value="bcharre">Bcharre</option>
        <option value="hermel">Hermel</option>
    </select>

    <label for="specialization" class="form-label">Select Doctor's Specialization:</label>
    <select id="specialization" name="specialization" class="form-input">
        <option value="cardiologist">Cardiologist</option>
        <option value="dermatologist">Dermatologist</option>
        <option value="orthopedic">Orthopedic</option>
        <option value="pediatrician">Pediatrician</option>
    </select>

    <label for="appointmentDate" class="form-label">Choose Appointment Date:</label>
    <input type="date" id="appointmentDate" name="appointmentDate" class="form-input" required>

    <br>


    <br>

    <input type="submit" value="Search" class="form-submit">
</form>
</div>
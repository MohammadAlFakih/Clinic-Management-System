<!-- i wanna make a confirm clear then clear  -->
<?php
session_start();
include "../db_utils/DB_Functions.php";
include "../includes/functions.php";
if(!isset($_SESSION['role'])){
    header("Location:../login.php");
    die();
}

if ($_SESSION['role'] == 'patient') {
    header("Location:../index.php");
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/clear_unavailable_hours.css">
    <title>Clear Unavailable Hours</title>
<body>
<div class="big_container">
<div class="container">
    <h1>Clear All Unavailable Hours</h1>
    <hr>
    <div class="col">
        <div class="date">For The Day: <?php echo $_SESSION['date'];?></div>
    </div>
    <div class="form_buttons">
        <a href="<?php echo $_SESSION['previous_url']?>" class="form_button back">Back</a>
        <form method="post" action="clear_unavailable_hours.php">
        <input type="submit" name='confirm' value="Confirm" class="form_button">
        </form>
    </div>
</div>
</div>
</body>
</html>
<?php
    if (isset($_POST["confirm"])) {

        $dbc = connectServer('localhost', 'root', '', 0);
        selectDB($dbc,"clinic_db",1);

        $query = " DELETE FROM unavailable_slots
                    WHERE doctor_id = ? AND DATE(start_date) = ? ";

        $stmt = $dbc->prepare($query);
        $stmt->bind_param("is",$_SESSION['doctor_id'],$_SESSION['date']);
        $stmt->execute();
        $stmt->close();
        $dbc->close();

        unset($_SESSION['date']);
        unset($_SESSION['department_id']);
        unset($_SESSION['work_start_hour']);
        unset($_SESSION['work_end_hour']);
        header("Location:".$_SESSION['previous_url']."&message=All Unavailable Hours are Cleared for this day! ✅");
    }
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="static/css/login.css">
    <title>Login</title>
</head>
<?php
include "includes/header.php";
$dbc = connectServer('localhost', 'root', '', 0);
$db = "clinic_db";
selectDB($dbc, $db, 0);
?>

<body>
    <div class="container">
        <form action="login.php" method="POST" class="login-form">
            <label class="form-label success"><?php
                                                if (isset($_GET['message'])) {
                                                    if ($_GET['message'] == 'success')
                                                        echo 'Your account has been created successfully ✅';
                                                }
                                                ?>
            </label>
            <label class="form-label" for="email">Email:</label>
            <input class="form-input" type="email" name="email" placeholder="Enter your email" required>
            <label class="form-label" for="password">Password:</label>
            <input class="form-input" type="password" name="password" placeholder="Enter your password" required>
            <button class="form-button" type="submit" name="submit">Login</button>
            <label class="form-label error">
                <?php
                if (isset($_POST['submit'])) {
                    if (!isset($_POST['email']) || !isset($_POST['password'])) {
                        echo "Please enter all your information";
                        die();
                    } else {
                        $email = $_POST['email'];
                        $password = $_POST['password'];
                        $result = login($email, $password, $dbc);
                        if ($result != null) {
                            $_SESSION['role']=$result['role'];
                            $_SESSION['user_id']=$result['id'];
                            if($_SESSION['role'] == 'patient') {
                                $_SESSION['patient_name'] = $result['first_name']." ".$result['last_name'];
                                $_SESSION['patient_id'] = $result['id'];
                            }

                            else if($_SESSION['role'] == 'secretary') {
                                $query = "SELECT sec.doctor_id,doc.department_id
                                            FROM secretary sec
                                            JOIN doctor doc ON sec.doctor_id = doc.id
                                            WHERE sec.id=".$result['id'];
                                $doctor = mysqli_query($dbc, $query);
                                $doctor = $doctor->fetch_assoc();
                                $_SESSION['doctor_id'] = $doctor['doctor_id'];
                                $_SESSION['doctor_department_id'] = $doctor['department_id'];
                            }
                            else{
                                $query = "SELECT doc.department_id
                                            FROM doctor doc
                                            WHERE doc.id=".$result['id'];
                                $doctor = mysqli_query($dbc, $query);
                                $doctor = $doctor->fetch_assoc();
                                $_SESSION['doctor_name'] = $result['first_name']." ".$result['last_name'];
                                $_SESSION['doctor_id'] = $result['id'];
                                $_SESSION['doctor_department_id'] = $doctor['department_id'];
                                // i can use user_id and doctor_id interchangeably for the doctor
                                // We can add more attributes if needed
                            }
                            header('location:index.php');
                        } else {
                            echo "Invalid email or password";
                        }
                    }
                }
                ?>
            </label>
        </form>
    </div>
</body>

</html>
<?php 
    mysqli_close($dbc);
?>
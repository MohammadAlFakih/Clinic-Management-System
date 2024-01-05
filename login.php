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
$dbc = connectServer('localhost', 'root', '', 1);
$db = "mhamad";
selectDB($dbc, $db, 1);
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
                                $query = "SELECT doctor_id FROM secretary WHERE id=".$result['id'];
                                $doctor_id = mysqli_query($dbc, $query);
                                $doctor_id = $doctor_id->fetch_assoc();
                                $_SESSION['doctor_id'] = $doctor_id['doctor_id'];
                            }
                            else{
                                $_SESSION['doctor_id'] = $result['id'];
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
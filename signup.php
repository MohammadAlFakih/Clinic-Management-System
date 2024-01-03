<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Information Form</title>
    <link rel="stylesheet" href="static/css/signup.css">
</head>
<body>
    <?php 
        include "classes/patient.php";
        include "includes/header.php";
        $dbc=connectServer('localhost','root','',1);
        $db="mhamad";
        selectDB($dbc,$db,1);
    ?>
    <div class="container">
    <form class="user-form" action="signup.php" method="POST">

        <label class="form-label" for="firstName">First Name:</label>
        <input class="form-input" type="text" id="first_name" name="first_name" placeholder="Enter your first name">

        <label class="form-label" for="lastName">Last Name:</label>
        <input class="form-input" type="text" id="last_name" name="last_name" placeholder="Enter your last name">

        <label class="form-label" for="age">Age:</label>
        <input class="form-input" type="number" id="age" name="age" placeholder="Enter your age">

        <label class="form-label" for="gender">Gender:</label>
        <select class="form-input" id="gender" name="gender">
            <option value="" disabled selected>Select your gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
        </select>

        <label class="form-label" for="phone">Phone:</label>
        <input class="form-input" type="tel" id="phone" name="phone" placeholder="Enter your phone number" >

        <label class="form-label" for="email">Email:</label>
        <input class="form-input" type="email" id="email" name="email" placeholder="Enter your email">

        <label class="form-label" for="email">Password:</label>
        <input class="form-input" type="password" id="password1" name="password1" placeholder="Password">

        <label class="form-label" for="email">Verify Passowrd:</label>
        <input class="form-input" type="password" id="password2" name="password2" placeholder="Password">

        <button class="form-button" type="submit" name="submit">Sign Up</button>
        <div class="error">
            <?php 
            if(isset($_POST['submit']))
                {
                    if(!isset($_POST['first_name']) || !isset($_POST['last_name']) 
                    || !isset($_POST['email']) || !isset($_POST['age']) 
                    || !isset($_POST['password1']) || !isset($_POST['password2']) || !isset($_POST['gender'])){
                        echo "Please enter all your information";
                        die();
                    }
                    else{
                        if(email_exists($_POST['email'], $dbc)){
                            echo "You cannot use this email address";
                            die();
                        }
                        else if($_POST['password1']!=$_POST['password2']){
                            echo "Passwords don't match";
                            die();
                        }
                        else{
                            $patient=new Patient($_POST);
                            $patient->password=password_hash($_POST['password1'],PASSWORD_DEFAULT);
                            insert_patient($patient,$dbc);
                            header('location:login.php?message=success');
                        }
                    }
                }
            ?>
        </div>
    </form>
    </div>
</body>
</html>
<?php
    mysqli_close($dbc);
?>
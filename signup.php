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
    <form class="user-form" action="signup.php" method="POST" enctype="multipart/form-data">

        <label class="form-label" for="firstName">First Name:</label>
        <input class="form-input" type="text" id="first_name" name="first_name" required placeholder="Enter your first name">

        <label class="form-label" for="lastName">Last Name:</label>
        <input class="form-input" type="text" id="last_name" name="last_name" required placeholder="Enter your last name">

        <label class="form-label" for="age">Age:</label>
        <input class="form-input" type="number" id="age" name="age" required placeholder="Enter your age">

        <label class="form-label" for="gender">Gender:</label>
        <select class="form-input" id="gender" name="gender">
            <option value="" disabled selected>Select your gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
        </select>

        <label class="form-label" for="phone">Phone:</label>
        <input class="form-input" type="tel" id="phone" name="phone" required placeholder="Enter your phone number" >

        <label class="form-label" for="email">Email:</label>
        <input class="form-input" type="email" id="email" name="email" required placeholder="Enter your email">

        <label class="form-label" for="email">Password:</label>
        <input class="form-input" type="password" id="password1" name="password1" required placeholder="Password">

        <label class="form-label" for="email">Verify Passowrd:</label>
        <input class="form-input" type="password" id="password2" name="password2" required placeholder="Password">

		<label class="form-label">Profile Picture: </label>
		<input type="file" name="pp">

        <button class="form-button" type="submit" name="submit">Sign Up</button>
        <div class="error">
            <?php 
            if(isset($_POST['submit']))
                {
                    if(!empty($_POST['first_name']) || !empty($_POST['last_name']) 
                    || !empty($_POST['email']) || !empty($_POST['phone']) || !empty($_POST['age']) 
                    || !empty($_POST['password1']) || !empty($_POST['password2']) || !isset($_POST['gender'])){
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
                            if (isset($_FILES['pp']['name']) && !empty($_FILES['pp']['name'])) {
         
                                $img_name = $_FILES['pp']['name'];
                                $tmp_name = $_FILES['pp']['tmp_name'];
                                $error = $_FILES['pp']['error'];
                                
                                if($error === 0){
                                   $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
                                   $img_ex_to_lc = strtolower($img_ex);
                       
                                   $allowed_exs = array('jpg', 'jpeg', 'png');

                                   if(in_array($img_ex_to_lc, $allowed_exs)){
                                        $new_img_name = uniqid($_POST['first_name'], true).'.'.$img_ex_to_lc;
                                        $img_upload_path = './static/media/profile/'.$new_img_name;
                                        move_uploaded_file($tmp_name, $img_upload_path);
                                        $_POST['profile_pic'] = $new_img_name;
                                    }

                                    else {
                                        echo "You can't upload files of this type";
                                        die();
                                    }
                                }
                            }
                            else {
                                $_POST['profile_pic'] = 'default.png';
                            }
                            }
                        }
                                   
                        $patient=new Patient($_POST);
                        $patient->password=password_hash($_POST['password1'],PASSWORD_DEFAULT);
                        insert_patient($patient,$dbc);
                        header('location:login.php?message=success');
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
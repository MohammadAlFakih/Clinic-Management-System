<?php

include "header.php";
if(!isset($_SESSION['role'])){
    header('location:../login.php');
    die();
}
$dbc = connectServer('localhost', 'root', '', 1);
selectDB($dbc,'mhamad',1);

$user = get_user_info($dbc,$_SESSION['user_id'],$_SESSION['role']);

 ?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Edit Profile</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="../static/css/profile.css" rel="stylesheet" type="text/css">
</head>

<body>

<div class="container">
    <div class="profile-container">

        <form action="edit_profile.php" method="post" enctype="multipart/form-data">

        <h4 class="display-4  fs-1">Edit Profile</h4><br>

        <!-- error -->
        <?php if(isset($_GET['error'])){ ?>
            <div class="alert alert-danger" role="alert">
              <?php echo $_GET['error']; ?>
            </div>
            <?php } ?>
            
            <!-- success -->
            <?php if(isset($_GET['success'])){ ?>
            <div class="alert alert-success" role="alert">
              <?php echo $_GET['success']; ?>
            </div>
            <?php } ?>

        <div class="profile-photo">
            <img src="../static/media/profile/<?=$user['pp']?>" alt="User Photo">
        </div>
        
        <div class="profile-info">

            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="first_name" value="<?php echo $user['first_name']?>">

            <label class="form-label">Last Name</label>
            <input type="text" class="form-control" name="last_name" value="<?php echo $user['last_name']?>">

            <label class="form-label">Phone Number</label>
            <input type="text" class="form-control" name="phone" value="<?php echo $user['phone']?>">

            <label class="form-label">Age</label>
            <input type="number" class="form-control" name="age" value="<?php echo $user['age']?>">

            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo $user['email']?>">

            <label class="form-label">Profile Picture</label>
            <input type="file" class="form-control" name="pp">
            <input type="text" hidden="hidden" name="old_pp" value="<?=$user['pp']?>" >
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="profile.php" class="link-secondary">Back</a>

        </form>

    </div>
</div>

<?php

if(isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['phone']) && isset($_POST['age']) && isset($_POST['email'])) {

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $old_pp = $_POST['old_pp'];

    if (empty($first_name)) {
    	$em = "First name is required";
    	header("Location: edit_profile.php?error=$em");
	    exit;
    }else if (empty($last_name)) {
    	$em = "Last name is required";
    	header("Location:edit_profile.php?error=$em");
	    exit;
    }
    else if (empty($phone)) {
    	$em = "Phone is required";
    	header("Location:edit_profile.php?error=$em");
	    exit;
    }
    else if (empty($age)) {
    	$em = "Age is required";
    	header("Location:edit_profile.php?error=$em");
	    exit;
    }
    else if (empty($email)) {
    	$em = "Email is required";
    	header("Location:edit_profile.php?error=$em");
	    exit;
    }
    else if (email_exists($email, $dbc) && $email != $user['email']) {
        $em = "Email already exists.";
    	header("Location:edit_profile.php?error=$em");
	    exit;
    }
    else {
        // if image is changed
        if (isset($_FILES['pp']['name']) && !empty($_FILES['pp']['name'])) {
        
            $img_name = $_FILES['pp']['name'];
            $tmp_name = $_FILES['pp']['tmp_name'];
            $error = $_FILES['pp']['error'];
            
            if($error === 0){
               $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
               $img_ex_to_lc = strtolower($img_ex);
   
               $allowed_exs = array('jpg', 'jpeg', 'png');

               if(in_array($img_ex_to_lc, $allowed_exs)){
                    $new_img_name = uniqid($first_name, true).'.'.$img_ex_to_lc;
                    $img_upload_path = '../static/media/profile/'.$new_img_name;
                    
                    if ($old_pp == 'default.png') {
                        move_uploaded_file($tmp_name, $img_upload_path);
                    }
                    else {
                    $old_pp_des = "../static/media/profile/$old_pp";
                    // Delete old profile if different than the default
                    if(unlink($old_pp_des)){
                            // successfully deleted
                            move_uploaded_file($tmp_name, $img_upload_path);
                        }
                    else {
                            // error or already deleted
                            move_uploaded_file($tmp_name, $img_upload_path);
                    }
                    }

                // Update the Database if image is changed
                $sql = "UPDATE patient SET first_name=?, last_name=?, phone=?, age=?, email=?, pp=?
                        WHERE id=?";
                $stmt = $dbc->prepare($sql);
                $stmt->bind_param("sssissi",$first_name,$last_name,$phone,$age,$email,$new_img_name,$_SESSION['user_id']);
                $stmt->execute();
                $_SESSION['patient_name'] = $first_name.' '.$last_name;
                header("Location:edit_profile.php?success=Your account has been updated successfully");
                exit;
                }

                else {
                    $em = "You can't upload files of this type";
                    header("Location:edit_profile.php?error=$em&$data");
                    exit;
                }
            }
            else {
                $em = "unknown error occurred!";
                header("Location:edit_profile.php?error=$em&$data");
                exit;
            }
        }
        // image is unchanged
        else {
            $sql = "UPDATE patient SET first_name=?, last_name=?, phone=?, age=?, email=?
                        WHERE id=?";
            $stmt = $dbc->prepare($sql);
            $stmt->bind_param("sssisi",$first_name,$last_name,$phone,$age,$email,$_SESSION['user_id']);
            $stmt->execute();
            $_SESSION['patient_name'] = $first_name.' '.$last_name;
            header("Location:edit_profile.php?success=Your account has been updated successfully");
            exit;
        }
    }
}

?>


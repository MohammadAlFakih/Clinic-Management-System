<link href="../static/css/profile.css" rel="stylesheet" type="text/css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

<?php
include "header.php";
if(!isset($_SESSION['role'])){
    header('location:../login.php');
    die();
}
?>

<body style="background-image: url('../static/media/light_blue_bck_img.avif'); background-size: cover; background-repeat: no-repeat;">
<!-- <body style="  background-color: #7fc9e6;" > -->
<?php

$dbc = connectServer('localhost', 'root', '', 0);
selectDB($dbc,'clinic_db',0);

if(isset($_GET['id']) && isset($_GET['role'])){
    $user = get_user_info($dbc,$_GET['id'],$_GET['role']);
}
else{
$user = get_user_info($dbc,$_SESSION['user_id'],$_SESSION['role']);
}
?>
<div class="container">
    <div class="profile-container">

    <div class="profile-photo">
        <!-- Show user photo if exist -->
        <?php 
        // $profile_path = "../static/media/profile/".$user['id'].".*";
        // $matchingFiles = glob($profile_path);
        // if ($matchingFiles !== false && count($matchingFiles) === 1) 
        //     $profile_path = $matchingFiles[0];
        // if(!file_exists($profile_path)){
        //     $profile_path = "../static/media/profile/default.png";
        // }
        ?>
        <img src="../static/media/profile/<?=$user['pp']?>" alt="User Photo">
    </div>
    
    <div class="profile-info">
        <p><strong></strong><?php echo $user['first_name']." ".$user['last_name'] ?></p>
        <p><strong></strong> <?php echo ucfirst($user['role'])?></p>
        <p><strong></strong><?php echo $user['phone']?></p>
        <p>Age:<strong></strong><?php echo $user['age']?></p>
        <p><strong></strong> <?php echo $user['email']?></p>
    </div>
    <a href="edit_profile.php" class="btn btn-primary">
        Edit Profile
    </a>
    </div>
</div>
</body>
<?php
mysqli_close($dbc);
?>
<link href="../static/css/profile.css" rel="stylesheet" type="text/css">

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
    <div class="container">
<div class="profile-container">

    <div class="profile-photo">
        <!-- Show user photo if exist -->
        <?php 
        $profile_path = "../static/media/profile/".$user['id'].".*";
        $matchingFiles = glob($profile_path);
        if ($matchingFiles !== false && count($matchingFiles) === 1) 
            $profile_path = $matchingFiles[0];
        if(!file_exists($profile_path)){
            $profile_path = "../static/media/profile/default.png";
        }
        ?>
        <img src="<?php echo $profile_path?>" alt="User Photo">
    </div>
    
    <div class="profile-info">
        <p><strong></strong><?php echo $user['first_name']." ".$user['last_name'] ?></p>
        <p><strong></strong> <?php echo ucfirst($user['role'])?></p>
        <p><strong></strong><?php echo $user['phone']?></p>
        <p><strong></strong> <?php echo $user['email']?></p>
    </div>
</div></div>
<?php
mysqli_close($dbc);
?>
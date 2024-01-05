<?php 
    include "../includes/header.php";
    if(!isset($_SESSION['role']) || $_SESSION['role']!='patient') {
        header('location:../index.php');
        die();
    }

    $dbc = connectServer('localhost','root','',0);
    selectDB($dbc,'mhamad',0);
    $notifications = get_notifications($dbc , $_SESSION['patient_id']);
    echo "<div class='container'><div class='message-container'>";
    $empty = false;
    if($notifications && mysqli_num_rows($notifications)>0){
        foreach($notifications as $message){
            echo '
                <div class="message">
                    <span class="sender">Secretary of Dc. '.$message['first_name']." ".$message['last_name'].'</span>
                    <div class="date">'.$message['date'].'</div>';

            $content = "";
            //Check the reason of the message
            if($message['reason'] == 'accepted'){
                $app = get_appointment($dbc,$message['appointment_id']);
                $content = 'Your appointment on '.(new DateTime($app['start_date']))->format('Y-m-d').' has been accepted.
                Please make sure to attend your appointment before 15 minutes.';
            }

            echo '<p>'.$content.'</p></div>';
        }
    }
    else{
        $empty = true;
        echo'
        <div class="message">
        No messages.
        </div>';
    }
    echo "</div>
    <div class='bts'>
    <a href='".$_SESSION['before_inbox']."'>Back</a>
    ";
    if(!$empty)
    echo "<a href='clear_messages.php'>Clear History</a>";
    echo "</div>
    </div>";
    ?>
<link rel="stylesheet" href="../static/css/inbox.css" type="text/css">
<?php
    mysqli_close($dbc);
?>
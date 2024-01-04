<?php 
include '../db_utils/DB_Functions.php';
session_start();
if(!isset($_SESSION['role']) && $_SESSION['role']!='patient'){
    header('../index.php');
    die();
}

$dbc = connectServer('localhost','root','',0);
selectDB($dbc,'mhamad',0);

$query = "DELETE FROM notifications WHERE receiver = ? AND status = 'read'";
$stmt = $dbc->prepare($query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->close();

mysqli_close($dbc);
header('location:inbox.php');
?>
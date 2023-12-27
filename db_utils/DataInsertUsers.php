<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
	<title> USERS ADD </title>
</head>
<body style = "background-color: #F0E68C">

<?php
	include "DB_Functions.php";
	
	$dbc=connectServer('localhost','root','',1);//connect to server	
	selectDB($dbc,"DBMultiSearch",1);//Select DB
				
	$sqlQuery ="INSERT INTO  users (id_user, name, lastname,age,gender) 
					VALUES
						(0, 'ali','jaber',55,'male'),
						(0, 'ahmad','youssef',25,'male'),
						(0, 'zainab','baydoun',21,'female'),
						(0, 'zainab','hajali',21,'female'),
						(0, 'mohamad','khaze',27,'male'),
						(0, 'sara','najem',20,'female'),
						(0, 'fatima','mahdi',22,'female')";

	insertDataToTab($dbc, "users", $sqlQuery);
	@mysqli_close( $dbc); // Close the connection.
?>
</body>
</html>